<?php
session_start();
include '../../../Database/Connection.php';

// --- AUTH CHECK ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../../Auth/Html/login.html");
    exit;
}

$StudentID = $_SESSION['user']['id'];
$successMsg = '';
$errorMsg = '';

// --- FETCH ATTENDANCE RECORDS ---
$query = "
    SELECT 
        a.date, 
        a.status, 
        s.subject_name,
        sp.first_name AS marked_by
    FROM attendance a
    INNER JOIN subjects s ON a.subject_id = s.id
    LEFT JOIN student_profiles sp ON a.marked_by = sp.id
    WHERE a.student_id = $StudentID
    ORDER BY a.date DESC
";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance</title>   
     <link rel="stylesheet" href="../../Admin/Css/Admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f4f6f9;
            color: #333;
            font-family: 'Segoe UI', sans-serif;
            padding: 30px;
            margin-left: 250px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .page-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .page-header h2 {
            font-size: 26px;
            color: #1a2a6c;
        }
        .card {
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(90deg, #1a2a6c, #b21f1f);
            color: #fff;
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
            padding: 10px;
            border-bottom: 1px solid #eaeaea;
        }
        tr:nth-child(even) { background: #f9fafb; }
        .status-present {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
        }
        .status-absent {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
        }
        .empty {
            text-align: center;
            padding: 40px;
            color: #777;
        }
    </style>
</head>
<body>
    <?php include 'sideMeneu.php'; ?>
    <div class="container">
        <div class="page-header">
            <i class="fas fa-calendar-check fa-lg"></i>
            <h2>My Attendance Record</h2>
        </div>

        <div class="card">
            <div class="card-header">
                Attendance Summary
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Marked By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['date']) ?></td>
                                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'Present') { ?>
                                            <span class="status-present">Present</span>
                                        <?php } else { ?>
                                            <span class="status-absent">Absent</span>
                                        <?php } ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['marked_by'] ?? '—') ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <div class="empty">
                        <i class="fas fa-user-slash fa-3x"></i>
                        <p>No attendance records found yet.</p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body> 
</html>
