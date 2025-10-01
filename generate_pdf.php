<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Autoloader and DB Connection
if (!file_exists('vendor/autoload.php')) { die('CRITICAL ERROR: Composer autoload file not found.'); }
require 'vendor/autoload.php';
if (!file_exists('db_connection.php')) { die('CRITICAL ERROR: Database connection file not found.'); }
require_once 'db_connection.php'; // Provides $pdo
if (!file_exists('dal.php')) { die('CRITICAL ERROR: Data Access Layer file not found.'); }
require_once 'dal.php'; // Provides DAL functions

// Define an absolute path for file system operations
define('ABSOLUTE_PATH', __DIR__ . '/');

// --- Input Validation ---
if (!isset($_GET['batch_id']) || !filter_var($_GET['batch_id'], FILTER_VALIDATE_INT) || $_GET['batch_id'] <= 0) {
    $_SESSION['error_message'] = 'Invalid or missing Batch ID for PDF generation.';
    header('Location: index.php');
    exit;
}
$batch_id = (int)$_GET['batch_id'];

// --- Determine Output Mode ---
$outputMode = 'D'; // Default to Download
if (isset($_GET['output_mode']) && strtoupper($_GET['output_mode']) === 'I') {
    $outputMode = 'I'; // Inline view
}

// --- Fetch Batch & Common Data ---
$batchSettingsData = getReportBatchSettings($pdo, $batch_id);
if (!$batchSettingsData) {
    $_SESSION['error_message'] = 'Could not find settings for Batch ID: ' . htmlspecialchars($batch_id);
    header('Location: view_processed_data.php?batch_id=' . $batch_id);
    exit;
}

$stmtStudentIds = $pdo->prepare("SELECT student_id FROM student_report_summary WHERE report_batch_id = :batch_id ORDER BY student_id");
$stmtStudentIds->execute([':batch_id' => $batch_id]);
$studentIdsInBatch = $stmtStudentIds->fetchAll(PDO::FETCH_COLUMN);

if (empty($studentIdsInBatch)) {
    $_SESSION['error_message'] = 'No students found with calculated summaries for Batch ID: ' . htmlspecialchars($batch_id) . '. Please run calculations first.';
    header('Location: view_processed_data.php?batch_id=' . $batch_id);
    exit;
}

$teacherInitials = isset($batchSettingsData['teacher_initials']) ? json_decode($batchSettingsData['teacher_initials'], true) : [];
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON Decode Error for teacher_initials in generate_pdf.php for batch_id: " . $batch_id);
    $teacherInitials = [];
}

$classNameForBatch = $batchSettingsData['class_name'];
$isP4_P7_batch = in_array($classNameForBatch, ['P4', 'P5', 'P6', 'P7']);
$isP1_P3_batch = in_array($classNameForBatch, ['P1', 'P2', 'P3']);
$nurseryClasses = ['Baby Class', 'Middle Class', 'Top Class'];
$isNursery_batch = in_array($classNameForBatch, $nurseryClasses);

$expectedSubjectKeysForClass = [];
if ($isP4_P7_batch) {
    $expectedSubjectKeysForClass = ['english', 'mtc', 'science', 'sst', 'kiswahili'];
} elseif ($isP1_P3_batch) {
    $expectedSubjectKeysForClass = ['english', 'mtc', 're', 'lit1', 'lit2', 'local_lang'];
} elseif ($isNursery_batch) {
    $expectedSubjectKeysForClass = ['language_development', 'mathematical_concepts', 'language_development_2', 'health_habits', 'social_development'];
}

$subjectDisplayNames = [
    'english' => 'English', 'mtc' => 'Mathematics (MTC)', 'science' => 'Science',
    'sst' => 'Social Studies (SST)', 'kiswahili' => 'Kiswahili',
    're' => 'Religious Education (R.E)', 'lit1' => 'Literacy I',
    'lit2' => 'Literacy II', 'local_lang' => 'Local Language',
    'language_development' => 'Language Development',
    'mathematical_concepts' => 'Mathematical Concepts',
    'language_development_2' => 'Language Development II',
    'health_habits' => 'Health Habits',
    'social_development' => 'Social Development'
];
$gradingScaleForP4P7Display = [
    'D1' => '90-100', 'D2' => '80-89', 'C3' => '70-79', 'C4' => '60-69',
    'C5' => '55-59', 'C6' => '50-54', 'P7' => '45-49', 'P8' => '40-44', 'F9' => '0-39'
];

// --- mPDF Initialization ---
try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8', 'format' => 'A4',
        'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 8, 'margin_bottom' => 8,
        'margin_header' => 4, 'margin_footer' => 4, 'default_font_size' => 9.5,
        'default_font' => 'helvetica'
    ]);


    // --- Prepare Base64 encoded logo for embedding in HTML ---
    $logoPath = ABSOLUTE_PATH . 'images/logo.png';
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/' . $logoType . ';base64,' . base64_encode($logoData);
    } else {
        error_log("Report card logo image not found at: " . $logoPath);
    }

    $pdfFileName = 'Report_Cards_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $batchSettingsData['class_name']) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $batchSettingsData['term_name']) . '_' . $batchSettingsData['year_name'] . '.pdf';
    $mpdf->SetTitle('Report Cards - ' . $batchSettingsData['class_name'] . ' Term ' . $batchSettingsData['term_name'] . ' ' . $batchSettingsData['year_name']);
    $mpdf->SetAuthor("MARIA OW'EMBABAZI PRIMARY SCHOOL");
    $mpdf->SetCreator('Report Card System');

    $firstPage = true;

    // --- Loop Through Students & Generate HTML for Each Report ---
    foreach ($studentIdsInBatch as $student_id) {

        if (!$firstPage) {
            $mpdf->AddPage();
        }
        $firstPage = false;

        $sessionKeyForEnrichedData = 'enriched_students_data_for_batch_' . $batch_id;
        if (!isset($_SESSION[$sessionKeyForEnrichedData][$student_id])) {
            throw new Exception("Enriched student data not found in session for student ID $student_id and batch ID $batch_id. Please run calculations first.");
        }
        $currentStudentEnrichedData = $_SESSION[$sessionKeyForEnrichedData][$student_id];

        ob_start();
        if ($isNursery_batch) {
            $studentData = [];
            $studentData['student_name'] = $currentStudentEnrichedData['student_name'];
            $studentData['class_teacher_remark'] = $currentStudentEnrichedData['auto_classteachers_remark_text'] ?? '';
            $studentData['head_teacher_remark'] = $currentStudentEnrichedData['auto_headteachers_remark_text'] ?? '';
            $studentData['subjects'] = [];

            foreach ($expectedSubjectKeysForClass as $subjectKey) {
                // Use the pre-calculated grade and remark from the enriched data
                $studentData['subjects'][$subjectKey] = [
                    'grade' => $currentStudentEnrichedData['subjects'][$subjectKey]['eot_grade'] ?? 'N/A',
                    'remark' => $currentStudentEnrichedData['subjects'][$subjectKey]['eot_remark'] ?? 'N/A'
                ];
            }

            $batchSettings = $batchSettingsData;
            $batchSettings['nursery_specific'] = [
                'school_fees' => $batchSettingsData['nursery_school_fees'] ?? '',
                'coloured_pencils' => $batchSettingsData['nursery_coloured_pencils'] ?? '',
                'toilet_papers' => $batchSettingsData['nursery_toilet_papers'] ?? '',
                'books' => $batchSettingsData['nursery_books'] ?? '',
                'pencils' => $batchSettingsData['nursery_pencils'] ?? ''
            ];
            $batchSettings['term_end_date_formatted'] = isset($batchSettingsData['term_end_date']) ? date('d/m/Y', strtotime($batchSettingsData['term_end_date'])) : '____________________';
            $batchSettings['next_term_begin_date_formatted'] = isset($batchSettingsData['next_term_begin_date']) ? date('d/m/Y', strtotime($batchSettingsData['next_term_begin_date'])) : '____________________';

            include 'nursery_report_card.php';
        } else {
            include 'report_card.php';
        }
        $html = ob_get_clean();
        $mpdf->WriteHTML($html);
    }

    $logDescription = "Generated PDF report for batch '" . htmlspecialchars($batchSettingsData['class_name'] . " " . $batchSettingsData['term_name'] . " " . $batchSettingsData['year_name']) . "' (ID: " . $batch_id . ").";
    logActivity(
        $pdo,
        $_SESSION['user_id'] ?? null,
        $_SESSION['username'] ?? 'System',
        'REPORT_GENERATED',
        $logDescription,
        'batch',
        $batch_id
    );

    $mpdf->Output($pdfFileName, $outputMode);
    exit;

} catch (\Mpdf\MpdfException $e) {
    if (ob_get_level() > 0) ob_end_clean();
    $_SESSION['error_message'] = "mPDF Error generating PDF for Batch ID " . htmlspecialchars($batch_id) . ": " . $e->getMessage();
} catch (Exception $e) {
    if (ob_get_level() > 0) ob_end_clean();
    $_SESSION['error_message'] = "General error generating PDF for Batch ID " . htmlspecialchars($batch_id) . ": " . $e->getMessage() . " (File: " . basename($e->getFile()) . ", Line: " . $e->getLine() . ")";
}

// If any error occurred and was caught, redirect back
if(isset($_SESSION['error_message'])){
    header('Location: view_processed_data.php?batch_id=' . $batch_id);
    exit;
}
?>