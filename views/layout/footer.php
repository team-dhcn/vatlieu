</div> <!-- closing main-content -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {

        function toggleMenu(btnId, subId) {
            const btn = document.getElementById(btnId);
            const sub = document.getElementById(subId);

            if (btn && sub) {
                btn.addEventListener("click", function () {
                    sub.classList.toggle("d-none");
                });
            }
        }

        // GỌI CHO TẤT CẢ MENU
        toggleMenu("btnSanPham", "submenuSanPham");
        toggleMenu("btnPhieuNhap", "submenuPhieuNhap");
        toggleMenu("btnPhieuXuat", "submenuPhieuXuat");
        toggleMenu("btnPhieudc", "submenuPhieudc");
        toggleMenu("btnSanXuat", "submenuSanXuat");
        toggleMenu("btnBaoCao", "submenuBaoCao");
        toggleMenu("btnKhachHang", "submenuKhachHang");

    });
    // System-wide helpers 
    const getHeaders = () => {
        const token = localStorage.getItem('token');
        if (!token) return null;
        return { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' };
    };

    // API_BASE, API, apiUrl() và fetch interceptor đã được định nghĩa trong header.php



    // Synchronize UI with User Token
    document.addEventListener('DOMContentLoaded', () => {
        const userStr = localStorage.getItem('user');
        const token = localStorage.getItem('token');

        if (!token) {
            window.location.href = '/dangnhap';
            return;
        }

        if (userStr) {
            try {
                const user = JSON.parse(userStr);
                const role = (user.Vaitro || 'guest').toLowerCase();

                // Update Name & Role
                document.getElementById('user-fullname').textContent = user.Fullname || user.username || user.Tendangnhap;
                document.getElementById('user-role').innerHTML = `<i class="fas fa-user-shield me-1"></i> ${user.Vaitro || 'Guest'}`;

                // Show/Hide warehouse menu based on role
                if (role === 'staff') {
                    document.getElementById('menu-warehouse').style.display = 'none';
                } if (role === 'kho') {
                    document.getElementById('menu-sanxuat').style.display = 'none';
                }
            } catch (e) { console.error('Error parsing user data'); }
        }
    });

    // Handle Logout
    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout) {
        btnLogout.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Bạn có muốn đăng xuất?')) {
                localStorage.clear();
                window.location.href = '/dangnhap';
            }
        });
    }

    function showAlert(msg, type = 'success') {
        const div = document.createElement('div');
        div.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow-sm`;
        div.style.zIndex = '9999';
        div.innerHTML = `
            ${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 4000);
    }
</script>

</body>

</html>
