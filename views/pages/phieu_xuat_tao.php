<!-- views/pages/phieu_xuat_tao.php -->
<div class="page-header d-flex justify-content-between align-items-center mb-4 p-4 rounded-3 shadow-sm bg-gradient-export">
    <div>
        <h1 class="mb-1 text-white fw-bold fs-3"><i class="fas fa-file-export me-2"></i>Tạo phiếu xuất hàng mới</h1>
        <p class="mb-0 text-white opacity-75">Xuất thành phẩm từ kho cho khách hàng và đại lý</p>
    </div>
    <a href="phieu-xuat-danh-sach" class="btn btn-light btn-lg fw-bold text-export shadow-sm"><i class="fas fa-list-ul me-2"></i>Xem danh sách</a>
</div>

<div id="statusAlert" class="alert d-none mb-4 shadow-sm border-0"></div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header-export p-3 border-bottom d-flex align-items-center">
        <h5 class="mb-0 text-white fw-bold"><i class="fas fa-info-circle me-2"></i>Thông tin phiếu xuất</h5>
    </div>
    <div class="card-body p-4">
        <form id="createExportForm">
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Mã phiếu xuất</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-hashtag"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" id="fMaPX" placeholder="Tự động tạo nếu để trống">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Khách hàng <span class="text-danger">*</span></label>
                    <select class="form-select border-1 select2-custom" id="fMakh" required>
                        <option value="">-- Chọn khách hàng --</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Kho xuất hàng <span class="text-danger">*</span></label>
                    <select class="form-select border-1" id="fMakho" required>
                        <option value="">-- Chọn kho xuất --</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Ngày xuất <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fNgayXuat" required>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <label class="form-label text-muted small fw-bold text-uppercase">Ghi chú (Tùy chọn)</label>
                    <textarea class="form-control" id="fGhichu" rows="1" placeholder="Ví dụ: Xuất hàng cho đơn hàng #12345..."></textarea>
                </div>
            </div>

            <hr class="my-4 opacity-10">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-export mb-0"><i class="fas fa-boxes me-2"></i>Chi tiết danh sách hàng xuất</h6>
                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="addItemRow()">
                    <i class="fas fa-plus me-1"></i>Thêm sản phẩm
                </button>
            </div>

            <div id="itemsContainer" class="mb-4">
                <!-- Dynamic rows here -->
            </div>

            <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center mb-4">
                <span class="fw-bold text-muted text-uppercase small">Tổng giá trị đơn xuất:</span>
                <div>
                    <span class="fs-4 fw-bold text-success" id="grandTotal">0</span>
                    <span class="fs-4 fw-bold text-success ms-1">VNĐ</span>
                </div>
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-lg btn-export-action px-5 rounded-pill shadow-sm" id="btnSubmit">
                    <i class="fas fa-check-circle me-2"></i>Xác nhận & Lưu phiếu
                </button>
                <a href="phieu-xuat-danh-sach" class="btn btn-lg btn-outline-secondary px-4 rounded-pill border-0">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>

<style>
    .bg-gradient-export { background: linear-gradient(135deg, #7c2d12 0%, #ea580c 100%); }
    .card-header-export { background: linear-gradient(90deg, #ea580c 0%, #f97316 100%); }
    .text-export { color: #ea580c; }
    .btn-export-action { background: #ea580c; color: white; transition: all 0.2s; font-weight: 700; border: none; }
    .btn-export-action:hover { background: #9a3412; color: white; transform: translateY(-2px); box-shadow: 0 8px 16px rgba(124, 45, 18, 0.2); }
    .item-row { transition: all 0.2s; border-radius: 12px; margin-bottom: 12px; border: 1px solid #f1f5f9; background: #fff; }
    .item-row:hover { border-color: #ffedd5; background: #fffaf5; }
</style>

<script>
    const API_EXPORT_V1 = '/vlxd/api_gateway.php';
    const authHeaders_V1 = { 'Authorization': 'Bearer ' + localStorage.getItem('token'), 'Content-Type': 'application/json' };
    let availableProducts = [];

    async function loadFormDropdowns() {
        try {
            const [khRes, khoRes, spRes] = await Promise.all([
                fetch(`${API_EXPORT_V1}/customers`, { headers: authHeaders_V1 }),
                fetch(`${API_EXPORT_V1}/warehouses`, { headers: authHeaders_V1 }),
                fetch(`${API_EXPORT_V1}/products`, { headers: authHeaders_V1 })
            ]);
            
            const kData = await khRes.json();
            const whData = await khoRes.json();
            const spData = await spRes.json();
            
            if(kData.success) {
                document.getElementById('fMakh').innerHTML += kData.data.customers.map(k => `<option value="${k.Makh}">${k.Tenkh}</option>`).join('');
            }
            if(whData.success) {
                document.getElementById('fMakho').innerHTML += whData.data.warehouses.map(w => `<option value="${w.Makho}">[${w.Makho}] ${w.Tenkho}</option>`).join('');
            }
            if(spData.success) {
                availableProducts = spData.data.products;
                // Add initial row once products are loaded
                addItemRow();
            }
        } catch (e) {
            showNotify('Không thể tải các danh mục lựa chọn (Khách hàng, Kho, SP)', 'danger');
        }
    }

    function addItemRow() {
        const container = document.getElementById('itemsContainer');
        const div = document.createElement('div');
        div.className = 'item-row p-3 shadow-sm';
        div.innerHTML = `
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted">Sản phẩm xuất</label>
                    <select class="form-select border-0 bg-light select-product" onchange="updateRowPrice(this)" required>
                        <option value="">-- Chọn sản phẩm --</option>
                        ${availableProducts.map(p => `<option value="${p.Masp}" data-price="${p.Giaban || 0}">${p.Tensp} (${p.Dvt || ''})</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Số lượng</label>
                    <input type="number" min="0.01" step="0.01" class="form-control border-0 bg-light input-qty" placeholder="0.00" oninput="calculateGrandTotal()" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Đơn giá (đ)</label>
                    <input type="number" min="0" step="1000" class="form-control border-0 bg-light input-price" placeholder="Giá bán" oninput="calculateGrandTotal()" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Thành tiền</label>
                    <div class="form-control border-0 bg-light fw-bold text-success text-end row-total">0</div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-link text-danger p-0 h-100" onclick="removeItemRow(this)">
                        <i class="fas fa-trash-alt fs-5 mt-4"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    function removeItemRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            btn.closest('.item-row').remove();
            calculateGrandTotal();
        } else {
            alert('Cần ít nhất một sản phẩm trong phiếu xuất.');
        }
    }

    function updateRowPrice(select) {
        const row = select.closest('.item-row');
        const priceInput = row.querySelector('.input-price');
        const selectedOption = select.options[select.selectedIndex];
        const basePrice = selectedOption.getAttribute('data-price');
        if (basePrice) priceInput.value = basePrice;
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.input-qty').value || 0);
            const price = parseFloat(row.querySelector('.input-price').value || 0);
            const rowSum = qty * price;
            row.querySelector('.row-total').textContent = rowSum.toLocaleString('vi-VN');
            total += rowSum;
        });
        document.getElementById('grandTotal').textContent = total.toLocaleString('vi-VN');
    }

    function showNotify(msg, type) {
        const al = document.getElementById('statusAlert');
        al.className = `alert alert-${type} d-block shadow-sm border-0`;
        al.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i> ${msg}`;
        window.scrollTo(0, 0);
        if(type === 'success') setTimeout(() => { al.classList.add('d-none'); }, 5000);
    }

    document.getElementById('createExportForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...';

        const details = [];
        document.querySelectorAll('.item-row').forEach(row => {
            const masp = row.querySelector('.select-product').value;
            const sl = parseFloat(row.querySelector('.input-qty').value || 0);
            const dg = parseFloat(row.querySelector('.input-price').value || 0);
            if (masp && sl > 0) details.push({ Masp: masp, Soluong: sl, Dongiaxuat: dg });
        });

        const payload = {
            Maxuathang: document.getElementById('fMaPX').value.trim() || undefined,
            Makh: document.getElementById('fMakh').value,
            Makho: document.getElementById('fMakho').value,
            Ngayxuat: document.getElementById('fNgayXuat').value,
            Ghichu: document.getElementById('fGhichu').value,
            details: details
        };

        try {
            const res = await fetch(`${API_EXPORT_V1}/export-receipts`, {
                method: 'POST',
                headers: authHeaders_V1,
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            if (data.success) {
                showNotify(`Đã lưu phiếu xuất thành công! (Mã: ${data.data.id})`, 'success');
                setTimeout(() => window.location.href = 'phieu-xuat-danh-sach', 2000);
            } else {
                showNotify(data.message, 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Xác nhận & Lưu phiếu';
            }
        } catch (error) {
            showNotify('Lỗi kết nối server', 'danger');
            btn.disabled = false;
        }
    });

    // Init
    document.getElementById('fNgayXuat').valueAsDate = new Date();
    loadFormDropdowns();
</script>
