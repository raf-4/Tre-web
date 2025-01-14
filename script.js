document.addEventListener('DOMContentLoaded', function() {
    const createKeyForm = document.getElementById('createKeyForm');
    const toggleThemeBtn = document.getElementById('toggleTheme');
    
    createKeyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: 'نجاح!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'حسناً'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'خطأ!',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'حسناً'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

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

    window.copyToClipboard = copyToClipboard;

    function deleteKey(keyId) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "لن تتمكن من التراجع عن هذا!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'نعم، احذفه!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('key_id', keyId);

                fetch('index.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire(
                            'تم الحذف!',
                            data.message,
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'خطأ!',
                            data.message,
                            'error'
                        );
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
    }

    window.deleteKey = deleteKey;

    function editKey(keyId) {
        // Fetch key details and show edit form
        // This is a placeholder for the edit functionality
        console.log('Edit key:', keyId);
    }

    window.editKey = editKey;

    // Theme toggling
    function toggleTheme() {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    }

    toggleThemeBtn.addEventListener('click', toggleTheme);

    // Check for saved theme preference
    const darkMode = localStorage.getItem('darkMode');
    if (darkMode === 'true') {
        document.body.classList.add('dark-mode');
    }

    // Chart initialization
    const ctx = document.getElementById('usageChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
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
});