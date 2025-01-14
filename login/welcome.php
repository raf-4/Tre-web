<?php
// إعدادات الحماية للجلسة
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

// إعداد الكوكيز للجلسة
session_set_cookie_params([
    'lifetime' => 0, // انتهاء الجلسة عند إغلاق المتصفح
    'path' => '/', // جعل الجلسة صالحة في جميع صفحات الموقع
    'domain' => 'theraf.ct.ws', // وضع نطاق الموقع
    'secure' => false, // عدم إجبار HTTPS
    'httponly' => true, // منع وصول الجافاسكربت إلى الكوكيز
    'samesite' => 'Strict', // منع الكوكيز من الإرسال في الطلبات الخارجية
]);

session_start();

include('config.php');

// التأكد من أن الجلسة موجودة
if (!isset($_SESSION['Email_Session'])) {
    header("Location: login.php");
    exit;
}

// جلب معلومات المستخدم من قاعدة البيانات باستخدام استعلامات مجهزة
$email = $_SESSION['Email_Session'];
$stmt = $conx->prepare("SELECT Username FROM register WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['Username'] ?? "غير معروف"; // في حالة عدم وجود الحقل
} else {
    echo "<script>
            Swal.fire({
                title: 'خطأ!',
                text: 'تعذر العثور على معلومات المستخدم.',
                icon: 'error'
            }).then(() => {
                window.location.href = 'login.php';
            });
          </script>";
    exit;
}

// إنشاء رمز CSRF وتخزينه في الكوكيز
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// حفظ CSRF token في الكوكيز أيضًا
setcookie('csrf_token', $_SESSION['csrf_token'], 0, '/', 'theraf.ct.ws', false, true);

// معالجة طلب تحديث اسم المستخدم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // تحقق من رمز CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'] || $_POST['csrf_token'] !== $_COOKIE['csrf_token']) {
        die("طلب غير صالح!");
    }

    $new_Username = trim($_POST['new_Username']);
    if (empty($new_Username) || strlen($new_Username) > 50) {
        echo "<script>
                Swal.fire({
                    title: 'خطأ!',
                    text: 'الاسم الجديد غير صالح. يجب أن يكون بين 1 و50 حرفًا.',
                    icon: 'error'
                });
              </script>";
        exit;
    }

    // تحديث اسم المستخدم في قاعدة البيانات باستخدام استعلامات مجهزة
    $update_stmt = $conx->prepare("UPDATE register SET Username = ? WHERE email = ?");
    $update_stmt->bind_param("ss", $new_Username, $email);

    if ($update_stmt->execute()) {
        $username = htmlspecialchars($new_Username, ENT_QUOTES, 'UTF-8');
        echo "<script>
                Swal.fire({
                    title: 'تم التحديث!',
                    text: 'تم تحديث اسمك بنجاح!',
                    icon: 'success'
                }).then(() => {
                    window.location.reload();
                });
              </script>";
    } else {
        echo "<script>
                Swal.fire({
                    title: 'خطأ!',
                    text: 'حدث خطأ أثناء تحديث اسمك!',
                    icon: 'error'
                });
              </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحديث اسم المستخدم</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h1>أهلاً بك، <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>!</h1>
    <p>في هذه الصفحة يمكنك تحديث اسم المستخدم الخاص بك.</p>
    <form method="POST">
        <label for="new_Username">اسمك الجديد:</label>
        <input type="text" id="new_Username" name="new_Username" placeholder="أدخل اسمك الجديد" required>
        <!-- تضمين رمز CSRF -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit">تحديث البيانات</button>
    </form>
</body>
</html>