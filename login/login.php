<?php
session_start();

if (isset($_SESSION['Email_Session'])) {
  header("Location: welcome.php");
  exit(); // تأكد من عدم وجود إعادة توجيه مستمرة
}

include('config.php');
$msg = "";
$Error_Pass = "";

if (isset($_GET['Verification'])) {
  // الحصول على الكود من الرابط والتحقق منه في قاعدة البيانات
  $raquet = mysqli_query($conx, "SELECT * FROM register WHERE CodeV='{$_GET['Verification']}'");
  if (mysqli_num_rows($raquet) > 0) {
    $query = mysqli_query($conx, "UPDATE register SET verification='1' WHERE CodeV='{$_GET['Verification']}'");
    if ($query) {
      $rowv = mysqli_fetch_assoc($raquet);
      // إعادة التوجيه بشكل آمن بعد التحقق
      header("Location: welcome.php?id={$rowv['id']}");
      exit(); // تأكد من عدم وجود إعادة توجيه مستمرة
    } else {
      // في حال فشل التحقق
      header("Location: login.php");
      exit();
    }
  } else {
    // في حال عدم العثور على الكود
    header("Location: login.php");
    exit();
  }
}

if (isset($_POST['submit'])) {
  $email = mysqli_real_escape_string($conx, $_POST['email']);
  $Pass = mysqli_real_escape_string($conx, md5($_POST['Password']));
  $sql = "SELECT * FROM register WHERE email='{$email}' and Password='{$Pass}'";
  $resulte = mysqli_query($conx, $sql);

  if (mysqli_num_rows($resulte) === 1) {
    $row = mysqli_fetch_assoc($resulte);

    if ($row['verification'] === '1') {
      $_SESSION['Email_Session'] = $email;

      // تعيين الكوكيز بشكل آمن هنا
      setcookie('user_session_RaF', session_id(), time() + 3600, '/', '', false, true); // HttpOnly

      // إعادة التوجيه بشكل آمن إلى welcome.php
      header("Location: welcome.php");
      exit(); // تأكد من عدم وجود إعادة توجيه مستمرة
    } else {
      $msg = "<div class='alert alert-info'>
                First, verify your account.
                <a href='#' onclick='warningAlert(\"{$email}\")' class='alert-link'>Resend Verification Code</a>
              </div>";
    }
  } else {
    $msg = "<div class='alert alert-danger'>Email or Password is not match</div>";
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    .Forget-Pass{
      display: flex;
      width: 65%;
    }
    .Forget{
      color: #2E9AFE;
      font-weight: 500;
      text-decoration: none;
      margin-left: auto;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="forms-container">
      <div class="signin-signup">
        <form action="" method="POST" class="sign-in-form">
          <h2 class="title">Sign in</h2>
          <?php echo $msg ?>
          <div class="input-field">
            <i class="fas fa-user"></i>
            <input type="text" name="email" placeholder="Email" />
          </div>
          <div class="input-field">
            <i class="fas fa-lock"></i>
            <input type="password" name="Password" placeholder="Password" />
          </div>
          <div class="Forget-Pass">
            <a href="4/Forget.php" class="Forget">Forget Password ?</a>
          </div>
          <input type="submit" name="submit" value="Login" class="btn solid" />
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
              // إضافة زر لتسجيل الدخول عبر GitHub
<p class="social-text">Or Sign in with:</p>
<div class="social-media">
  <a href="4/github.php" class="social-icon">
    <i class="fab fa-github"></i>
  </a>
</div>
            </a>
          </div>
        </form>
      </div>
    </div>

    <div class="panels-container">
      <div class="panel left-panel">
        <div class="content">
          <h3>New here ?</h3>
          <p>
            Lorem ipsum, dolor sit amet consectetur adipisicing elit. Debitis,
            ex ratione. Aliquid!
          </p>
          <a href="SignUp.php" class="btn transparent" id="sign-in-btn" style="padding:10px 20px;text-decoration:none">
            Sign up
          </a>
        </div>
        <img src="img/log.svg" class="image" alt="" />
      </div>
    </div>
  </div>

  <script>
    function warningAlert(email) {
      Swal.fire({
        title: 'دير بالك!',
        text: 'متأكد تكمّل؟ هالخطوة ما ترجع.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'إي، أكمل',
        cancelButtonText: 'لا، تراجع'
      }).then((result) => {
        if (result.isConfirmed) {
          // هذه النافذة الجديدة تؤكد إرسال رمز التحقق
          successAlert(email);
        }
      });
    }

function successAlert(email) {
  Swal.fire({
    title: 'تم إرسال رمز التحقق!',
    text: 'تم إرسال رمز التحقق إلى البريد الإلكتروني: ' + email,
    icon: 'success',
    confirmButtonText: 'حلو'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('http://theraf.ct.ws/4/resend/' + email)  // استخدام الرابط الجديد هنا
        .then(response => response.text())
        .then(data => {
          // هنا يمكنك إضافة أي منطق إضافي إذا أردت التعامل مع البيانات المستلمة
        })
        .catch(error => console.error('Error:', error));
    }
  });
}
  </script>
</body>
</html>