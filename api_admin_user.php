<?php
require_once 'config.php';

header('Content-Type: application/json');

// دالة للتحقق من صحة مفتاح API
function validateApiKey($apiKey) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM api_keys WHERE api_key = ? AND status = 'active'");
    $stmt->bind_param("s", $apiKey);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// دالة لتحديث عدد الاستخدام
function updateUsageCount($apiKey) {
    global $conn;
    $stmt = $conn->prepare("UPDATE api_keys SET usage_count = usage_count + 1, last_used_at = CURRENT_TIMESTAMP WHERE api_key = ?");
    $stmt->bind_param("s", $apiKey);
    $stmt->execute();
}

// دالة لتسجيل الطلب في جدول api_requests
function logApiRequest($apiKeyId) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO api_requests (api_key_id, request_time) VALUES (?, CURRENT_TIMESTAMP)");
    $stmt->bind_param("i", $apiKeyId);
    $stmt->execute();
}

// تحقق من نوع الطلب (GET أو POST)
if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    // استلام مفتاح API من رأس الطلب
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

    // تحقق إذا كان المفتاح غير موجود
    if (empty($apiKey)) {
        http_response_code(401);
        echo json_encode(['error' => 'API key is missing']);
        exit;
    }

    // التحقق من صحة المفتاح
    $keyData = validateApiKey($apiKey);
    
    // إذا كان المفتاح غير صحيح أو غير مفعل
    if (!$keyData) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }

    // تحقق من تجاوز حد الاستخدام
    if ($keyData['usage_count'] >= $keyData['rate_limit']) {
        http_response_code(429);
        echo json_encode(['error' => 'Rate limit exceeded']);
        exit;
    }

    // تحديث عدد الاستخدام
    updateUsageCount($apiKey);

    // تسجيل الطلب في جدول api_requests
    logApiRequest($keyData['id']);

    // هنا يمكنك إضافة المنطق الخاص بك للتعامل مع الطلب (مثل الاستعلامات من قاعدة البيانات)
    echo json_encode(['success' => true, 'message' => 'API request processed successfully']);
} else {
    // إذا كان نوع الطلب غير مدعوم
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>