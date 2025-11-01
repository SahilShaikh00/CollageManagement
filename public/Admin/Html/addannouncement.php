<?php
session_start();
require_once("../../../Database/Connection.php");

// --- AUTH CHECK (Only Admin Allowed) ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../../Auth/Html/login.html");
    exit;
}

$user = $_SESSION['user'];
$UserID = $user['id'];

$successMsg = '';
$errorMsg = '';

// Fetch Courses & Divisions
$courses = mysqli_query($conn, "SELECT id, courseName FROM courses");
$divisions = mysqli_query($conn, "SELECT id, division_name FROM divisions");

// Initialize form field variables
$title = $message = $course_id = $division_id = $start_date = $end_date = '';

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    $course_id = !empty($_POST['course_id']) ? intval($_POST['course_id']) : "NULL";
    $division_id = !empty($_POST['division_id']) ? intval($_POST['division_id']) : "NULL";
    $start_date = !empty($_POST['start_date']) ? mysqli_real_escape_string($conn, $_POST['start_date']) : "NULL";
    $end_date = !empty($_POST['end_date']) ? mysqli_real_escape_string($conn, $_POST['end_date']) : "NULL";
    $created_by = intval($user['id']);

    if ($title !== '' && $message !== '') {
        // Prepare the query with proper NULL handling
        $query = "
            INSERT INTO announcements (title, message, course_id, division_id, start_date, end_date, created_by)
            VALUES (
                '$title', 
                '$message', 
                " . ($course_id === "NULL" ? "NULL" : $course_id) . ",
                " . ($division_id === "NULL" ? "NULL" : $division_id) . ",
                " . ($start_date === "NULL" ? "NULL" : "'$start_date'") . ",
                " . ($end_date === "NULL" ? "NULL" : "'$end_date'") . ",
                $created_by
            )
        ";

        if (mysqli_query($conn, $query)) {
            $successMsg = "✅ Announcement created successfully!";
            // Clear form fields on success
            $title = $message = '';
            $course_id = $division_id = "NULL";
            $start_date = $end_date = "NULL";
        } else {
            $errorMsg = "❌ Error: " . mysqli_error($conn);
        }
    } else {
        $errorMsg = "Please fill all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Announcement</title>
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
        max-width: 800px;
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
        padding: 25px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 20px;
    }

    label {
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-control {
        padding: 12px 16px;
        border-radius: 8px;
        border: 1px solid #e6e9ef;
        background: #fff;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        box-shadow: 0 6px 18px rgba(26, 42, 108, 0.08);
        border-color: var(--primary);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        font-size: 15px;
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

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .alert {
        padding: 14px 18px;
        border-radius: 8px;
        margin-bottom: 20px;
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
        margin-top: 25px;
        gap: 12px;
    }

    .fade-out {
        animation: fadeOut 0.5s ease-out forwards;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
            height: 0;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
    }

    @media (max-width: 900px) {
        body {
            padding: 20px;
            margin-left: 0;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .footer-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 0 10px;
        }
        
        .card-body {
            padding: 20px 15px;
        }
        
        .page-header h2 {
            font-size: 24px;
        }
    }
    </style>
</head>
<body>
    <?php include 'side_menu.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-bullhorn"></i>&nbsp; Create Announcement</h2>
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

        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Announcement Details
            </div>
            <div class="card-body">
                <form method="POST" id="announcementForm">
                    <div class="form-group">
                        <label class="required">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter announcement title" 
                               value="<?= htmlspecialchars($title) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="required">Message</label>
                        <textarea name="message" class="form-control" placeholder="Enter announcement message" 
                                  rows="5" required><?= htmlspecialchars($message) ?></textarea>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Course</label>
                            <select name="course_id" class="form-control">
                                <option value="">All Courses</option>
                                <?php 
                                $courses_data = mysqli_query($conn, "SELECT id, courseName FROM courses");
                                while ($c = mysqli_fetch_assoc($courses_data)) { 
                                    $selected = ($course_id !== "NULL" && $course_id == $c['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $c['id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($c['courseName']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Division</label>
                            <select name="division_id" class="form-control">
                                <option value="">All Divisions</option>
                                <?php 
                                $divisions_data = mysqli_query($conn, "SELECT id, division_name FROM divisions");
                                while ($d = mysqli_fetch_assoc($divisions_data)) { 
                                    $selected = ($division_id !== "NULL" && $division_id == $d['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $d['id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($d['division_name']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" 
                                   value="<?= ($start_date !== "NULL") ? $start_date : '' ?>">
                        </div>

                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" 
                                   value="<?= ($end_date !== "NULL") ? $end_date : '' ?>">
                        </div>
                    </div>

                    <div class="footer-actions">
                        <button type="reset" class="btn btn-primary" onclick="clearForm()">
                            <i class="fas fa-redo"></i> Reset Form
                        </button>
                        <button type="submit" class="btn btn-accent">
                            <i class="fas fa-paper-plane"></i> Create Announcement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide success message after 5 seconds
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
            
            // Set default dates if not already set
            const startDateInput = document.querySelector('input[name="start_date"]');
            if (startDateInput && !startDateInput.value) {
                startDateInput.value = new Date().toISOString().split('T')[0];
            }
            
            const endDateInput = document.querySelector('input[name="end_date"]');
            if (endDateInput && !endDateInput.value) {
                const nextWeek = new Date();
                nextWeek.setDate(nextWeek.getDate() + 7);
                endDateInput.value = nextWeek.toISOString().split('T')[0];
            }
        });

        function clearForm() {
            // Clear any existing alerts
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');
            
            if (successAlert) successAlert.remove();
            if (errorAlert) errorAlert.remove();
            
            // Reset dates to defaults
            const startDateInput = document.querySelector('input[name="start_date"]');
            const endDateInput = document.querySelector('input[name="end_date"]');
            
            if (startDateInput) {
                startDateInput.value = new Date().toISOString().split('T')[0];
            }
            
            if (endDateInput) {
                const nextWeek = new Date();
                nextWeek.setDate(nextWeek.getDate() + 7);
                endDateInput.value = nextWeek.toISOString().split('T')[0];
            }
        }
    </script>
</body>
</html>