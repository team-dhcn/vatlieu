<div class="d-flex justify-content-between align-items-center mb-5">
    <h2>Trang Chủ</h2>
</div>

<canvas id="snowCanvas" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 9999;"></canvas>

<div class="text-center mt-5 pt-5" style="position: relative; z-index: 1;">
    <h1 class="display-3 fw-bold text-primary mb-4">
        <i class="fas fa-warehouse me-3 text-warning"></i>
        Chào mừng bạn quay trở lại!
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

        // --- 2. HIỆU ỨNG BÔNG TUYẾT TO + CLICK LÀ BUNG NỔ ---
        const canvas = document.getElementById('snowCanvas');
        const ctx = canvas.getContext('2d');

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        const snowflakeChars = ['❄', '❅', '❆'];
        const flakes = [];
        const particles = []; // Chứa các mảnh vụn khi tuyết nổ
        const numFlakes = 40; // Giảm số lượng vì bông tuyết to, nhiều quá sẽ rối

        // Khởi tạo các bông tuyết lớn ban đầu
        for (let i = 0; i < numFlakes; i++) {
            flakes.push(createSnowflake(false));
        }

        function createSnowflake(onTop = true) {
            return {
                x: Math.random() * canvas.width,
                y: onTop ? -30 : Math.random() * canvas.height,
                size: Math.random() * 15 + 30, // Kích thước to từ 15px đến 30px
                speedY: Math.random() * 0.11 + 0.44,
                speedX: Math.random() * 1 - 0.5,
                char: snowflakeChars[Math.floor(Math.random() * snowflakeChars.length)],
                opacity: Math.random() * 0.4 + 0.2, // Độ mờ vừa phải để không che khuất chữ
                spin: Math.random() * 0.02 - 0.01,
                angle: Math.random() * Math.PI
            };
        }

        // Hàm tạo vụn nổ khi click trúng
        function explode(x, y) {
            for (let i = 0; i < 15; i++) {
                particles.push({
                    x: x,
                    y: y,
                    radius: Math.random() * 3 + 1,
                    alpha: 1,
                    speedX: Math.random() * 6 - 3,
                    speedY: Math.random() * 6 - 3
                });
            }
        }

        function drawAndMove() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Vẽ bông tuyết lớn
            flakes.forEach((f, index) => {
                ctx.save();
                ctx.translate(f.x, f.y);
                ctx.rotate(f.angle);
                // Màu xanh nhạt đồng bộ với Bootstrap text-primary
                ctx.fillStyle = `rgba(255, 255, 255, ${f.opacity})`;
                ctx.font = `${f.size}px Arial`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(f.char, 0, 0);
                ctx.restore();

                // Di chuyển
                f.y += f.speedY;
                f.x += f.speedX;
                f.angle += f.spin;

                // Reset nếu rơi hết màn hình
                if (f.y > canvas.height + 30) {
                    flakes[index] = createSnowflake(true);
                }
            });

            // Vẽ mảnh vụn nổ
            particles.forEach((p, index) => {
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(13, 110, 253, ${p.alpha})`;
                ctx.fill();

                p.x += p.speedX;
                p.y += p.speedY;
                p.alpha -= 0.02; // Mờ dần rồi biến mất

                if (p.alpha <= 0) {
                    particles.splice(index, 1);
                }
            });

            requestAnimationFrame(drawAndMove);
        }

        drawAndMove();

        // Xử lý sự kiện click chuột/vùng chạm
        canvas.addEventListener('click', (e) => {
            const rect = canvas.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            let hitSomething = false;

            // Kiểm tra xem có click trúng bông tuyết nào không
            for (let i = flakes.length - 1; i >= 0; i--) {
                const f = flakes[i];
                // Tính khoảng cách từ chuột tới tâm bông tuyết
                const dist = Math.hypot(mouseX - f.x, mouseY - f.y);

                // Nếu khoảng cách nhỏ hơn bán kính kích thước bông tuyết -> Trúng!
                if (dist < f.size) {
                    explode(f.x, f.y);      // Tạo hiệu ứng nổ vụn
                    flakes.splice(i, 1);    // Xóa bông tuyết cũ
                    flakes.push(createSnowflake(true)); // Bù lại bông tuyết mới trên đỉnh
                    hitSomething = true;
                    break; // Chỉ nổ 1 bông mỗi lần click
                }
            }

            // MẸO UX: Nếu KHÔNG click trúng bông tuyết nào, cho phép sự kiện click xuyên qua canvas xuống các thẻ HTML bên dưới (Nút, Menu)
            if (!hitSomething) {
                canvas.style.pointerEvents = 'none'; // Tạm thời tắt tương tác canvas
                const lowerElement = document.elementFromPoint(e.clientX, e.clientY);
                if (lowerElement) lowerElement.click(); // Kích hoạt click cho phần tử bên dưới
                canvas.style.pointerEvents = 'auto';  // Bật lại tương tác canvas ngay lập tức
            }
        });
    });
</script>
