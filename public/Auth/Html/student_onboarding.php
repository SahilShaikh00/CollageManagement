<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Onboarding</title>
    <style>
        :root {
            --primary: #1a2a6c;
            --secondary: #b21f1f;
            --accent: #fdbb2d;
            --dark: #0f1a45;
            --light: #f8f9fc;
            --gray: #6c757d;
            --success: #28a745;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: white;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary);
        }

        .header h2 {
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: var(--gray);
        }

        .form-container {
            background-color: var(--light);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -1rem;
        }

        .form-group {
            flex: 1 0 300px;
            padding: 0 1rem;
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(26, 42, 108, 0.2);
        }

        .submit-btn {
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 2rem auto 0;
            padding: 0.75rem 2rem;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .error {
            background-color: rgba(178, 31, 31, 0.1);
            color: var(--secondary);
            border: 1px solid var(--secondary);
        }

        @media (max-width: 768px) {
            .form-group {
                flex: 1 0 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Student Onboarding</h2>
            <p>Complete your profile to get started</p>
        </div>

        <?php
        require_once("../../../Database/Connection.php");
        session_start();

        // Ensure student is logged in
        if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student'){
            echo "<div class='message error'>Unauthorized access.</div>";
            exit;
        }

        $userId = $_SESSION['user']['id'];

        // Fetch dropdown data BEFORE rendering form
        $departments = $conn->query("SELECT * FROM departments");
        $courses = $conn->query("SELECT * FROM courses");
        $divisions = $conn->query("SELECT * FROM divisions");

        // Check if user already submitted
        $check = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
        $check->bind_param("i", $userId);
        $check->execute();
        $result = $check->get_result();

        if($result->num_rows > 0){
            echo "<div class='message success'>✅ You have already submitted your onboarding request. Waiting for admin approval.</div>";
        } else {
            // Handle form submission
            if($_SERVER['REQUEST_METHOD'] === 'POST'){
                $first_name = trim($_POST['first_name'] ?? '');
                $last_name = trim($_POST['last_name'] ?? '');
                $gender = trim($_POST['gender'] ?? '');
                $department_id = intval($_POST['department'] ?? 0);
                $course_id = intval($_POST['course'] ?? 0);
                $year = intval($_POST['year'] ?? 0);
                $division_id = intval($_POST['division'] ?? 0);

                if($first_name && $last_name && $gender && $department_id && $course_id && $year && $division_id){
                    $stmt = $conn->prepare("
                        INSERT INTO student_profiles 
                        (user_id, first_name, last_name, gender, department_id, course_id, year, division_id, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
                    ");
                    $stmt->bind_param(
                        "isssiiii",
                        $userId, $first_name, $last_name, $gender, $department_id, $course_id, $year, $division_id
                    );

                    if($stmt->execute()){
    // Redirect to StudentApproval.php after successful submission
    header("Location: ../Html/StudentAprovel.php");
    exit;
} else {
    echo "<div class='message error'>❌ Something went wrong while saving your data. Please try again.</div>";
}

                } else {
                    echo "<div class='message error'>⚠️ Please fill all required fields.</div>";
                }
            }
        ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="year">Year</label>
                        <select id="year" name="year" required>
                            <option value="">Select Year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <?php if($departments && $departments->num_rows > 0): ?>
                                <?php while($d = $departments->fetch_assoc()): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="">No departments found</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="course">Course</label>
                        <select id="course" name="course" required>
                            <option value="">Select Course</option>
                            <?php if($courses && $courses->num_rows > 0): ?>
                                <?php while($c = $courses->fetch_assoc()): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['courseName']) ?></option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="">No courses found</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="division">Division</label>
                        <select id="division" name="division" required>
                            <option value="">Select Division</option>
                            <?php if($divisions && $divisions->num_rows > 0): ?>
                                <?php while($v = $divisions->fetch_assoc()): ?>
                                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['division_name']) ?></option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="">No divisions found</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <!-- Empty for spacing -->
                    </div>
                </div>

                <button type="submit" class="submit-btn">Submit Onboarding Request</button>
            </form>
        </div>
        <?php } // end else ?>
    </div>
</body>
</html>
