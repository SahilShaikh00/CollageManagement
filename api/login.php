<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Safe header helpers for environments where header() or http_response_code() may be unavailable
function safe_header($string) {
    if (function_exists('header')) {
        header($string);
    }
}


function safe_http_response_code($code) {
    if (function_exists('http_response_code')) {
        http_response_code($code);
    } else {
        // Fallback to sending a status header if possible
        if (function_exists('header')) {
            $status_texts = [
                200 => 'OK',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not Found',
                500 => 'Internal Server Error',
            ];
            $text = isset($status_texts[$code]) ? $status_texts[$code] : '';
            header(sprintf('HTTP/1.1 %d %s', $code, $text));
        }
    }
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    safe_header("Access-Control-Allow-Origin: *");
    safe_header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    safe_header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    safe_http_response_code(200);
    exit;
}

// Set headers for actual requests
safe_header("Content-Type: application/json; charset=UTF-8");
safe_header("Access-Control-Allow-Origin: *");
safe_header("Access-Control-Allow-Methods: POST, OPTIONS");
safe_header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once("../Database/Connection.php");

// Start session for authentication
session_start();

// Get JSON input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Check if JSON decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit;
}

if(!isset($data['action'])){
    echo json_encode(["success" => false, "message" => "Action required (signUp or Login)"]);
    exit;
}

$action = $data['action'];

if($action === "signUp"){
    if(!isset($data['email']) || !isset($data['password'])){
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }
    
    $email = trim($data['email']);
    $password = trim($data['password']);    
    $role = isset($input['role']) ? trim($input['role']) : 'student';

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit;
    }
     if ($role !== 'student') {
        $role = 'student';
    }
    
    // Check if user already exists
    $userExist = $conn->prepare("SELECT UserName FROM users WHERE UserName = ?");
    $userExist->bind_param("s", $email);
    $userExist->execute();
    $result = $userExist->get_result();

    if($result->num_rows > 0){
        echo json_encode(["success" => false, "message" => "User already exists"]);
        exit;
    }

    // Hash the passwaord for security
    
    $newUser = $conn->prepare("INSERT INTO users (UserName, Password,role) VALUES (?, ?,?)");
    $newUser->bind_param("sss", $email, $password ,$role);
    
    if($newUser->execute()){
        $userId = $conn->insert_id;
        // Store user data in session
        $_SESSION['user'] = [
            'id' => $userId,
            'email' => $email,
            'role' => $role
        ];
        echo json_encode([
            "success" => true,
            "message" => "Signup successful"
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Signup failed: " . $conn->error]);
    }

} elseif ($action === "login") {
    
    if(!isset($data['email']) || !isset($data['password'])){
        echo json_encode(["success" => false , "message" => "Email and Password Are Requried"]);
    }

    $email = trim( $data['email']);
    $password = trim($data['password']);
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        echo json_encode(["success" => false,"message" => "Invalid email format"]);
        exit;
    }

    $userExist = $conn->prepare("SELECT u.UserID, u.UserName, u.role, s.status
                             FROM users u
                             LEFT JOIN student_profiles s ON u.UserID = s.user_id
                             WHERE u.UserName = ? AND u.Password = ? ");
    $userExist -> bind_param("ss" , $email , $password);
    $userExist -> execute();
    $result =  $userExist -> get_result();

  if($result -> num_rows > 0 ){
    $user = $result -> fetch_assoc();

    if($user['role'] === 'student' && $user['status'] !== 'approved'){
        echo json_encode([
            "success" => false,
            "status" => $user['status'],
            "message" => "Your account is pending approval by admin/teacher."
        ]);
        $_SESSION['user'] = [
        'id' => $user['UserID'],
        'email' => $user['UserName'],
        'role' => $user['role']
        
     ];
   
        exit;
    }
    // Store user data in session
    $_SESSION['user'] = [
        'id' => $user['UserID'],
        'email' => $user['UserName'],
        'role' => $user['role']
    ];
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
         "role" => $user['role'],
                
    ]);
    
  }else{
    echo json_encode(["success" => false , "message" => "Worng Email or Password"]);
  }
   
    
}
else{
        echo json_encode(["success" => false, "message" => "Invalid action. Use 'signUp' or 'login'"]);
}

// Close connection
$conn->close();
?>