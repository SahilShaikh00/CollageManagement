<?php
    session_start();
    require_once("../../../Database/Connection.php");

    // --- AUTH CHECK ---
   if (
    !isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['Admin', 'Teacher'])
) {
    header("Location: ../../Auth/Html/login.html");
    exit;
}

    $UserID = $_SESSION['user']['id']; // ✅ Correct ID
    $successMsg = '';
    $errorMsg = '';

    // --- FETCH COURSES (always available) ---
    $courses = mysqli_query($conn, "SELECT DISTINCT id AS course_id, courseName FROM courses");

    // --- FETCH DIVISIONS BASED ON SELECTED COURSE ---
    $divisions = [];
    if (!empty($_GET['course_id'])) {
        $cid = intval($_GET['course_id']);
        $divisions = mysqli_query($conn, "
            SELECT DISTINCT d.id AS division_id, d.division_name
            FROM student_profiles s
            INNER JOIN divisions d ON s.division_id = d.id
            WHERE s.course_id = $cid
        ");
    }

    // --- FETCH SUBJECTS BASED ON SELECTED COURSE ---
    $subjectOptions = [];
    if (!empty($_GET['course_id'])) {
        $cid = intval($_GET['course_id']);
        $subjectOptions = mysqli_query($conn, "SELECT * FROM subjects WHERE course_id = $cid");
    }

   // --- HANDLE ATTENDANCE SUBMIT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $subject_id = intval($_POST['subject_id']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $cid = intval($_POST['course_id']);
    $div = intval($_POST['division_id']);
    $presentStudents = isset($_POST['attendance']) ? $_POST['attendance'] : []; // Only checked ones

    // Fetch all students in the selected course & division
    $allStudents = mysqli_query($conn, "
        SELECT id FROM student_profiles 
        WHERE course_id = $cid AND division_id = $div
    ");

    if (mysqli_num_rows($allStudents) > 0) {
        while ($stu = mysqli_fetch_assoc($allStudents)) {
            $student_id = intval($stu['id']);
            $status = isset($presentStudents[$student_id]) ? 'Present' : 'Absent';

            // Check if attendance record already exists
            $check = mysqli_query($conn, "
                SELECT * FROM attendance 
                WHERE student_id = $student_id AND subject_id = $subject_id AND date = '$date'
            ");

            if (mysqli_num_rows($check) > 0) {
                // Update existing record
                mysqli_query($conn, "
                    UPDATE attendance 
                    SET status = '$status', marked_by = $UserID 
                    WHERE student_id = $student_id AND subject_id = $subject_id AND date = '$date'
                ");
            } else {
                // Insert new record
                mysqli_query($conn, "
                    INSERT INTO attendance (student_id, subject_id, date, status, marked_by)
                    VALUES ($student_id, $subject_id, '$date', '$status', $UserID)
                ");
            }
        }

        $successMsg = "Attendance marked successfully!";
    } else {
        $errorMsg = "No students found for this course and division.";
    }
}


    // --- FETCH STUDENTS BASED ON FILTER ---
    $students = [];
    if (!empty($_GET['course_id']) && !empty($_GET['division_id'])) {
        $cid = intval($_GET['course_id']);
        $div = intval($_GET['division_id']);
        $students = mysqli_query($conn, "
            SELECT * FROM student_profiles 
            WHERE course_id = $cid AND division_id = $div
        ");
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance</title>

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
        max-width: 1200px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .page-header h2 {
        font-size: 28px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .card {
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        color: #fff;
        padding: 16px 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-body {
        padding: 20px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
        align-items: end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    label {
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 6px;
        font-size: 14px;
    }

    .form-control {
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #e6e9ef;
        background: #fff;
        font-size: 14px;
    }

    .form-control:focus {
        outline: none;
        box-shadow: 0 6px 18px rgba(26, 42, 108, 0.08);
        border-color: var(--primary);
    }

    .btn {
        padding: 10px 16px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
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

    .btn-delete {
        background: linear-gradient(90deg, #e55353, #c82333);
        color: #fff;
        border-radius: 6px;
        padding: 8px 12px;
    }

    .table-container {
        overflow-x: auto;
        border-radius: 10px;
        box-shadow: var(--card-shadow);
        background: #fff;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        color: #fff;
        text-align: left;
        padding: 12px;
        font-weight: 600;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid #f1f3f7;
        vertical-align: middle;
    }

    tr:nth-child(even) {
        background-color: #f9fafb;
    }

    tr:hover {
        background-color: #f0f4f8;
    }

    .attendance-options {
        display: flex;
        gap: 15px;
    }

    .radio-label {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

    input[type="radio"] {
        accent-color: var(--primary);
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
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

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--gray);
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        color: #d1d5db;
    }

    .footer-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 700;
        display: inline-block;
    }

    .status-present {
        background: linear-gradient(90deg, #28a745, #20c997);
        color: white;
    }

    .status-absent {
        background: linear-gradient(90deg, #e55353, #c82333);
        color: white;
    }

    @media (max-width: 900px) {
        body {
            padding: 16px;
            margin-left: 0;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>
    <?php include 'side_menu.php'; ?>

    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-clipboard-check"></i>&nbsp; Manage Attendance</h2>
        </div>

        <?php if ($successMsg): ?>
        <div class="alert success">
            <i class="fas fa-check-circle"></i>
            <?= $successMsg ?>
        </div>
        <?php elseif ($errorMsg): ?>
        <div class="alert error">
            <i class="fas fa-exclamation-circle"></i>
            <?= $errorMsg ?>
        </div>
        <?php endif; ?>

        <!-- FILTER FORM -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-filter"></i> Filter Students
            </div>
            <div class="card-body">
                <form method="GET">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Course</label>
                            <select name="course_id" class="form-control" onchange="this.form.submit()">
                                <option value="">Select Course</option>
                                <?php while ($c = mysqli_fetch_assoc($courses)) { ?>
                                <option value="<?= $c['course_id'] ?>"
                                    <?= (!empty($_GET['course_id']) && $_GET['course_id'] == $c['course_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['courseName']) ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Division</label>
                            <select name="division_id" class="form-control">
                                <option value="">Select Division</option>
                                <?php if (!empty($divisions)) { 
                                    while ($div = mysqli_fetch_assoc($divisions)) { ?>
                                <option value="<?= $div['division_id'] ?>"
                                    <?= (!empty($_GET['division_id']) && $_GET['division_id'] == $div['division_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($div['division_name']) ?>
                                </option>
                                <?php } } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Fetch Students
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ATTENDANCE FORM -->
        <?php if (!empty($_GET['course_id']) && !empty($_GET['division_id'])): ?>
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-check"></i> Mark Attendance
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="course_id" value="<?= $_GET['course_id'] ?>">
                    <input type="hidden" name="division_id" value="<?= $_GET['division_id'] ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Subject</label>
                            <select name="subject_id" class="form-control" required>
                                <option value="">Select Subject</option>
                                <?php if (!empty($subjectOptions)) { 
                                        while ($s = mysqli_fetch_assoc($subjectOptions)) { ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
                                <?php } } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="table-container" style="margin-top: 20px;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Mark Present</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($students) && mysqli_num_rows($students) > 0) {
                while ($stu = mysqli_fetch_assoc($students)) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($stu['first_name']) ?></td>
                                    <td style="text-align:center;">
                                        <input type="checkbox" name="attendance[<?= $stu['id'] ?>]" value="Present"
                                            style="width:20px; height:20px; accent-color:var(--primary); cursor:pointer;">
                                    </td>
                                </tr>
                                <?php } } else { ?>
                                <tr>
                                    <td colspan="2">
                                        <div class="empty-state">
                                            <i class="fas fa-users-slash"></i>
                                            <p>No students found for this course & division.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>


                    <div class="footer-actions">
                        <button type="submit" name="mark_attendance" class="btn btn-accent">
                            <i class="fas fa-save"></i> Submit Attendance
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    // Set today's date as default if not already set
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.querySelector('input[name="date"]');
        if (dateInput && !dateInput.value) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
    });
    </script>
</body>

</html>