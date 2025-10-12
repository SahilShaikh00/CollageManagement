<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    http_response_code(200);
    exit;
}

// Set headers for actual requests
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once("../Database/Connection.php");
require_once("../vendor/autoload.php");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$config = require __DIR__ . "/../Config/appsetting.php";
$secretKey = $config['jwt_secret'];
$algo = $config['jwt_algo'];
$tokenExpiry = $config['jwt_exp'];

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
        $issuedAt = time();
        $expire = $issuedAt + 3600;
         $payload = [
        'exp' => $expire,
        'sub' => $userId,
        'email' => $email,
        'role' => $role 
    ];
    
    $jwt = JWT::encode($payload, $secretKey, $algo);
     echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "token" => $jwt,
        "user" => [
            "id" => $payload['sub'],
            "email" => $payload['email'],
            "role" => $payload['role']
        ]
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

    $userExist = $conn->prepare("SELECT * FROM users  WHERE UserName = ? AND Password = ?");
    $userExist -> bind_param("ss" , $email , $password);
    $userExist -> execute();
    $result =  $userExist -> get_result();

  if($result -> num_rows > 0 ){
    $user = $result -> fetch_assoc();
     $issuedAt = time();
     $expire = $issuedAt + 3600;
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expire,
        'sub' => $user['UserID'],
        'email' => $user['UserName'],
        'role' => $user['role']
    ];
    
    $jwt = JWT::encode($payload, $secretKey, $algo);
     echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "token" => $jwt,
        "user" => [
            "id" => $payload['sub'],
            "email" => $payload['email'],
            "role" => $payload['role']
        ]
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