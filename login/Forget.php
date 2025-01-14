<?php
session_start();
if (isset($_SESSION['Email_Session'])) {
    header("Location: welcome.php");
    die();
}

include('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

$msg = "";
if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conx, $_POST['email']);
    $CodeReset = mysqli_real_escape_string($conx, md5(rand()));
    if (mysqli_num_rows(mysqli_query($conx, "SELECT * FROM register WHERE email='{$email}'")) > 0) {
        $query = mysqli_query($conx, "UPDATE register SET CodeV='{$CodeReset}' WHERE email='{$email}'");
        if ($query) {
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->SMTPDebug = 0;                      // Disable verbose debug output
                $mail->isSMTP();                                            // Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                     // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                $mail->Username   = 'im.the.raff@gmail.com';                     // SMTP username
                $mail->Password   = 'aaad rjtr rlrg fqlq';                               // SMTP password
                $mail->SMTPSecure = 'Tls';            // Enable implicit TLS encryption
                $mail->Port       = 25;                                    // TCP port to connect to

                // Recipients
                $mail->setFrom('im.the.raff@gmail.com', 'Eagle Team');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = 'Password Reset Request';

                $mail->Body = '
                <div style="font-family: Arial, sans-serif; text-align: center; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
                    <img src="https://via.placeholder.com/100" alt="Eagle Logo" style="width: 100px; margin-bottom: 20px;">
                    <h2 style="color: #333;">Reset Your Password</h2>
                    <p style="color: #555; font-size: 16px;">
                        Hi there! We received a request to reset your password. Click the button below to proceed:
                    </p>
                    <a href="http://theraf.ct.ws/change-Password.php?Reset=' . $CodeReset . '" 
                       style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: #fff; text-decoration: none; border-radius: 5px; font-size: 16px; margin-top: 20px;">
                        Reset Password
                    </a>
                    <p style="color: #999; font-size: 12px; margin-top: 20px;">
                        If you didn\'t request this, please ignore this email or contact our support team.
                    </p>
                    <p style="color: #aaa; font-size: 12px; margin-top: 10px;">
                        © 2024 Eagle Team. All rights reserved.
                    </p>
                </div>';

                $mail->send();

                // Success message
                $msg = "
                <div class='alert alert-success' style='text-align: center; font-family: Arial, sans-serif;'>
                    <h4>Password Reset Email Sent</h4>
                    <p>We\'ve sent a password reset email to <strong>{$email}</strong>. Please check your inbox or spam folder.</p>
                </div>";
            } catch (Exception $e) {
                // Error message
                $msg = "
                <div class='alert alert-danger' style='text-align: center; font-family: Arial, sans-serif;'>
                    <h4>Error Sending Email</h4>
                    <p>Oops! Something went wrong while sending the email. Please try again later.</p>
                </div>";
            }
        }
    } else {
        // Email not found message
        $msg = "
        <div class='alert alert-danger' style='text-align: center; font-family: Arial, sans-serif;'>
            <h4>Email Not Found</h4>
            <p>The email address <strong>{$email}</strong> was not found in our system. Please double-check and try again.</p>
        </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css" />
    <title>Sign in & Sign up Form</title>
    <style>
        .alert {
            padding: 1rem;
            border-radius: 5px;
            color: white;
            margin: 1rem 0;
            font-weight: 500;
            width: 65%;
        }

        .alert-success {
            background-color: #42ba96;
        }

        .alert-danger {
            background-color: #fc5555;
        }

        .alert-info {
            background-color: #2E9AFE;
        }

        .alert-warning {
            background-color: #ff9966;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="forms-container">
            <div class="signin-signup" style="left: 50%;z-index:99;">
                <form action="" method="POST" class="sign-in-form">
                    <h2 class="title">Forget Password</h2>
                    <?php echo $msg ?>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="email" placeholder="Email" />
                    </div>
                    <input type="submit" name="submit" value="Send" class="btn solid" />
                    <p class="social-text">Or Sign in with social platforms</p>
                    <div class="social-media">
                        <a href="#" class="social-icon">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-google"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        
    </div>

    <script src="app.js"></script>
</body>

</html>
