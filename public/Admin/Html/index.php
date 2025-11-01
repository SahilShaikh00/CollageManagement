<?php
session_start();
include '../../../Database/Connection.php';

// --- AUTH CHECK ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../../Auth/Html/login.html");
    exit();
}

$user = $_SESSION['user'];

// --- FETCH DASHBOARD DATA ---
function getCount($conn, $table) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM $table");
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

$totalStudents = getCount($conn, "student_profiles");
$totalFaculty = getCount($conn, "divisions"); // or create separate faculty table later
$totalCourses = getCount($conn, "courses");
$totalDepartments = getCount($conn, "departments");
$totalSubjects = getCount($conn, "subjects");

// --- FETCH ANNOUNCEMENTS ---
$announcements = [];
$announcementQuery = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5");
if ($announcementQuery && $announcementQuery->num_rows > 0) {
    while ($row = $announcementQuery->fetch_assoc()) {
        $announcements[] = $row;
    }
}

$dateToday = date("F j, Y");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartEdu - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Css/Admin.css">
</head>

<body>
    <div class="main">
        <div class="overlay" id="overlay"></div>
        <?php include 'side_menu.php'; ?>

        <div class="content">
            <div class="nav">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="user-profile">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user['email']); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($user['role']); ?></div>
                    </div>
                    <div class="user-img">
                        <?php echo strtoupper(substr($user['email'], 0, 2)); ?>
                    </div>
                </div>
            </div>

            <div class="dashboard">
                <div class="welcome-banner">
                    <div class="welcome-text">
                        <h2>Welcome to College Management System</h2>
                        <p>Manage students, faculty, courses, and more from one dashboard</p>
                    </div>
                    <div class="today-date">
                        <i class="fas fa-calendar"></i> <?php echo $dateToday; ?>
                    </div>
                </div>

                <!-- STATISTICS -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(26, 42, 108, 0.1); color: #1a2a6c;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-text">
                            <h3><?php echo $totalStudents; ?></h3>
                            <p>Total Students</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(178, 31, 31, 0.1); color: #b21f1f;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="stat-text">
                            <h3><?php echo $totalFaculty; ?></h3>
                            <p>Faculty / Divisions</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(253, 187, 45, 0.1); color: #fdbb2d;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-text">
                            <h3><?php echo $totalCourses; ?></h3>
                            <p>Courses</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(108, 117, 125, 0.1); color: #6c757d;">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-text">
                            <h3><?php echo $totalDepartments; ?></h3>
                            <p>Departments</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745;">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-text">
                            <h3><?php echo $totalSubjects; ?></h3>
                            <p>Subjects</p>
                        </div>
                    </div>
                </div>

                <!-- DYNAMIC ANNOUNCEMENTS -->
                <div class="card-container">
                    <?php if (count($announcements) > 0): ?>
                    <?php foreach ($announcements as $a): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-bullhorn"></i> <?php echo htmlspecialchars($a['title']); ?></h3>
                        </div>
                        <div class="card-body">
                            <p class="AnnPara"><?php echo htmlspecialchars($a['message']); ?></p>
                            <div class="card-footer"
                                style="display: flex; flex-direction: column; gap: 4px; font-size: 0.9rem; color: #555;">
                                <div>
                                    <i class="fas fa-calendar-plus" style="color:#1a2a6c; margin-right:5px;"></i>
                                    <strong>Start:</strong>
                                    <?php echo date("F j, Y", strtotime($a['start_date'])); ?>
                                </div>
                                <div>
                                    <i class="fas fa-calendar-minus" style="color:#b21f1f; margin-right:5px;"></i>
                                    <strong>End:</strong>
                                    <?php echo date("F j, Y", strtotime($a['end_date'])); ?>
                                </div>
                                <div>
                                    <i class="fas fa-clock" style="color:#6c757d; margin-right:5px;"></i>
                                    <strong>Posted:</strong>
                                    <?php echo date("F j, Y", strtotime($a['created_at'])); ?>
                                </div>
                            </div>

                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-bullhorn"></i> No Announcements</h3>
                        </div>
                        <div class="card-body">
                            <p class="AnnPara">There are currently no announcements.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <script src="../Script/Admin.js"></script>
</body>

</html>