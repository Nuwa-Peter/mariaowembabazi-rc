<?php
require_once 'session_check.php'; // Handles session start and authentication

$last_processed_batch_id = $_SESSION['last_processed_batch_id'] ?? null;
$current_teacher_initials_for_session = $_SESSION['current_teacher_initials'] ?? []; // For repopulating form if needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nursery Data Entry - Maria Ow'embabazi Primary School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="images/logo.png">
    <link href="css/style.css" rel="stylesheet">
    <style>
        body { background-color: #e0f7fa; }
        .container.main-content {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .card-header-custom {
            background-color: #f8f9fa;
            padding: 0.75rem 1.25rem;
            margin-bottom: 0;
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-light bg-light sticky-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2">
                Maria Ow'embabazi P/S - Report System
            </a>
            <div>
                <a href="index.php" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                <a href="logout.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container main-content">
        <?php
        if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])) {
            echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            if ($last_processed_batch_id) {
                echo '<div class="mt-3"><a href="view_processed_data.php?batch_id=' . htmlspecialchars($last_processed_batch_id) . '" class="btn btn-primary"><i class="fas fa-eye"></i> View Processed Data</a></div>';
            }
            unset($_SESSION['success_message']);
        }
        ?>
        <div class="text-center mb-4">
            <h2>Nursery Section - Data Entry</h2>
        </div>

        <div class="row justify-content-center"><div class="col-lg-9 mx-auto">
            <div class="card mb-4">
                <h5 class="card-header card-header-custom text-center">Download Nursery Marks Entry Template</h5>
                <div class="card-body text-center">
                    <p class="text-muted mb-3">Download the Excel template for the Nursery section. The template contains sheets for each learning area.</p>
                    <a href="download_template.php?type=nursery" class="btn btn-primary"><i class="fas fa-file-excel"></i> Download Nursery Template</a>
                </div>
            </div>
        </div></div>

        <form action="process_excel.php" method="post" enctype="multipart/form-data">
            <div class="row"><div class="col-lg-9 mx-auto">

            <div class="card mb-4">
                <h5 class="card-header card-header-custom">School & Term Information</h5>
                <div class="card-body">
                    <div class="row mb-3 justify-content-center mt-3">
                        <div class="col-md-4">
                            <label for="class_selection" class="form-label">Class:</label>
                            <select class="form-select" id="class_selection" name="class_selection" required>
                                <option value="" disabled selected>Select Nursery Class</option>
                                <option value="Baby Class">Baby Class</option>
                                <option value="Middle Class">Middle Class</option>
                                <option value="Top Class">Top Class</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="year" class="form-label">Year:</label>
                            <select class="form-select" id="year" name="year" required>
                                <option value="" disabled selected>Select Year</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="term" class="form-label">Term:</label>
                            <select class="form-select" id="term" name="term" required>
                                <option value="" disabled selected>Select Term</option>
                                <option value="I">Term I</option>
                                <option value="II">Term II</option>
                                <option value="III">Term III</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 justify-content-center">
                         <div class="col-md-6">
                            <label for="term_end_date" class="form-label">This Term Ended On:</label>
                            <input type="date" class="form-control" id="term_end_date" name="term_end_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="next_term_begin_date" class="form-label">Next Term Begins On:</label>
                            <input type="date" class="form-control" id="next_term_begin_date" name="next_term_begin_date" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <h5 class="card-header card-header-custom">Nursery Specific Information</h5>
                <div class="card-body">
                    <div class="row mb-3 justify-content-center">
                        <div class="col-md-4"><label for="school_fees" class="form-label">School Fees:</label><input type="text" class="form-control" id="school_fees" name="nursery_specific[school_fees]" placeholder="e.g., 100000"></div>
                        <div class="col-md-4"><label for="coloured_pencils" class="form-label">Coloured Pencils:</label><input type="text" class="form-control" id="coloured_pencils" name="nursery_specific[coloured_pencils]" placeholder="e.g., 1 packet"></div>
                        <div class="col-md-4"><label for="toilet_papers" class="form-label">Toilet Papers:</label><input type="text" class="form-control" id="toilet_papers" name="nursery_specific[toilet_papers]" placeholder="e.g., 5 rolls"></div>
                    </div>
                    <div class="row mb-3 justify-content-center">
                        <div class="col-md-6"><label for="books" class="form-label">Books:</label><input type="text" class="form-control" id="books" name="nursery_specific[books]" placeholder="e.g., 12 books"></div>
                        <div class="col-md-6"><label for="pencils" class="form-label">Pencils:</label><input type="text" class="form-control" id="pencils" name="nursery_specific[pencils]" placeholder="e.g., 12 pencils"></div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <h5 class="card-header card-header-custom text-center">Upload Marks File & Teacher Initials</h5>
                <div class="card-body">
                    <div class="row justify-content-center mb-4">
                        <div class="col-md-8"><label for="marks_excel_file" class="form-label">Marks Excel File (.xlsx):</label><input type="file" class="form-control" id="marks_excel_file" name="marks_excel_file" required accept=".xlsx"></div>
                    </div>
                    <hr>
                    <h6 class="mt-4 text-center">Enter Teacher Initials</h6>
                    <div class="row mb-2 justify-content-center"><div class="col-md-4 text-end"><label for="language_development_initials" class="form-label">Language Development Initials:</label></div><div class="col-md-4"><input type="text" class="form-control" name="teacher_initials[language_development]" placeholder="e.g., L.D."></div></div>
                    <div class="row mb-2 justify-content-center"><div class="col-md-4 text-end"><label for="mathematical_concepts_initials" class="form-label">Mathematical Concepts Initials:</label></div><div class="col-md-4"><input type="text" class="form-control" name="teacher_initials[mathematical_concepts]" placeholder="e.g., M.C."></div></div>
                    <div class="row mb-2 justify-content-center"><div class="col-md-4 text-end"><label for="language_development_2_initials" class="form-label">Language Development II Initials:</label></div><div class="col-md-4"><input type="text" class="form-control" name="teacher_initials[language_development_2]" placeholder="e.g., L.D.2"></div></div>
                    <div class="row mb-2 justify-content-center"><div class="col-md-4 text-end"><label for="health_habits_initials" class="form-label">Health Habits Initials:</label></div><div class="col-md-4"><input type="text" class="form-control" name="teacher_initials[health_habits]" placeholder="e.g., H.H."></div></div>
                    <div class="row mb-2 justify-content-center"><div class="col-md-4 text-end"><label for="social_development_initials" class="form-label">Social Development Initials:</label></div><div class="col-md-4"><input type="text" class="form-control" name="teacher_initials[social_development]" placeholder="e.g., S.D."></div></div>
                </div>
            </div>

            <div class="d-grid gap-2 col-md-6 mx-auto mt-4 mb-5">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-cogs"></i> Process & Save Data</button>
            </div>
            </div></div>
        </form>
    </div>
    <footer class="text-center mt-5 mb-3"><p>&copy; <span id="currentYear"></span> Maria Ow'embabazi Primary School - <i>Good Christian, Good Citizen</i></p></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const yearSelect = document.getElementById('year');
            if (yearSelect) {
                const currentYear = new Date().getFullYear();
                for (let year = currentYear; year <= 2100; year++) {
                    const option = document.createElement('option');
                    option.value = year;
                    option.textContent = year;
                    yearSelect.appendChild(option);
                }
                if(yearSelect.options.length > 1 && !yearSelect.value) {
                     yearSelect.value = currentYear;
                }
            }
            const currentYearSpan = document.getElementById('currentYear');
            if (currentYearSpan) {
                currentYearSpan.textContent = new Date().getFullYear();
            }
        });
    </script>
</body>
</html>