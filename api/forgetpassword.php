<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    http_response_code(200);
    exit;
}



header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once("../Database/Connection.php");
require __DIR__ . '/../Config/mailer.php';

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit;
}

if(!isset($data['action'])){
    echo json_encode(["success" => false, "message" => "Action required (signUp or Login)"]);
    exit;
}

$action = $data['action'];

if($action === 'sendotp'){
     if(!isset($data['email'])){
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    $email = trim($data['email']);
     if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit;
    }

     $userExist = $conn->prepare("SELECT * FROM users WHERE UserName = ?");
     $userExist -> bind_param("s",$email);
     $userExist -> execute();
     $result = $userExist ->get_result();

     if($result -> num_rows === 0){

     echo json_encode(["success" => false , "message" => "user Dont Exist"]);
     exit;
     }
     date_default_timezone_set("Asia/Kolkata");
         $otp = rand(100000, 999999);
         $expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));

         $deleteExistOtp = $conn -> prepare("DELETE FROM otps WHERE Email = ?");
         $deleteExistOtp -> bind_param("s",$email);
         $deleteExistOtp -> execute();

         $storedOtp = $conn -> prepare("INSERT INTO otps (Email ,otp,ExpireAt)VALUES (?,?,?)");
         $storedOtp -> bind_param("sss" ,$email ,$otp ,$expires_at );


     
         if($storedOtp -> execute()){
           $body = "
        <h2>Password Reset Request</h2>
        <p>Hi,</p>
        <p>You requested to reset your password. Use the following verification code to proceed:</p>
        <h3 style='color: #2e6da4;'>$otp</h3>
        <p>This code will expire in 2 minutes.</p>
        <p>If you didn't request this, please ignore this email.</p>
    ";
        $subject = "Your Verification Code";
            $mailsend = sendMail($email,$subject,$body);
            exit;
         }              

}elseif($action === 'verify'){
   $email = $data['email'];
   $otp = $data['otp'];

   $Verifyotp = $conn -> prepare("SELECT * FROM otps WHERE Email = ? AND otp = ? AND ExpireAt  > NOW()");
   $Verifyotp -> bind_param("ss" , $email , $otp);
   $Verifyotp -> execute();
   $result = $Verifyotp -> get_result();

   if($result -> num_rows > 0){
    
    echo json_encode(["success" => true, "message" => "OTP verified"]);
   }else{
      echo json_encode(["success" => false, "message" => "Invalid or expired OTP"]);
   }
}
elseif($action === 'resetPassword'){
    $email = $data['email'];
    $password = $data['password'];

    $setpassword = $conn -> prepare("UPDATE users SET  Password = ? WHERE UserName = ? ");
    $setpassword -> bind_param("ss" , $password , $email);
   if ($setpassword->execute()) {
    echo json_encode(["success" => true, "message" => "Password updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update password"]);
}

}else{
        echo json_encode(["success" => false, "message" => "Invalid action. Use 'Reset' or 'Sendotp'"]);
}

$conn->close();
?>