<?php
session_start();
require_once("../../../Database/Connection.php");

// --- AUTH CHECK (only student allowed) ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../../Auth/Html/login.html");
    exit;
}

$student = $_SESSION['user'];
$StudentID = intval($student['id'] ?? 0);

// --- Fetch student course & division ---
$stmt = $conn->prepare("SELECT course_id, division_id FROM student_profiles WHERE user_id = ?");
$stmt->bind_param("i", $StudentID);
$stmt->execute();
$res = $stmt->get_result();
$studentData = $res->fetch_assoc();
$stmt->close();

if (!$studentData) {
    die("Student record not found.");
}

$course_id = intval($studentData['course_id']);
$division_id = intval($studentData['division_id']);

// --- Fetch schedule for that course & division ---
$sql = "
SELECT 
    ws.id,
    c.courseName AS course_name,
    d.division_name,
    s.subject_name,
    ws.faculty_name,
    ws.day_of_week,
    ws.date,
    ws.start_time,
    ws.end_time,
    ws.room_no
FROM schedule ws
LEFT JOIN courses c ON ws.course_id = c.id
LEFT JOIN divisions d ON ws.division_id = d.id
LEFT JOIN subjects s ON ws.subject_id = s.id
WHERE ws.course_id = ? AND ws.division_id = ?
ORDER BY FIELD(ws.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), ws.start_time
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $course_id, $division_id);
$stmt->execute();
$schedules = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Weekly Schedule</title>
    <link rel="stylesheet" href="../Css/Student.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 20px;
            margin-left: 250px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
        }
        h2 {
            color: #1a2a6c;
            margin-bottom: 20px;
        }
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #1a2a6c;
            color: white;
            text-align: left;
        }
        .day {
            font-weight: bold;
            color: #b21f1f;
        }
        .time {
            color: #1a2a6c;
            font-weight: 600;
        }
    </style>
    
        <link rel="stylesheet" href="../../Admin/Css/Admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
</head>
<body>
    <?php include 'sideMeneu.php'; ?>
    <div class="container">
        <h2>📅 My Weekly Schedule</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Faculty</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($schedules && $schedules->num_rows > 0): ?>
                        <?php while($row = $schedules->fetch_assoc()): ?>
                        <tr>
                            <td class="day"><?= htmlspecialchars($row['day_of_week']) ?></td>
                            <td><?= $row['date'] ? htmlspecialchars($row['date']) : '-' ?></td>
                            <td><?= htmlspecialchars($row['subject_name']) ?></td>
                            <td><?= htmlspecialchars($row['faculty_name']) ?></td>
                            <td class="time"><?= htmlspecialchars($row['start_time']) ?></td>
                            <td class="time"><?= htmlspecialchars($row['end_time']) ?></td>
                            <td><?= htmlspecialchars($row['room_no']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center;">No schedule found for your class.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
