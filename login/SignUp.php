<?php
session_start();

if (isset($_SESSION['Email_Session'])) {
    header("Location: welcome.php");
    die();
}
include('config.php');
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';
$msg = "";
$Error_Pass="";
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conx, $_POST['Username']);
    $email = mysqli_real_escape_string($conx, $_POST['Email']);
    $Password = mysqli_real_escape_string($conx, md5($_POST['Password']));
    $Confirm_Password = mysqli_real_escape_string($conx, md5($_POST['Conf-Password']));
    $Code = mysqli_real_escape_string($conx, md5(rand()));

    if (mysqli_num_rows(mysqli_query($conx, "SELECT * FROM register WHERE email='{$email}'")) > 0) {
        $msg = "<div class='alert alert-danger'>The email <strong>{$email}</strong> is already registered. Please try another one.</div>";
    } else {
        if ($Password === $Confirm_Password) {
            $query = "INSERT INTO register(`Username`, `email`, `Password`, `CodeV`) VALUES ('$name','$email','$Password','$Code')";
            $result = mysqli_query($conx, $query);

            if ($result) {
                // إعداد البريد الإلكتروني
                $mail = new PHPMailer(true);

                try {
                    // إعدادات الخادم
                    $mail->SMTPDebug = 0;
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'im.the.raff@gmail.com';
                    $mail->Password = 'aaad rjtr rlrg fqlq';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 25;

                    // المرسل والمستقبل
                    $mail->setFrom('im.the.raff@gmail.com', 'Eagle');
                    $mail->addAddress($email, $name);

                    // محتوى البريد
                    $verificationLink = "http://theraf.ct.ws/Verification/$Code";
                    $mail->isHTML(true);
                    $mail->Subject = 'Welcome to My Website!';
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; text-align: center;'>
                            <h1 style='color: #4CAF50;'>Welcome, $name!</h1>
                            <p style='font-size: 16px;'>Thank you for joining our platform. To complete your registration, please verify your email by clicking the button below:</p>
                            <a href='$verificationLink' style='display: inline-block; padding: 10px 20px; font-size: 18px; color: #fff; background-color: #4CAF50; text-decoration: none; border-radius: 5px;'>Verify Email</a>
                            <p style='margin-top: 20px; font-size: 14px; color: #777;'>If the button doesn't work, copy and paste the following link into your browser:</p>
                            <p><a href='$verificationLink'>$verificationLink</a></p>
                        </div>
                    ";

                    $mail->send();
                    $msg = "<div class='alert alert-info'>A verification link has been sent to your email address. Please check your inbox or spam folder.</div>";
                } catch (Exception $e) {
                    $msg = "<div class='alert alert-danger'>Could not send the email. Error: {$mail->ErrorInfo}</div>";
                }
            } else {
                $msg = "<div class='alert alert-danger'>Something went wrong. Please try again later.</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>Password and Confirm Password do not match. Please try again.</div>";
        }
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
    <div class="container sign-up-mode">
        <div class="forms-container">
            <div class="signin-signup">
                <form action="" method="POST" class="sign-up-form">
                    <h2 class="title">Sign up</h2>
                    <?php echo $msg ?>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="Username" placeholder="Username" value="<?php if (isset($_POST['Username'])) {
                                                                                                echo $name;
                                                                                            } ?>" />
                    </div>
                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="Email" placeholder="Email" value="<?php if (isset($_POST['Email'])) {
                                                                                        echo $email;
                                                                                    } ?>" />
                    </div>
                    <div class="input-field" <?php echo $Error_Pass?>>
                        <i class="fas fa-lock"></i>
                        <input type="password" name="Password" placeholder="Password" />
                    </div>
                    <div class="input-field" <?php echo $Error_Pass?>>
                        <i class="fas fa-lock"></i>
                        <input type="password" name="Conf-Password" placeholder="Confirm Password" />
                    </div>
                    <input type="submit" name="submit" class="btn" value="Sign up" />
                    <p class="social-text">Or Sign up with social platforms</p>
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
                        <a href="github-login.php" class="social-icon">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <div class="panels-container">
            <div class="panel left-panel">
            </div>
            <div class="panel right-panel">
                <div class="content">
                    <h3>One of us ?</h3>
                    <p>
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Nostrum
                        laboriosam ad deleniti.
                    </p>
                    <a href="index.php" class="btn transparent" id="sign-in-btn" style="padding:10px 20px;text-decoration:none">
                        Sign in
                                                                                </a>
                </div>
                <img src="img/register.svg" class="image" alt="" />
            </div>
        </div>

    </div>
    </div>
</body>

</html>
