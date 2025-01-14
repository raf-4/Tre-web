<?php
require_once 'config.php';

function generateApiKey() {
    // الجزء الأول (أرقام عشوائية)
    $numbers = substr(bin2hex(random_bytes(3)), 0, 6);
    
    // الجزء الثاني (حروف عشوائية)
    $letters = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"), 0, 2);

    // فلترة المدخلات إذا كانت هناك حاجة لفلترة
    $numbers = htmlspecialchars($numbers, ENT_QUOTES, 'UTF-8');
    $letters = htmlspecialchars($letters, ENT_QUOTES, 'UTF-8');

    // الجمع بين الأجزاء مع التنسيق المطلوب
    return $numbers . '-' . $numbers . '-' . $letters;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                if (!empty($_POST['name']) && !empty($_POST['rate_limit'])) {
                    $userId = 1; // استبدل بمعرف المستخدم الفعلي
                    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    $rateLimit = filter_input(INPUT_POST, 'rate_limit', FILTER_VALIDATE_INT);
                    $permissions = isset($_POST['permissions']) ? json_encode($_POST['permissions']) : json_encode([]);
                    $apiKey = generateApiKey();

                    $stmt = $conn->prepare("INSERT INTO api_keys (user_id, api_key, name, rate_limit, permissions) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("issis", $userId, $apiKey, $name, $rateLimit, $permissions);

                    if ($stmt->execute()) {
                        echo "<script>toastr.success('تم إنشاء المفتاح بنجاح');</script>";
                    } else {
                        echo "<script>toastr.error('حدث خطأ أثناء إنشاء المفتاح');</script>";
                    }
                } else {
                    echo "<script>toastr.error('الرجاء ملء جميع الحقول المطلوبة');</script>";
                }
                break;
            case 'update':
                if (!empty($_POST['key_id']) && !empty($_POST['name']) && !empty($_POST['rate_limit'])) {
                    $keyId = intval($_POST['key_id']);
                    $name = $_POST['name'];
                    $rateLimit = $_POST['rate_limit'];
                    $permissions = json_encode($_POST['permissions']);

                    $stmt = $conn->prepare("UPDATE api_keys SET name = ?, rate_limit = ?, permissions = ? WHERE id = ?");
                    $stmt->bind_param("sisi", $name, $rateLimit, $permissions, $keyId);

                    if ($stmt->execute()) {
                        echo "<script>toastr.success('تم تعديل المفتاح بنجاح');</script>";
                    } else {
                        echo "<script>toastr.error('حدث خطأ أثناء تعديل المفتاح');</script>";
                    }
                }
                break;

            case 'delete':
                if (!empty($_POST['key_id'])) {
                    $keyId = intval($_POST['key_id']);
                    $stmt = $conn->prepare("DELETE FROM api_keys WHERE id = ?");
                    $stmt->bind_param("i", $keyId);

                    if ($stmt->execute()) {
                        echo "<script>delete_key();</script>";
                    } else {
                        echo "<script>toastr.error('حدث خطأ أثناء حذف المفتاح');</script>";
                    }
                } else {
                    echo "<script>toastr.error('لم يتم تحديد المفتاح للحذف');</script>";
                }
                break;
        }
    }
}

$result = $conn->query("SELECT * FROM api_keys");
$keys = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم API</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-background-color custom-scrollbar">
    <div class="container mx-auto p-6 animate-fadeIn">
        <h1 class="text-4xl font-bold mb-6 gradient-text text-center">لوحة تحكم API</h1>

        <!-- نموذج إنشاء مفتاح جديد -->
        <div class="card mb-8 animate-slideInUp">
            <div class="card-header">
                <h2 class="text-2xl font-semibold">إنشاء مفتاح جديد</h2>
            </div>
            <div class="card-body">
                <form action="" method="POST" class="form-style">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="name" class="form-label">اسم المفتاح</label>
                        <input id="name" name="name" type="text" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="rate_limit" class="form-label">حد الاستخدام</label>
                        <input id="rate_limit" name="rate_limit" type="number" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">الصلاحيات</label>
                        <div class="flex space-x-4">
                            <label class="fancy-checkbox">
                                <input type="checkbox" name="permissions[]" value="read">
                                <span>قراءة</span>
                            </label>
                            <label class="fancy-checkbox">
                                <input type="checkbox" name="permissions[]" value="write">
                                <span>كتابة</span>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-3d mt-4">إنشاء مفتاح</button>
                </form>
            </div>
        </div>

        <!-- جدول المفاتيح -->
        <div class="card mb-8 animate-slideInUp">
            <div class="card-header">
                <h2 class="text-2xl font-semibold">المفاتيح الحالية</h2>
            </div>
            <div class="card-body overflow-x-auto">
                <table class="table table-hover w-full">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>المفتاح</th>
                            <th>حد الاستخدام</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($keys as $key): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($key['name']); ?></td>
                            <td>
                                <div class="tooltip">
                                    <?php echo substr(htmlspecialchars($key['api_key']), 0, 20) . '...'; ?>
                                    <span class="tooltiptext"><?php echo htmlspecialchars($key['api_key']); ?></span>
                                </div>
                                <button onclick="copyToClipboard('<?php echo htmlspecialchars($key['api_key']); ?>')" class="btn btn-secondary btn-sm ml-2">نسخ</button>
                            </td>
                            <td><?php echo htmlspecialchars($key['rate_limit']); ?></td>
                            <td>
                                <div class="btn-group">
                                    <form action="" method="POST" class="inline">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="key_id" value="<?php echo $key['id']; ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">تعديل</button>
                                    </form>
                                    <form action="" method="POST" class="inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="key_id" value="<?php echo $key['id']; ?>">
                                        <button type="submit" onclick="return confirmDelete()" class="btn btn-danger btn-sm">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- الرسم البياني -->
        <div class="card animate-slideInUp">
            <div class="card-header">
                <h2 class="text-2xl font-semibold">إحصائيات الاستخدام</h2>
            </div>
            <div class="card-body">
                <canvas id="usageChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                Swal.fire({
                    title: 'تم النسخ!',
                    text: 'تم نسخ المفتاح إلى الحافظة',
                    icon: 'success',
                    confirmButtonText: 'حسناً'
                });
            }, function(err) {
                console.error('فشل في النسخ: ', err);
            });
        }

        function confirmDelete() {
            return Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تتمكن من التراجع عن هذا!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'نعم، احذفه!',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                return result.isConfirmed;
            });
        }

        // إعداد الرسم البياني
        var ctx = document.getElementById('usageChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{
                    label: 'استخدام API',
                    data: [12, 19, 3, 5, 2, 3],
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
                        text: 'إحصائيات استخدام API الشهرية'
                    }
                }
            }
        });
    </script>
</body>
</html>