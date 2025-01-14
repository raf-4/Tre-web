<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('config.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if (isset($_GET['email'])) {
    $email = mysqli_real_escape_string($conx, $_GET['email']);
    // استعلام للتحقق من وجود البريد في قاعدة البيانات
    $sql = "SELECT * FROM register WHERE email='{$email}'";
    $result = mysqli_query($conx, $sql);

    if (mysqli_num_rows($result) > 0) {
        // توليد كود تحقق عشوائي
        $verification_code = md5(rand());
        $update_query = "UPDATE register SET CodeV='{$verification_code}' WHERE email='{$email}'";

        if (mysqli_query($conx, $update_query)) {
            // إعدادات PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();                                            // Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                     // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                $mail->Username   = 'im.the.raff@gmail.com';                     // SMTP username
                $mail->Password   = 'aaad rjtr rlrg fqlq';                               // SMTP password
                $mail->SMTPSecure = 'tls';            // Enable TLS encryption
                $mail->Port       = 587;                                    // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                $mail->setFrom('im.the.raff@gmail.com', 'Eagle');
                $mail->addAddress($email);   // Add a recipient

                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = 'Verification Code';
                $mail->Body    = '<p>Click here to verify your email: <b><a href="http://theraf.ct.ws/4/change-Password.php?Verification=' . urlencode($verification_code) . '">Verify</a></b></p>';

                // Send email
                if ($mail->send()) {
                    echo "<div class='alert alert-info'>Verification code has been sent to your email.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Failed to send email. Please try again later.</div>";
                }

            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>Mailer Error: {$mail->ErrorInfo}</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Failed to update the verification code.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Email not found in the system.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Email parameter is missing in the URL.</div>";
}
?>