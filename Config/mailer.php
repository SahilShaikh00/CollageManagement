<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer-master/src/Exception.php';
require __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer-master/src/SMTP.php';

function sendMail($toEmail,$subject,$body){
    $mail = new  PHPMailer(true);
    try{
        $mail ->isSMTP();
        $mail -> Host = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '4621sahilshaikh@gmail.com'; 
        $mail->Password   = 'you-PassWord'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587;

        $mail -> setFrom('4621sahilshaikh@gmail.com' ,'Education System Management');
        $mail -> addAddress($toEmail);

        $mail-> isHTML(true);
        $mail-> Subject = $subject;
        $mail -> Body = $body;

        $mail-> send();
        echo json_encode(["status" => "success", "message" => "Email sent successfully"]);
  

    }catch(Exception $e){
     return "Mailer Error: {$mail->ErrorInfo}";
    }
}

?>