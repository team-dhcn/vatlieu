<?php
// logout.php
session_start();
session_destroy(); // Xóa sạch session trên server
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
</head>

<body>
    <script>
        // Xóa dữ liệu ở trình duyệt
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        // Chuyển hướng về đúng file dangnhap.php
        window.location.href = 'dangnhap.php';
    </script>
</body>

</html>