
<div class="d-flex justify-content-between align-items-center mb-5">
    <h2>Trang Chủ</h2>
</div>

<div class="text-center mt-5 pt-5" style="position: relative; z-index: 1;">
    <h1 class="display-3 fw-bold text-danger mb-4">
    🚀 CI/CD TEST THÀNH CÔNG 🚀
	</h1>
    <p class="lead text-muted fs-4">
        Hệ thống Quản lý Kho Vật liệu xây dựng (Microservices Architecture)
    </p>
    <div id="roleInfo" class="mt-4 p-3 bg-light rounded d-inline-block border">
        <i class="fas fa-user-shield me-2 text-primary"></i>
        Đang tải thông tin quyền hạn...
    </div>
    
    <hr class="w-50 mx-auto my-5 border-primary opacity-50">
    <p class="text-secondary fs-5">
        Hãy chọn chức năng từ menu bên trái để bắt đầu quản lý.
    </p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- 1. XỬ LÝ QUYỀN HẠN CỦA BẠN (GIỮ NGUYÊN) ---
        const token = localStorage.getItem('token');
        if (token) {
            try {
                const payload = JSON.parse(atob(token));
                const mapping = {
                    'admin': 'Quản trị viên - Toàn quyền hệ thống',
                    'staff': 'Nhân viên - Quản lý nghiệp vụ',
                };
                const role = payload.Vaitro || 'guest';
                document.getElementById('roleInfo').innerHTML = `
                    <i class="fas fa-user-shield me-2 text-primary"></i>
                    Quyền hạn: <strong>${mapping[role] || role}</strong>
                `;
            } catch(e) {
                document.getElementById('roleInfo').innerHTML = 'Chào mừng bạn đến với hệ thống!';
            }
        }
    });
</script>
