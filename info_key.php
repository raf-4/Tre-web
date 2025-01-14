<?php
require_once 'config.php';

if (isset($_GET['key_id'])) {
    $keyId = intval($_GET['key_id']);
    
    // Fetch key details
    $stmt = $conn->prepare("SELECT * FROM api_keys WHERE id = ?");
    $stmt->bind_param("i", $keyId);
    $stmt->execute();
    $result = $stmt->get_result();
    $key = $result->fetch_assoc();

    if ($key) {
        // Fetch usage statistics
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');
        $yearStart = date('Y-01-01 00:00:00');
        $yearEnd = date('Y-12-31 23:59:59');

        $stmt = $conn->prepare("SELECT 
            (SELECT COUNT(*) FROM api_requests WHERE api_key_id = ? AND request_time BETWEEN ? AND ?) as today_count,
            (SELECT COUNT(*) FROM api_requests WHERE api_key_id = ? AND request_time BETWEEN ? AND ?) as month_count,
            (SELECT COUNT(*) FROM api_requests WHERE api_key_id = ? AND request_time BETWEEN ? AND ?) as year_count");
        
        $stmt->bind_param("issississ", $keyId, $todayStart, $todayEnd, $keyId, $monthStart, $monthEnd, $keyId, $yearStart, $yearEnd);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();

        // Fetch daily usage for the past month
        $stmt = $conn->prepare("SELECT DATE(request_time) as date, COUNT(*) as count 
                                FROM api_requests 
                                WHERE api_key_id = ? AND request_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                GROUP BY DATE(request_time)
                                ORDER BY date");
        $stmt->bind_param("i", $keyId);
        $stmt->execute();
        $result = $stmt->get_result();
        $dailyUsage = $result->fetch_all(MYSQLI_ASSOC);

        // Prepare data for chart
        $labels = [];
        $data = [];
        foreach ($dailyUsage as $day) {
            $labels[] = $day['date'];
            $data[] = $day['count'];
        }
    } else {
        die("المفتاح غير موجود");
    }
} else {
    die("لم يتم تحديد المفتاح");
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المفتاح</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-background-color custom-scrollbar">
    <div class="container mx-auto p-6 animate-fadeIn">
        <h1 class="text-4xl font-bold mb-6 gradient-text text-center">تفاصيل المفتاح</h1>

        <div class="card mb-8 animate-slideInUp">
            <div class="card-header">
                <h2 class="text-2xl font-semibold">معلومات المفتاح</h2>
            </div>
            <div class="card-body">
                <p><strong>الاسم:</strong> <?php echo htmlspecialchars($key['name']); ?></p>
                <p><strong>المفتاح:</strong> <?php echo htmlspecialchars($key['api_key']); ?></p>
                <p><strong>حد الاستخدام:</strong> <?php echo htmlspecialchars($key['rate_limit']); ?></p>
                <p><strong>الحالة:</strong> <?php echo htmlspecialchars($key['status']); ?></p>
                <p><strong>تاريخ الإنشاء:</strong> <?php echo htmlspecialchars($key['created_at']); ?></p>
                <p><strong>آخر استخدام:</strong> <?php echo htmlspecialchars($key['last_used_at']); ?></p>
            </div>
        </div>

        <div class="card mb-8 animate-slideInUp">
            <div class="card-header">
                <h2 class="text-2xl font-semibold">إحصائيات الاستخدام</h2>
            </div>
            <div class="card-body">
                <p><strong>الاستخدام اليوم:</strong> <?php echo $stats['today_count']; ?></p>
                <p><strong>الاستخدام هذا الشهر:</strong> <?php echo $stats['month_count']; ?></p>
                <p><strong>الاستخدام هذا العام:</strong> <?php echo $stats['year_count']; ?></p>
            </div>
        </div>

        <div class="card animate-slideInUp">
            <div class="card-header">
                <h2 class="text-2xl font-semibold">الاستخدام اليومي (آخر 30 يوم)</h2>
            </div>
            <div class="card-body">
                <canvas id="usageChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <script>
        var ctx = document.getElementById('usageChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'الاستخدام اليومي',
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'الاستخدام اليومي للمفتاح'
                    }
                }
            }
        });
    </script>
    <script src="script.js"></script>
</body>
</html>