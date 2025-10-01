<?php
// nursery_report_card.php - Template for generating Nursery student report cards
// EXPECTS variables: $studentData, $batchSettings, $teacherInitials
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nursery Report Card - <?php echo htmlspecialchars($studentData['student_name'] ?? 'Student'); ?></title>
    <link rel="icon" type="image/png" href="<?php echo BASE_PATH; ?>/images/logo.png">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            font-size: 12pt;
        }
        .report-card-container {
            width: 210mm;
            height: 297mm;
            margin: 10mm auto;
            padding: 15mm;
            background-color: white;
            border: 1px solid #000;
            position: relative;
            box-sizing: border-box;
        }
        .watermark {
            position: fixed;
            top: 10mm;
            right: 10mm;
            width: 60mm;
            height: auto;
            opacity: 0.1;
            z-index: -1;
        }
        .header {
            text-align: center;
            margin-bottom: 10mm;
        }
        .header .school-name {
            font-size: 24pt;
            font-weight: bold;
        }
        .header .logo-container {
            margin: 5mm 0;
        }
        .header .logo-container img {
            width: 80px;
            height: auto;
        }
        .header .school-details {
            font-size: 11pt;
        }
        .report-title {
            font-size: 16pt;
            font-weight: bold;
            margin-top: 10mm;
            text-decoration: underline;
        }
        .student-details {
            margin-bottom: 10mm;
        }
        .student-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-details td {
            padding: 5px;
            font-size: 12pt;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10mm;
        }
        .results-table th, .results-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .results-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .info-section {
            margin-bottom: 10mm;
        }
        .remarks-section {
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 10mm;
        }
        .remarks-section p {
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        .signature-line {
            margin-top: 15px;
            text-align: right;
        }
        .info-section table {
            width: 100%;
        }
        .info-section td {
            padding: 5px;
        }
        .grading-key {
            font-size: 10pt;
            margin-top: 10mm;
        }
        .footer-note {
            margin-top: 5mm;
            font-style: italic;
            font-size: 10pt;
        }
    </style>
</head>
<body>
    <div class="report-card-container">
        <?php if (!empty($logoBase64)): ?>
            <img src="<?php echo $logoBase64; ?>" class="watermark" alt="Watermark">
        <?php endif; ?>
        <div class="header">
            <div class="school-name">MARIA OW'EMBABAZI PRIMARY SCHOOL</div>
            <div class="logo-container">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?php echo $logoBase64; ?>" alt="School Logo">
                <?php endif; ?>
            </div>
            <div class="school-details">
                P.O. BOX, 406<br>
                MBARARA<br>
                TEL: 0700-172858
            </div>
            <div class="report-title">PUPIL'S ASSESSMENT PERFORMANCE REPORT</div>
        </div>

        <div class="student-details">
            <table>
                <tr>
                    <td><strong>PUPIL'S NAME:</strong> <?php echo htmlspecialchars($studentData['student_name'] ?? '____________________'); ?></td>
                </tr>
                <tr>
                    <td><strong>CLASS:</strong> <?php echo htmlspecialchars($batchSettings['class_name'] ?? '__________'); ?></td>
                    <td><strong>TERM:</strong> <?php echo htmlspecialchars($batchSettings['term_name'] ?? '__________'); ?></td>
                    <td><strong>YEAR:</strong> <?php echo htmlspecialchars($batchSettings['year_name'] ?? '__________'); ?></td>
                </tr>
            </table>
        </div>

        <table class="results-table">
            <thead>
                <tr>
                    <th>LEARNING AREA (SUBJECT)</th>
                    <th>GRADE ATTAINED</th>
                    <th>REMARKS</th>
                    <th>INITIALS</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subjects = [
                    'language_development' => 'LANGUAGE DEVELOPMENT',
                    'mathematical_concepts' => 'MATHEMATICAL CONCEPTS',
                    'language_development_2' => 'LANGUAGE DEVELOPMENT II',
                    'health_habits' => 'HEALTH HABITS',
                    'social_development' => 'SOCIAL DEVELOPMENT'
                ];
                foreach ($subjects as $key => $name):
                ?>
                <tr>
                    <td><?php echo $name; ?></td>
                    <td><?php echo htmlspecialchars($studentData['subjects'][$key]['grade'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($studentData['subjects'][$key]['remark'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($teacherInitials[$key] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="info-section">
            <p><strong>DAYS MISSED:</strong> ____________________</p>
        </div>

        <div class="remarks-section">
            <p><strong>CLASS TEACHER'S REPORT:</strong> <?php echo nl2br(htmlspecialchars($studentData['class_teacher_remark'] ?? '____________________')); ?></p>
            <div class="signature-line">
                <strong>SIGNATURE:</strong> ............................................
            </div>
        </div>

        <div class="remarks-section">
            <p><strong>HEADTEACHER'S REPORT:</strong> <?php echo nl2br(htmlspecialchars($studentData['head_teacher_remark'] ?? '____________________')); ?></p>
            <div class="signature-line">
                <strong>SIGNATURE:</strong> ............................................
            </div>
        </div>

        <div class="info-section">
            <table>
                <tr>
                    <td><strong>SCHOOL FEES:</strong> <?php echo htmlspecialchars($batchSettings['nursery_specific']['school_fees'] ?? '__________'); ?></td>
                    <td><strong>COLOURED PENCILS:</strong> <?php echo htmlspecialchars($batchSettings['nursery_specific']['coloured_pencils'] ?? '__________'); ?></td>
                </tr>
                <tr>
                    <td><strong>TOILET PAPERS:</strong> <?php echo htmlspecialchars($batchSettings['nursery_specific']['toilet_papers'] ?? '__________'); ?></td>
                    <td><strong>BOOKS:</strong> <?php echo htmlspecialchars($batchSettings['nursery_specific']['books'] ?? '__________'); ?></td>
                </tr>
                <tr>
                    <td><strong>PENCILS:</strong> <?php echo htmlspecialchars($batchSettings['nursery_specific']['pencils'] ?? '__________'); ?></td>
                    <td></td>
                </tr>
            </table>
        </div>

        <div class="info-section">
             <table>
                <tr>
                    <td><strong>THIS TERM ENDED ON:</strong> <?php echo htmlspecialchars($batchSettings['term_end_date_formatted'] ?? '____________________'); ?></td>
                    <td><strong>NEXT TERM BEGINS ON:</strong> <?php echo htmlspecialchars($batchSettings['next_term_begin_date_formatted'] ?? '____________________'); ?></td>
                </tr>
             </table>
        </div>

        <div class="grading-key">
            <strong>KEY:</strong>
            <strong>A=</strong> 90-100 EXCELLENT,
            <strong>B=</strong> 80-89 V.GOOD,
            <strong>C=</strong> 60-79 GOOD,
            <strong>D=</strong> 40-59, FAIR
            <strong>E=</strong> 0-39 PUT MORE EFFORTS.
        </div>
        <div class="footer-note">
            NOTE: PUPIL'S SHOULD PAY 50% OF THE SCHOOL FEES AT THE REPORTING DAY.
        </div>
    </div>
</body>
</html>