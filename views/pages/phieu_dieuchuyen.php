<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0 text-gray-800">Tạo phiếu điều chuyển kho</h2>
            <p class="text-muted small mt-1">Điều chuyển hàng hóa giữa các kho</p>
        </div>
        <div>
            <a href="/vlxd/phieu-dieuchuyen-danh-sach" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Danh sách phiếu điều chuyển
            </a>
        </div>
    </div>

    <div id="alertMsg" class="alert d-none mb-4" role="alert"></div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form id="formDieuChuyen" onsubmit="event.preventDefault(); submitTransfer();">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Mã điều chuyển *</label>
                        <input type="text" id="madieuchuyen" name="madieuchuyen" required class="form-control" placeholder="Tự động nếu để trống">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Kho xuất *</label>
                        <select id="khoxuat" name="khoxuat" required class="form-select">
                            <option value="">-- Chọn kho xuất --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Kho nhập *</label>
                        <select id="khonhap" name="khonhap" required class="form-select">
                            <option value="">-- Chọn kho nhập --</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Ngày điều chuyển *</label>
                        <input type="date" id="ngaydieuchuyen" name="ngaydieuchuyen" required class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Ghi chú</label>
                        <input type="text" id="ghichu" name="ghichu" class="form-control" placeholder="Lý do điều chuyển...">
                    </div>
                </div>

                <hr class="mb-4">

                <h5 class="fw-bold mb-3">Chi tiết sản phẩm điều chuyển</h5>
                <div id="product-list" class="mb-3">
                    <div class="row product-item mb-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-muted">Sản phẩm</label>
                            <select name="masp[]" class="form-select sp-select" required>
                                <option value="">-- Chọn sản phẩm --</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-muted">Số lượng</label>
                            <input type="number" name="soluong[]" step="0.01" min="0" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-item w-100" style="display: none;">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button" id="add-product" class="btn btn-outline-primary mb-4">
                    <i class="fas fa-plus"></i> Thêm sản phẩm
                </button>

                <div class="d-flex justify-content-end border-top pt-3">
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">
                        <i class="fas fa-save me-1"></i> Tạo phiếu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // ======================= MICROSERVICES FETCH API =========================
    let productsData = [];

    // Gọi Gateway lấy kho và sản phẩm
    async function initData() {
        try {
            const token = localStorage.getItem('token');
            const headers = { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' };
            
            const [resKho, resSp] = await Promise.all([
                fetch('/vlxd/api_gateway.php/warehouses', { headers }),
                fetch('/vlxd/api_gateway.php/products', { headers })
            ]);
            
            const dataKho = await resKho.json();
            const dataSp = await resSp.json();

            if(dataKho.success && dataKho.data.warehouses) {
                let kHtml = '<option value="">-- Chọn kho --</option>';
                dataKho.data.warehouses.forEach(k => {
                    kHtml += `<option value="${k.Makho}">${k.Tenkho}</option>`;
                });
                document.getElementById('khoxuat').innerHTML = kHtml;
                document.getElementById('khonhap').innerHTML = kHtml;
            }

            if(dataSp.success && dataSp.data.products) {
                productsData = dataSp.data.products;
                updateProductSelects();
            }
        } catch(e) {
            console.error("Lỗi lấy dữ liệu API", e);
            showAlertCustom("Không thể tải dữ liệu kho/sản phẩm từ máy chủ.", "danger");
        }
    }

    function updateProductSelects() {
        let pHtml = '<option value="">-- Chọn sản phẩm --</option>';
        productsData.forEach(p => {
            pHtml += `<option value="${p.Masp}">${p.Masp} - ${p.Tensp}</option>`;
        });
        document.querySelectorAll('.sp-select').forEach(sel => {
            if(sel.options.length <= 1) sel.innerHTML = pHtml;
        });
    }

    // Logic thêm/xóa dòng sản phẩm
    document.getElementById("add-product").addEventListener("click", function () {
        const productList = document.getElementById("product-list");
        const newItem = productList.querySelector(".product-item").cloneNode(true);
        newItem.querySelector("select").selectedIndex = 0;
        newItem.querySelector("input").value = "";
        newItem.querySelector(".remove-item").style.display = "block";
        productList.appendChild(newItem);
    });

    document.addEventListener("click", function (e) {
        if (e.target.closest(".remove-item")) {
            e.target.closest(".product-item").remove();
        }
    });

    // Logic Submit Form tạo phiếu
    async function submitTransfer() {
        const mag = document.getElementById("madieuchuyen").value.trim();
        const kx = document.getElementById("khoxuat").value;
        const kn = document.getElementById("khonhap").value;
        const ns = document.getElementById("ngaydieuchuyen").value;
        const note = document.getElementById("ghichu").value.trim();

        if (kx === kn) {
            showAlertCustom('Kho xuất và Kho nhập không được trùng nhau.', 'warning');
            return;
        }

        const items = [];
        document.querySelectorAll('.product-item').forEach(el => {
            const p = el.querySelector('select').value;
            const sl = el.querySelector('input').value;
            if(p && sl && parseFloat(sl) > 0) {
                items.push({ Masp: p, Soluong: parseFloat(sl) });
            }
        });

        if(items.length === 0) {
            showAlertCustom('Vui lòng thêm ít nhất một sản phẩm với số lượng hợp lệ.', 'warning');
            return;
        }

        const payload = {
            Madieuchuyen: mag,
            Khoxuat: kx,
            Khonhap: kn,
            Ngaydieuchuyen: ns,
            Ghichu: note,
            details: items
        };

        try {
            const token = localStorage.getItem('token');
            const res = await fetch('/vlxd/api_gateway.php/transfers', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(payload)
            });
            const d = await res.json();
            
            if(d.success) {
                showAlertCustom('Tạo phiếu điều chuyển thành công!', 'success');
                // ĐÃ SỬA: Chuyển hướng sau khi tạo thành công
                setTimeout(() => window.location.href = '/vlxd/phieu-dieuchuyen-danh-sach', 1000);
            } else {
                showAlertCustom(d.message || 'Lỗi tạo phiếu từ máy chủ.', 'danger');
            }
        } catch(e) {
            console.error(e);
            showAlertCustom('Lỗi kết nối máy chủ.', 'danger');
        }
    }

    // Custom Alert Box cho component này
    function showAlertCustom(msg, type) {
        const a = document.getElementById('alertMsg');
        a.className = `alert alert-${type} mb-4`;
        a.innerHTML = msg;
        
        window.scrollTo({top: 0, behavior: 'smooth'});
        
        if(type === 'success' || type === 'warning') {
            setTimeout(() => { a.classList.add('d-none'); }, 4000);
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        initData();
        document.getElementById('ngaydieuchuyen').valueAsDate = new Date();
    });
</script>