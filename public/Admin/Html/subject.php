<?php
session_start();
include '../../../Database/Connection.php';

// --- AUTH CHECK ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../../Auth/Html/login.html");
    exit();
}

// --- SUCCESS / ERROR MESSAGES ---
$successMsg = '';
$errorMsg = '';

if (isset($_SESSION['success'])) {
    $successMsg = $_SESSION['success'];
    unset($_SESSION['success']);
}

// --- FETCH COURSES ---
$courseQuery = "SELECT id, courseName FROM courses";
$courseResult = mysqli_query($conn, $courseQuery);

// --- STATS QUERIES ---
$totalSubjectsQuery = "SELECT COUNT(*) AS total FROM subjects";
$totalSubjectsResult = mysqli_query($conn, $totalSubjectsQuery);
$totalSubjects = ($totalSubjectsResult && mysqli_num_rows($totalSubjectsResult) > 0)
    ? mysqli_fetch_assoc($totalSubjectsResult)['total']
    : 0;

$totalCoursesQuery = "SELECT COUNT(*) AS total FROM courses";
$totalCoursesResult = mysqli_query($conn, $totalCoursesQuery);
$totalCourses = ($totalCoursesResult && mysqli_num_rows($totalCoursesResult) > 0)
    ? mysqli_fetch_assoc($totalCoursesResult)['total']
    : 0;

// If you later add a status column, replace this with WHERE status = 1
$activeSubjects = $totalSubjects;

// --- FORM VARIABLES ---
$subject_name = $course_id = '';

// --- ADD SUBJECT LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $subject_name = trim($_POST['subject_name']);
    $course_id = intval($_POST['course_id']);

    if ($subject_name === '' || $course_id === 0) {
        $errorMsg = "Please fill all fields.";
    } else {
        $insertQuery = "INSERT INTO subjects (subject_name, course_id) VALUES ('$subject_name', '$course_id')";
        if (mysqli_query($conn, $insertQuery)) {
            $_SESSION['success'] = "Subject added successfully!";
            header("Location: addsubject.php");
            exit();
        } else {
            $errorMsg = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject</title>
    <link rel="stylesheet" href="../Css/Admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --primary: #1a2a6c;
        --secondary: #b21f1f;
        --accent: #fdbb2d;
        --light: #f8f9fa;
        --dark: #0f1a45;
        --gray: #6c757d;
        --white: #ffffff;
        --card-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        --border-radius: 12px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #e9eef8 100%);
        color: #222;
        margin: 0;
        padding: 30px;
        margin-left: 250px;
        min-height: 100vh;
    }

    .container {
        max-width: 95%;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
        padding: 0 10px;
    }

    .page-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-title h2 {
        font-size: 32px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
    }

    .card {
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        margin-bottom: 24px;
        overflow: hidden;
        min-height: 400px;
        display: flex;
        flex-direction: column;
    }

    .card-header {
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        color: #fff;
        padding: 20px 30px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 18px;
    }

    .card-body {
        padding: 40px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .form-container {
        max-width: 800px;
        margin: 0 auto;
        width: 100%;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    label {
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 10px;
        font-size: 15px;
    }

    .form-control {
        padding: 14px 18px;
        border-radius: 8px;
        border: 1px solid #e6e9ef;
        background: #fff;
        font-size: 15px;
        transition: all 0.3s ease;
        height: 50px;
    }

    .form-control:focus {
        outline: none;
        box-shadow: 0 6px 18px rgba(26, 42, 108, 0.08);
        border-color: var(--primary);
    }

    .btn {
        padding: 14px 28px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        font-size: 16px;
        height: 50px;
    }

    .btn-primary {
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        color: #fff;
        box-shadow: 0 8px 20px rgba(26, 42, 108, 0.12);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(26, 42, 108, 0.2);
    }

    .btn-accent {
        background: linear-gradient(90deg, var(--accent), #ffd166);
        color: var(--dark);
        box-shadow: 0 8px 20px rgba(253, 187, 45, 0.2);
    }

    .btn-accent:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(253, 187, 45, 0.3);
    }

    .alert {
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        animation: slideDown 0.3s ease-out;
    }

    .alert.success {
        background: #e9f7ee;
        color: #28a745;
        border-left: 4px solid #28a745;
    }

    .alert.error {
        background: #fdecea;
        color: #c82333;
        border-left: 4px solid #c82333;
    }

    .required::after {
        content: " *";
        color: var(--secondary);
    }

    .footer-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: auto;
        gap: 15px;
        padding-top: 30px;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
    }

    .fade-out {
        animation: fadeOut 0.5s ease-out forwards;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeOut {
        from { opacity: 1; }
        to {
            opacity: 0;
            height: 0;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--white);
        padding: 25px;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        text-align: center;
        border-left: 4px solid var(--primary);
    }

    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 8px;
    }

    .stat-label {
        color: var(--gray);
        font-weight: 600;
        font-size: 14px;
    }

    @media (max-width: 1200px) {
        .form-row { grid-template-columns: 1fr; gap: 20px; }
        .card-body { padding: 30px; }
    }

    @media (max-width: 900px) {
        body { padding: 20px; margin-left: 0; }
        .container { max-width: 100%; }
        .footer-actions { flex-direction: column; }
        .action-buttons { width: 100%; }
        .btn { width: 100%; justify-content: center; }
        .page-header { flex-direction: column; align-items: flex-start; gap: 15px; }
    }

    @media (max-width: 480px) {
        .card-body { padding: 20px 15px; }
        .page-title h2 { font-size: 26px; }
        .card-header { padding: 15px 20px; font-size: 16px; }
        .stats-cards { grid-template-columns: 1fr; }
    }
    </style>
</head>
<body>
    <?php include 'side_menu.php'; ?>

    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h2><i class="fas fa-book"></i>&nbsp; Add New Subject</h2>
            </div>
        </div>

        <?php if ($successMsg): ?>
            <div class="alert success" id="successAlert">
                <i class="fas fa-check-circle"></i>
                <?= $successMsg ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="alert error" id="errorAlert">
                <i class="fas fa-exclamation-circle"></i>
                <?= $errorMsg ?>
            </div>
        <?php endif; ?>

        <!-- ✅ Dynamic Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?= $totalSubjects ?></div>
                <div class="stat-label">Total Subjects</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalCourses ?></div>
                <div class="stat-label">Total Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $activeSubjects ?></div>
                <div class="stat-label">Active Subjects</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus-circle"></i> Subject Details
            </div>
            <div class="card-body">
                <div class="form-container">
                    <form method="POST" id="subjectForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="required">Subject Name</label>
                                <input type="text" name="subject_name" class="form-control" 
                                       placeholder="Enter subject name (e.g., Mathematics, Physics)" 
                                       value="<?= htmlspecialchars($subject_name) ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="required">Course</label>
                                <select name="course_id" class="form-control" required>
                                    <option value="">-- Select Course --</option>
                                    <?php 
                                    mysqli_data_seek($courseResult, 0);
                                    while ($row = mysqli_fetch_assoc($courseResult)): 
                                        $selected = ($course_id == $row['id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row['id'] ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($row['courseName']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="footer-actions">
                            <div class="action-buttons">
                                <button type="reset" class="btn btn-primary" onclick="clearForm()">
                                    <i class="fas fa-redo"></i> Reset Form
                                </button>
                                <button type="submit" name="add_subject" class="btn btn-accent">
                                    <i class="fas fa-save"></i> Add Subject
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');

            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.add('fade-out');
                    setTimeout(() => successAlert.remove(), 500);
                }, 5000);
            }

            if (errorAlert) {
                setTimeout(() => {
                    errorAlert.classList.add('fade-out');
                    setTimeout(() => errorAlert.remove(), 500);
                }, 8000);
            }
        });

        function clearForm() {
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');
            if (successAlert) successAlert.remove();
            if (errorAlert) errorAlert.remove();
        }
    </script>
</body>
</html>
