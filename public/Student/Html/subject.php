<?php
session_start();
include '../../../Database/Connection.php';

// --- AUTH CHECK ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../../Auth/Html/login.html");
    exit;
}

$StudentID = $_SESSION['user']['id'];

// --- FETCH STUDENT COURSE ---
$studentQuery = mysqli_query($conn, "
    SELECT s.course_id, c.courseName 
    FROM student_profiles s
    INNER JOIN courses c ON s.course_id = c.id
    WHERE s.user_id = $StudentID
");

if (mysqli_num_rows($studentQuery) === 0) {
    die("Student course not found. Please contact admin.");
}

$studentData = mysqli_fetch_assoc($studentQuery);
$courseID = intval($studentData['course_id']);
$courseName = $studentData['courseName'];

// --- FETCH SUBJECTS FOR THAT COURSE ---
$subjectQuery = mysqli_query($conn, "
    SELECT id, subject_name 
    FROM subjects 
    WHERE course_id = $courseID
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subjects</title>
    <link rel="stylesheet" href="../Css/Admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fa;
            color: #333;
            font-family: 'Segoe UI', sans-serif;
            margin-left: 250px;
            padding: 30px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        .page-header {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 20px;
        }
        .page-header h2 {
            font-size: 26px;
            color: #1a2a6c;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(90deg, #1a2a6c, #b21f1f);
            color: white;
            padding: 14px 20px;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #1a2a6c;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .empty {
            text-align: center;
            padding: 40px;
            color: #777;
        }
    </style>
        <link rel="stylesheet" href="../../Admin/Css/Admin.css">
</head>
<body>
    <?php include 'sideMeneu.php'; ?>

    <div class="container">
        <div class="page-header">
            <i class="fas fa-book fa-lg"></i>
            <h2>My Subjects</h2>
        </div>

        <div class="card">
            <div class="card-header">
                Course: <?= htmlspecialchars($courseName) ?>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($subjectQuery) > 0) { ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            while ($sub = mysqli_fetch_assoc($subjectQuery)) { ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($sub['subject_name']) ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <div class="empty">
                        <i class="fas fa-folder-open fa-3x"></i>
                        <p>No subjects found for your course.</p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
