<?php
$currentPage = $page ?? '';
$user = getCurrentUser();
$roleName = getRoleName(getCurrentRole());
?>
<nav class="sidebar shadow">
    <div class="text-center mb-4 px-3">
        <h4 class="mb-0 text-white fw-bold"><i class="fas fa-warehouse text-warning me-2"></i> KHO VLXD</h4>
        <div class="mt-2 py-2 px-3 bg-white bg-opacity-10 rounded-pill shadow-sm border border-light border-opacity-10">
            <div class="fw-bold small text-truncate" id="user-fullname">Đang tải...</div>
            <div id="user-role" class="opacity-75" style="font-size: 0.7rem;"><i class="fas fa-user-shield me-1"></i>
                <?= $roleName ?></div>
        </div>
    </div>

    <ul class="nav flex-column pb-5">
        <li class="nav-item">
            <a class="nav-link rounded-pill mx-2 mb-1 <?php echo $currentPage === 'trangchu' ? 'active' : ''; ?>"
                href="trangchu">
                <i class="fas fa-chart-line me-2"></i> Trang Chủ
            </a>
        </li>

        <li class="nav-item mt-3 px-3 small text-uppercase opacity-50 fw-bold"
            style="font-size: 0.65rem; letter-spacing: 1px;">Danh mục & Đối tác</li>
        <li class="nav-item">
            <a class="nav-link rounded-pill mx-2 mb-1 <?php echo in_array($currentPage, ['sanpham', 'dmsp', 'nguyenvatlieu', 'nhacungcap']) ? 'active' : ''; ?>"
                href="#" data-bs-toggle="collapse" data-bs-target="#menuSP">
                <i class="fas fa-box me-2"></i> Hàng hóa <i class="fas fa-chevron-down float-end mt-1 small"></i>
            </a>
            <div class="collapse <?php echo in_array($currentPage, ['sanpham', 'dmsp', 'nguyenvatlieu', 'nhacungcap']) ? 'show' : ''; ?>"
                id="menuSP" style="margin-left: 20px;">
                <a class="nav-link small py-1" href="sanpham"><i class="fas fa-cube me-2 opacity-50"></i> Sản phẩm</a>
                <a class="nav-link small py-1" href="dmsp"><i class="fas fa-tags me-2 opacity-50"></i> Danh mục SP</a>
                <a class="nav-link small py-1" href="nguyenvatlieu"><i class="fas fa-layer-group me-2 opacity-50"></i>
                    Nguyên vật liệu</a>
                <a class="nav-link small py-1" href="nhacungcap"><i class="fas fa-truck me-2 opacity-50"></i> Nhà cung
                    cấp</a>
            </div>
        </li>

        <li class="nav-item mt-3 px-3 small text-uppercase opacity-50 fw-bold"
            style="font-size: 0.65rem; letter-spacing: 1px;">Hoạt động kho</li>
        <li id="menu-warehouse" style="display: block;">
            <a class="nav-link rounded-pill mx-2 mb-1 <?php echo strpos($currentPage, 'phieu-nhap') !== false ? 'active' : ''; ?>"
                href="#" data-bs-toggle="collapse" data-bs-target="#menuNhap">
                <i class="fas fa-file-import me-2"></i> Nhập kho <i
                    class="fas fa-chevron-down float-end mt-1 small"></i>
            </a>
            <div class="collapse <?php echo strpos($currentPage, 'phieu-nhap') !== false ? 'show' : ''; ?>"
                id="menuNhap" style="margin-left: 20px;">
                <a class="nav-link small py-1" href="phieu-nhap-danh-sach"><i class="fas fa-list me-2 opacity-50"></i>
                    Danh sách phiếu</a>
                <a class="nav-link small py-1" href="phieu-nhap-tao"><i class="fas fa-plus-circle me-2 opacity-50"></i>
                    Tạo phiếu nhập</a>
            </div>

            <a class="nav-link rounded-pill mx-2 mb-1 <?php echo strpos($currentPage, 'phieu-xuat') !== false ? 'active' : ''; ?>"
                href="#" data-bs-toggle="collapse" data-bs-target="#menuXuat">
                <i class="fas fa-file-export me-2"></i> Xuất kho <i
                    class="fas fa-chevron-down float-end mt-1 small"></i>
            </a>
            <div class="collapse <?php echo strpos($currentPage, 'phieu-xuat') !== false ? 'show' : ''; ?>"
                id="menuXuat" style="margin-left: 20px;">
                <a class="nav-link small py-1" href="phieu-xuat-danh-sach"><i class="fas fa-list me-2 opacity-50"></i>
                    Danh sách phiếu</a>
                <a class="nav-link small py-1" href="phieu-xuat-tao"><i class="fas fa-plus-circle me-2 opacity-50"></i>
                    Tạo phiếu xuất</a>
            </div>

            <a class="nav-link rounded-pill mx-2 mb-1 <?php echo strpos($currentPage, 'phieu-dieuchuyen') !== false ? 'active' : ''; ?>"
                href="#" data-bs-toggle="collapse" data-bs-target="#menuDieuChuyen">
                <i class="fas fa-exchange-alt me-2"></i> Điều chuyển kho <i
                    class="fas fa-chevron-down float-end mt-1 small"></i>
            </a>
            <div class="collapse <?php echo strpos($currentPage, 'phieu-dieuchuyen') !== false ? 'show' : ''; ?>"
                id="menuDieuChuyen" style="margin-left: 20px;">
                <a class="nav-link small py-1" href="phieu-dieuchuyen-danh-sach"><i
                        class="fas fa-list me-2 opacity-50"></i> Danh sách phiếu</a>
                <a class="nav-link small py-1" href="phieu-dieuchuyen-tao"><i
                        class="fas fa-plus-circle me-2 opacity-50"></i> Tạo phiếu điều chuyển</a>
            </div>
        </li>

        <li class="nav-item mt-3 px-3 small text-uppercase opacity-50 fw-bold"
            style="font-size: 0.65rem; letter-spacing: 1px;">Sản xuất</li>
        <li id="menu-sanxuat" style="display: block;">
            <a class="nav-link rounded-pill mx-2 mb-1 <?php echo strpos($currentPage, 'lenh-san-xuat') !== false ? 'active' : ''; ?>"
                href="#" data-bs-toggle="collapse" data-bs-target="#menuSanXuat">
                <i class="fas fa-cogs me-2"></i> Sản xuất <i class="fas fa-chevron-down float-end mt-1 small"></i>
            </a>
            <div class="collapse <?php echo strpos($currentPage, 'lenh-san-xuat') !== false ? 'show' : ''; ?>"
                id="menuSanXuat" style="margin-left: 20px;">
                <a class="nav-link small py-1" href="lenh-san-xuat-danh-sach"><i
                        class="fas fa-list me-2 opacity-50"></i> Danh sách lệnh</a>
                <a class="nav-link small py-1" href="lenh-san-xuat-tao"><i
                        class="fas fa-plus-circle me-2 opacity-50"></i> Tạo lệnh mới</a>
                <a class="nav-link small py-1" href="congthuc"><i class="fas fa-flask me-2 opacity-50"></i> Công thức
                    SP</a>
            </div>
        </li>

        <li class="nav-item mt-3 px-3 small text-uppercase opacity-50 fw-bold"
            style="font-size: 0.65rem; letter-spacing: 1px;">Báo cáo & Thống kê</li>
        <li class="nav-item">
            <a class="nav-link rounded-pill mx-2 mb-1 <?php echo strpos($currentPage, 'tonkho') !== false ? 'active' : ''; ?>"
                href="#" data-bs-toggle="collapse" data-bs-target="#menuBaoCao">
                <i class="fas fa-chart-pie me-2"></i> Tồn kho <i class="fas fa-chevron-down float-end mt-1 small"></i>
            </a>
            <div class="collapse <?php echo strpos($currentPage, 'tonkho') !== false ? 'show' : ''; ?>" id="menuBaoCao"
                style="margin-left: 20px;">
                <a class="nav-link small py-1" href="tonkho-nvl"><i class="fas fa-boxes-stacked me-2 opacity-50"></i>
                    Tồn kho NVL</a>
                <a class="nav-link small py-1" href="tonkho-sp"><i class="fas fa-cubes me-2 opacity-50"></i> Tồn kho Sản
                    phẩm</a>
                <a class="nav-link small py-1" href="tonkho.php"><i class="fas fa-chart-bar me-2 opacity-50"></i> Báo
                    cáo tổng hợp</a>
            </div>
        </li>

        <li class="nav-item mt-3 px-3 small text-uppercase opacity-50 fw-bold"
            style="font-size: 0.65rem; letter-spacing: 1px;">Đối tác</li>
        <li class="nav-item">
            <a class="nav-link rounded-pill mx-2 mb-1 <?php echo in_array($currentPage, ['khachhang', 'loaikhachhang']) ? 'active' : ''; ?>"
                href="#" data-bs-toggle="collapse" data-bs-target="#menuKH">
                <i class="fas fa-users me-2"></i> Khách hàng <i class="fas fa-chevron-down float-end mt-1 small"></i>
            </a>
            <div class="collapse <?php echo in_array($currentPage, ['khachhang', 'loaikhachhang']) ? 'show' : ''; ?>"
                id="menuKH" style="margin-left: 20px;">
                <a class="nav-link small py-1" href="khachhang"><i class="fas fa-user-friends me-2 opacity-50"></i> Danh
                    sách KH</a>
                <a class="nav-link small py-1" href="loaikhachhang"><i class="fas fa-id-card me-2 opacity-50"></i> Loại
                    khách hàng</a>
            </div>
        </li>

        <li class="nav-item mt-auto">
            <hr class="mx-3 bg-white opacity-10">
        </li>
        <a class="nav-link text-danger fw-bold rounded-pill" href="logout.php">
            <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
        </a>
    </ul>
</nav>
<div class="main-content">
    <style>
        .sidebar .nav-link {
            font-weight: 500;
            font-size: 0.82rem;
        }

        .sidebar .nav-link:not(.active):hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar .nav-link.active {
            background: #3b82f6 !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .nav-item .collapse .nav-link {
            font-size: 0.78rem;
            opacity: 0.85;
        }

        .nav-item .collapse .nav-link:hover {
            opacity: 1;
            text-decoration: underline;
            background: none !important;
        }
    </style>