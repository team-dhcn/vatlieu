<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/role_helper.php';

/* 
// Authentication check (Removed because we use JWT in LocalStorage)
if (!isLoggedIn()) {
    header("Location: dangnhap.php");
    exit;
}
*/

if (php_sapi_name() === 'cli-server') {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($requestUri !== '/' && is_file(__DIR__ . $requestUri)) {
        return false; // Serve the requested file instead of routing
    }
}

// Get the requested page
$page = 'trangchu';
if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = trim($requestUri, '/');
    if (!empty($path)) {
        $page = explode('/', $path)[0];
    } else {
        // Root URL "/" → redirect to login
        header('Location: /dangnhap');
        exit;
    }
}

// Mapping URL paths to view files
$views = [
    'trangchu' => ['file' => 'trangchu.php', 'title' => 'Trang chủ - VLXD'],
    'sanpham' => ['file' => 'sanpham.php', 'title' => 'Quản lý Sản phẩm - VLXD'],
    'dmsp' => ['file' => 'dmsp.php', 'title' => 'Danh mục Sản phẩm - VLXD'],
    'nguyenvatlieu' => ['file' => 'nguyenvatlieu.php', 'title' => 'Nguyên vật liệu - VLXD'],
    'nhacungcap' => ['file' => 'nhacungcap.php', 'title' => 'Nhà cung cấp - VLXD'],
    'congthuc' => ['file' => 'congthucsanpham.php', 'title' => 'Công thức Sản phẩm - VLXD'],
    'khachhang' => ['file' => 'khachhang.php', 'title' => 'Khách hàng - VLXD'],
    'loaikhachhang' => ['file' => 'loaikhachhang.php', 'title' => 'Loại Khách hàng - VLXD'],
    'tonkho-nvl' => ['file' => 'tonkho_nvl.php', 'title' => 'Tồn kho Nguyên vật liệu - VLXD'],
    'tonkho-sp' => ['file' => 'tonkho_sp.php', 'title' => 'Tồn kho Thành phẩm - VLXD'],
    'phieu-nhap-danh-sach' => ['file' => 'danh_sach_phieu_nhap.php', 'title' => 'Danh sách Phiếu nhập - VLXD'],
    'phieu-nhap-tao' => ['file' => 'phieu_nhap.php', 'title' => 'Tạo Phiếu nhập - VLXD'],
    'phieu-nhap-chi-tiet' => ['file' => 'chi_tiet_phieu_nhap.php', 'title' => 'Chi tiết Phiếu nhập - VLXD'],
    'lenh-san-xuat-danh-sach' => ['file' => 'lenh_san_xuat_danh_sach.php', 'title' => 'Danh sách Lệnh sản xuất - VLXD'],
    'lenh-san-xuat-tao' => ['file' => 'lenh_san_xuat_tao.php', 'title' => 'Tạo Lệnh sản xuất - VLXD'],
    'lenh-san-xuat-sua' => ['file' => 'lenh_san_xuat_sua.php', 'title' => 'Sửa Lệnh sản xuất - VLXD'],
    'lenh-san-xuat-chi-tiet' => ['file' => 'lenh_san_xuat_chi_tiet.php', 'title' => 'Chi tiết Lệnh sản xuất - VLXD'],
    'phieu-dieuchuyen-danh-sach' => ['file' => 'danh_sach_phieu_dieuchuyen.php', 'title' => 'Danh sách Phiếu điều chuyển - VLXD'],
    'phieu-dieuchuyen-tao' => ['file' => 'phieu_dieuchuyen.php', 'title' => 'Tạo Phiếu điều chuyển - VLXD'],
    'phieu-dieuchuyen-thuc-hien' => ['file' => 'thuc_hien_dieuchuyen.php', 'title' => 'Thực hiện điều chuyển - VLXD'],
    'phieu-dieuchuyen-chi-tiet' => ['file' => 'chi_tiet_dieuchuyen.php', 'title' => 'Chi tiết phiếu điều chuyển - VLXD'],
    'phieu-xuat-danh-sach' => ['file' => 'phieu_xuat_danh_sach.php', 'title' => 'Danh sách Phiếu xuất - VLXD'],
    'phieu-xuat-tao' => ['file' => 'phieu_xuat_tao.php', 'title' => 'Tạo Phiếu xuất - VLXD'],
    'phieu-xuat-chi-tiet' => ['file' => 'phieu_xuat_chi_tiet.php', 'title' => 'Chi tiết Phiếu xuất - VLXD'],
    'phieu-xuat-sua' => ['file' => 'phieu_xuat_sua.php', 'title' => 'Sửa Phiếu xuất - VLXD'],
];

if ($page === 'dangnhap') {
    require_once __DIR__ . '/dangnhap.php';
    exit;
}

// Check if page exists
if (!isset($views[$page])) {
    $page = 'trangchu'; // Default fallback
}

$viewConfig = $views[$page];
$title = $viewConfig['title'];

// Header
require_once __DIR__ . '/views/layout/header.php';

// Sidebar (Includes opening of main-content)
require_once __DIR__ . '/views/layout/sidebar.php';

// View Page
$viewPath = __DIR__ . '/views/pages/' . $viewConfig['file'];
if (file_exists($viewPath)) {
    require_once $viewPath;
} else {
    echo "<div class='alert alert-warning'>Trang đang được phát triển...</div>";
}

// Footer (Includes closing of main-content)
require_once __DIR__ . '/views/layout/footer.php';

