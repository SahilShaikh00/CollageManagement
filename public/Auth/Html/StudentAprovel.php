<?php
session_start();
require_once("../../../Database/Connection.php");

// Check if student is logged in
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student'){
    header("Location: login.php");
    exit;
}   

$userId = $_SESSION['user']['id'];

// Get student profile status
$result = $conn->query("SELECT status FROM student_profiles WHERE user_id=$userId LIMIT 1");
if($result->num_rows > 0){
    $profile = $result->fetch_assoc();
    $status = $profile['status'];
} else {
    die("Profile not found. Contact admin.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
</head>
<body>

<?php
if($status === 'pending'){
    echo "<h2>Your account is pending approval by admin/teacher.</h2>
          <p>Please wait until your request is approved. You cannot access the dashboard yet.</p>";
} elseif($status === 'rejected'){
    echo "<h2>Your account registration has been rejected by admin/teacher.</h2>
          <p>Contact the admin for further details.</p>";
} elseif($status === 'approved'){
    echo "<h2>Welcome to your Dashboard!</h2>
          <p>You have full access now.</p>";
    // Here you can include the actual dashboard content
} else {
    echo "<h2>Unknown status. Contact admin.</h2>";
}
?>

</body>
</html>
