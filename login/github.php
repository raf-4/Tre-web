<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('config.php');

// إعدادات GitHub OAuth
$client_id = 'Ov23li53M8QEsd2FhAsf';
$client_secret = '29e1124cc05e9dc6875c936e8f7aa37ca124f808';
$redirect_uri = 'http://theraf.ct.ws/4/github.php';

if (isset($_GET['code'])) {
    // التحقق من رمز التفويض
    $code = $_GET['code'];
    $url = 'https://github.com/login/oauth/access_token';
    $data = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'redirect_uri' => $redirect_uri
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER => ['Accept: application/json']
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if (!isset($response_data['access_token'])) {
        die('Authentication failed. Please try again.');
    }

    $access_token = $response_data['access_token'];

    // الحصول على بيانات المستخدم
    $ch = curl_init('https://api.github.com/user');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER => [
            'Authorization: token ' . $access_token,
            'User-Agent: MyApp'
        ]
    ]);
    $user_info = curl_exec($ch);
    curl_close($ch);

    $user_data = json_decode($user_info, true);
    if (!isset($user_data['login'])) {
        die('Failed to fetch user data.');
    }

    $username = $user_data['login'];
    $email = $user_data['email'] ?? 'No email provided';

    // تحقق إذا كان المستخدم موجودًا
    $query = "SELECT * FROM github_users WHERE username='$username'";
    $result = mysqli_query($conx, $query);
    if (mysqli_num_rows($result) === 0) {
        $insert_query = "INSERT INTO github_users (username, email, access_token) VALUES ('$username', '$email', '$access_token')";
        mysqli_query($conx, $insert_query);
    }

    $_SESSION['github_user'] = $user_data;
    header('Location: welcome.php'); // إعادة توجيه آمنة
    exit();
} else {
    // إعادة توجيه المستخدم إلى GitHub
    $auth_url = 'https://github.com/login/oauth/authorize?client_id=' . $client_id . '&redirect_uri=' . urlencode($redirect_uri);
    header("Location: $auth_url");
    exit();
}
?>