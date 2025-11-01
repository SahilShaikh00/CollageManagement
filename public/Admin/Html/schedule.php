<?php
// manage_schedule.php
session_start();
require_once("../../../Database/Connection.php");

// --- AUTH CHECK (only admin allowed) ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../../Auth/Html/login.html");
    exit;
}

// Messages
$successMsg = '';
$errorMsg = '';

// --- HANDLE ADD SCHEDULE ---
if (isset($_POST['add_schedule'])) {
    $course_id = intval($_POST['course_id'] ?? 0);
    $subject_id = intval($_POST['subject_id'] ?? 0);
    $division_id = intval($_POST['division_id'] ?? 0);
    $faculty_name = trim($_POST['faculty_name'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $room_no = trim($_POST['room_no'] ?? '');
    $day_of_week = trim($_POST['day_of_week'] ?? '');

    // Basic validation
    if ($course_id <= 0 || $subject_id <= 0 || $division_id <= 0 || $faculty_name === '' || $day_of_week === '' || $start_time === '' || $end_time === '') {
        $errorMsg = "Please fill required fields: Course, Subject, Division, Faculty, Day, Start Time, End Time.";
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO schedule (course_id, subject_id, faculty_name, date, start_time, end_time, room_no, day_of_week, division_id)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssssi", $course_id, $subject_id, $faculty_name, $date, $start_time, $end_time, $room_no, $day_of_week, $division_id);
        if ($stmt->execute()) {
            $successMsg = "Schedule added successfully.";
        } else {
            $errorMsg = "Failed to add schedule: " . $stmt->error;
        }
        $stmt->close();
    }
}

// --- HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    if ($delId > 0) {
        $stmt = $conn->prepare("DELETE FROM schedule WHERE id = ?");
        $stmt->bind_param("i", $delId);
        if ($stmt->execute()) {
            $successMsg = "Schedule deleted.";
        } else {
            $errorMsg = "Failed to delete schedule: " . $stmt->error;
        }
        $stmt->close();
    }
}

// --- FETCH DROPDOWN DATA ---
$courses = $conn->query("SELECT id, courseName FROM courses ORDER BY courseName ASC");
$divisions = $conn->query("SELECT id, division_name FROM divisions ORDER BY division_name ASC");
$teachers = $conn->query("SELECT UserID, FullName FROM users WHERE role='teacher' ORDER BY FullName ASC");
$subjects = $conn->query("SELECT id, subject_name, course_id FROM subjects ORDER BY subject_name ASC");

// convert subjects to array for JS
$subjectsList = [];
while($s = $subjects->fetch_assoc()){
    $subjectsList[] = $s;
}

// days
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

// --- FETCH SCHEDULE LIST ---
$schedulesSql = "
    SELECT ws.id, c.courseName AS course_name, d.division_name, ws.day_of_week, ws.date, s.subject_name, ws.faculty_name, ws.start_time, ws.end_time, ws.room_no
    FROM schedule ws
    LEFT JOIN courses c ON ws.course_id = c.id
    LEFT JOIN divisions d ON ws.division_id = d.id
    LEFT JOIN subjects s ON ws.subject_id = s.id
    ORDER BY FIELD(ws.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), ws.start_time
";
$schedules = $conn->query($schedulesSql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    
    <title>Weekly Schedule Management</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../../Admin/Css/Admin.css">
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

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #e9eef8 100%);
        color: #222;
        margin: 0;
        padding: 30px;
        margin-left: 250px;
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
    }

    .btn-add {
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        color: #fff;
        box-shadow: 0 8px 20px rgba(26, 42, 108, 0.12);
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
        
        color: #000000;
        text-align: left;
        padding: 12px;
        font-weight: 600;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid #f1f3f7;
        vertical-align: middle;
    }

    .day-badge {
        padding: 6px 10px;
        border-radius: 20px;
        font-weight: 700;
        background: linear-gradient(90deg, var(--accent), #ffd166);
        color: var(--dark);
        display: inline-block;
    }

    .time-cell {
        font-weight: 700;
        color: var(--primary);
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .alert.success {
        background: #e9f7ee;
        color: #28a745;
    }

    .alert.error {
        background: #fdecea;
        color: #c82333;
    }

    @media (max-width:900px) {
        body {
            padding: 16px;
            margin-left: 0;
        }
    }
    </style>

    <script>
    // subjects array from PHP
    const subjects = <?php echo json_encode($subjectsList); ?>;

    // filter subjects by course without AJAX
    function loadSubjects(courseId) {
        const target = document.getElementById('subjectDropdown');
        target.innerHTML = '';

        if (!courseId) {
            target.innerHTML = '<option value="">Select course first</option>';
            return;
        }

        const filtered = subjects.filter(s => s.course_id == courseId);

        if (filtered.length === 0) {
            target.innerHTML = '<option value="">No subjects found</option>';
            return;
        }

        target.innerHTML = '<option value="">Select Subject</option>';
        filtered.forEach(sub => {
            const opt = document.createElement('option');
            opt.value = sub.id;
            opt.textContent = sub.subject_name;
            target.appendChild(opt);
        });
    }
    </script>
</head>

<body>
    <?php include 'side_menu.php'; ?>

    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-calendar-alt"></i>&nbsp; Weekly Schedule Management</h2>
        </div>

        <?php if($successMsg): ?>
        <div class="alert success"><?= htmlspecialchars($successMsg) ?></div>
        <?php endif; ?>
        <?php if($errorMsg): ?>
        <div class="alert error"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><i class="fas fa-plus-circle"></i> Add Weekly Schedule</div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Course</label>
                            <select name="course_id" class="form-control" onchange="loadSubjects(this.value)" required>
                                <option value="">Select Course</option>
                                <?php while($c = $courses->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['courseName']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Division</label>
                            <select name="division_id" class="form-control" required>
                                <option value="">Select Division</option>
                                <?php while($d = $divisions->fetch_assoc()): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['division_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Day of Week</label>
                            <select name="day_of_week" class="form-control" required>
                                <option value="">Select Day</option>
                                <?php foreach($days as $day): ?>
                                <option value="<?= $day ?>"><?= $day ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Subject</label>
                            <select name="subject_id" id="subjectDropdown" class="form-control" required>
                                <option value="">Select Subject (choose course first)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Faculty</label>
                            <select name="faculty_name" class="form-control" required>
                                <option value="">Select Faculty</option>
                                <?php while($t = $teachers->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($t['FullName']) ?>">
                                    <?= htmlspecialchars($t['FullName']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Date (optional)</label>
                            <input type="date" name="date" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time" name="start_time" class="form-control" required />
                        </div>

                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time" name="end_time" class="form-control" required />
                        </div>

                        <div class="form-group">
                            <label>Room No</label>
                            <input type="text" name="room_no" class="form-control" placeholder="Room / Lab no" />
                        </div>

                        <div style="grid-column:1 / -1; margin-top:6px;">
                            <button type="submit" name="add_schedule" class="btn btn-add"><i
                                    class="fas fa-plus"></i>&nbsp; Add Schedule</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- list schedules -->
        <div class="card">
            <div class="card-header"><i class="fas fa-list"></i> All Schedules</div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Course</th>
                                <th>Division</th>
                                <th>Day</th>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Faculty</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Room</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($schedules && $schedules->num_rows): ?>
                            <?php while($row = $schedules->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['course_name']) ?></td>
                                <td><?= htmlspecialchars($row['division_name']) ?></td>
                                <td><span class="day-badge"><?= htmlspecialchars($row['day_of_week']) ?></span></td>
                                <td><?= $row['date'] ? htmlspecialchars($row['date']) : '-' ?></td>
                                <td><?= htmlspecialchars($row['subject_name']) ?></td>
                                <td><?= htmlspecialchars($row['faculty_name']) ?></td>
                                <td class="time-cell"><?= htmlspecialchars($row['start_time']) ?></td>
                                <td class="time-cell"><?= htmlspecialchars($row['end_time']) ?></td>
                                <td><?= htmlspecialchars($row['room_no']) ?></td>
                                <td>
                                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-delete"
                                        onclick="return confirm('Delete this schedule?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="11" style="text-align:center; padding:22px;">No schedules found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>