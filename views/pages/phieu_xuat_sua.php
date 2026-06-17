<!-- views/pages/phieu_xuat_sua.php -->
<div class="page-header d-flex justify-content-between align-items-center mb-4 p-4 rounded-3 shadow-sm bg-gradient-export">
    <div>
        <h1 class="mb-1 text-white fw-bold fs-3"><i class="fas fa-edit me-2"></i>Sửa phiếu xuất kho</h1>
        <p class="mb-0 text-white opacity-75">Cập nhật thông tin phiếu xuất và điều chỉnh tồn kho</p>
    </div>
    <div class="d-flex gap-2">
        <a href="phieu-xuat-chi-tiet?id=${encodeURIComponent(new URLSearchParams(window.location.search).get('id'))}" class="btn btn-light fw-bold text-dark border-0 shadow-sm" id="btnViewDetail"><i class="fas fa-eye me-2"></i>Xem chi tiết</a>
        <a href="phieu-xuat-danh-sach" class="btn btn-outline-light fw-bold border-1 shadow-sm"><i class="fas fa-arrow-left me-2"></i>Danh sách</a>
    </div>
</div>

<div id="editLoading" class="text-center py-5">
    <div class="spinner-border text-warning" role="status"></div>
    <div class="mt-2 text-muted">Đang tải dữ liệu phiếu xuất...</div>
</div>

<div id="editContent" class="d-none">
    <div id="editStatusAlert" class="alert d-none mb-4 shadow-sm border-0"></div>

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-primary p-3 border-bottom d-flex align-items-center">
            <h5 class="mb-0 text-white fw-bold"><i class="fas fa-info-circle me-2"></i>Cập nhật thông tin phiếu</h5>
        </div>
        <div class="card-body p-4">
            <form id="editExportForm">
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Mã phiếu xuất</label>
                        <input type="text" class="form-control bg-light border-0 fw-bold" id="eMaPX" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Khách hàng <span class="text-danger">*</span></label>
                        <select class="form-select border-1" id="eMakh" required>
                            <option value="">-- Chọn khách hàng --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Kho xuất hàng <span class="text-danger">*</span></label>
                        <select class="form-select border-1" id="eMakho" required>
                            <option value="">-- Chọn kho xuất --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Ngày xuất <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="eNgayXuat" required>
                    </div>
                </div>
                
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label text-muted small fw-bold text-uppercase">Ghi chú (Tùy chọn)</label>
                        <textarea class="form-control" id="eGhichu" rows="1" placeholder="Ghi chú..."></textarea>
                    </div>
                </div>

                <hr class="my-4 opacity-10">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-primary mb-0"><i class="fas fa-boxes me-2"></i>Chi tiết danh sách hàng xuất</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="addEditItemRow()">
                        <i class="fas fa-plus me-1"></i>Thêm sản phẩm
                    </button>
                </div>

                <div id="editItemsContainer" class="mb-4">
                    <!-- Dynamic rows -->
                </div>

                <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold text-muted text-uppercase small">Tổng giá trị đơn xuất:</span>
                    <div>
                        <span class="fs-4 fw-bold text-success" id="editGrandTotal">0</span>
                        <span class="fs-4 fw-bold text-success ms-1">VNĐ</span>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-lg btn-primary px-5 rounded-pill shadow-sm fw-bold" id="btnSaveUpdate">
                        <i class="fas fa-save me-2"></i>Lưu thay đổi
                    </button>
                    <a href="phieu-xuat-danh-sach" class="btn btn-lg btn-outline-secondary px-4 rounded-pill border-0">Hủy bỏ</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-gradient-export { background: linear-gradient(135deg, #7c2d12 0%, #ea580c 100%); }
    .text-export { color: #ea580c; }
    .item-row-edit { transition: all 0.2s; border-radius: 12px; margin-bottom: 12px; border: 1px solid #f1f5f9; background: #fff; }
    .item-row-edit:hover { border-color: #dbeafe; background: #f8fafc; }
</style>

<script>
    const API_EDIT_V1 = '/vlxd/api_gateway.php';
    const editHeaders = { 'Authorization': 'Bearer ' + localStorage.getItem('token'), 'Content-Type': 'application/json' };
    let editProducts = [];
    let receiptIdToEdit = new URLSearchParams(window.location.search).get('id');

    async function initEditPage() {
        if (!receiptIdToEdit) {
            alert('Mã phiếu không hợp lệ.');
            window.location.href = 'phieu-xuat-danh-sach';
            return;
        }

        document.getElementById('btnViewDetail').href = `phieu-xuat-chi-tiet?id=${encodeURIComponent(receiptIdToEdit)}`;

        try {
            // Load dropdowns and current data
            const [khRes, khoRes, spRes, receiptRes] = await Promise.all([
                fetch(`${API_EDIT_V1}/customers`, { headers: editHeaders }),
                fetch(`${API_EDIT_V1}/warehouses`, { headers: editHeaders }),
                fetch(`${API_EDIT_V1}/products`, { headers: editHeaders }),
                fetch(`${API_EDIT_V1}/export-receipts/${receiptIdToEdit}`, { headers: editHeaders })
            ]);
            
            const kData = await khRes.json();
            const whData = await khoRes.json();
            const spData = await spRes.json();
            const rData = await receiptRes.json();
            
            if(kData.success) document.getElementById('eMakh').innerHTML += kData.data.customers.map(k => `<option value="${k.Makh}">${k.Tenkh}</option>`).join('');
            if(whData.success) document.getElementById('eMakho').innerHTML += whData.data.warehouses.map(w => `<option value="${w.Makho}">[${w.Makho}] ${w.Tenkho}</option>`).join('');
            if(spData.success) editProducts = spData.data.products;

            if (rData.success) {
                const r = rData.data.receipt;
                document.getElementById('eMaPX').value = r.Maxuathang;
                document.getElementById('eMakh').value = r.Makh;
                document.getElementById('eMakho').value = r.Makho;
                document.getElementById('eNgayXuat').value = r.Ngayxuat;
                document.getElementById('eGhichu').value = r.Ghichu || '';
                
                // Pre-fill items
                const container = document.getElementById('editItemsContainer');
                container.innerHTML = '';
                r.details.forEach(item => {
                    addEditItemRow(item);
                });
                
                calculateEditTotal();
                
                // Show content
                document.getElementById('editLoading').classList.add('d-none');
                document.getElementById('editContent').classList.remove('d-none');
            } else {
                throw new Error(rData.message);
            }
        } catch (e) {
            document.getElementById('editLoading').innerHTML = `<div class="alert alert-danger mx-5">Lỗi: ${e.message}</div>`;
        }
    }

    function addEditItemRow(data = null) {
        const container = document.getElementById('editItemsContainer');
        const div = document.createElement('div');
        div.className = 'item-row-edit p-3 shadow-sm';
        div.innerHTML = `
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted">Sản phẩm xuất</label>
                    <select class="form-select border-0 bg-light select-product" onchange="updateEditRowPrice(this)" required>
                        <option value="">-- Chọn sản phẩm --</option>
                        ${editProducts.map(p => `<option value="${p.Masp}" data-price="${p.Giaban || 0}" ${data && data.Masp === p.Masp ? 'selected' : ''}>${p.Tensp} (${p.Dvt || ''})</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Số lượng</label>
                    <input type="number" min="0.01" step="0.01" class="form-control border-0 bg-light input-qty" value="${data ? data.Soluong : ''}" oninput="calculateEditTotal()" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Đơn giá (đ)</label>
                    <input type="number" min="0" step="1000" class="form-control border-0 bg-light input-price" value="${data ? data.Dongiaxuat : ''}" oninput="calculateEditTotal()" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Thành tiền</label>
                    <div class="form-control border-0 bg-light fw-bold text-success text-end row-total">0</div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-link text-danger p-0 h-100" onclick="removeEditRow(this)">
                        <i class="fas fa-trash-alt fs-5 mt-4"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(div);
        if (data) calculateEditTotal();
    }

    function removeEditRow(btn) {
        const rows = document.querySelectorAll('.item-row-edit');
        if (rows.length > 1) {
            btn.closest('.item-row-edit').remove();
            calculateEditTotal();
        }
    }

    function updateEditRowPrice(select) {
        const row = select.closest('.item-row-edit');
        const priceInput = row.querySelector('.input-price');
        const basePrice = select.options[select.selectedIndex].getAttribute('data-price');
        if (basePrice) priceInput.value = basePrice;
        calculateEditTotal();
    }

    function calculateEditTotal() {
        let total = 0;
        document.querySelectorAll('.item-row-edit').forEach(row => {
            const qty = parseFloat(row.querySelector('.input-qty').value || 0);
            const price = parseFloat(row.querySelector('.input-price').value || 0);
            const rowSum = qty * price;
            row.querySelector('.row-total').textContent = rowSum.toLocaleString('vi-VN');
            total += rowSum;
        });
        document.getElementById('editGrandTotal').textContent = total.toLocaleString('vi-VN');
    }

    function showEditNotify(msg, type) {
        const al = document.getElementById('editStatusAlert');
        al.className = `alert alert-${type} d-block shadow-sm border-0`;
        al.innerHTML = `<i class="fas fa-info-circle me-2"></i> ${msg}`;
        window.scrollTo(0, 0);
    }

    document.getElementById('editExportForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnSaveUpdate');
        btn.disabled = true;

        const details = [];
        document.querySelectorAll('.item-row-edit').forEach(row => {
            const masp = row.querySelector('.select-product').value;
            const sl = parseFloat(row.querySelector('.input-qty').value || 0);
            const dg = parseFloat(row.querySelector('.input-price').value || 0);
            if (masp && sl > 0) details.push({ Masp: masp, Soluong: sl, Dongiaxuat: dg });
        });

        const payload = {
            Makh: document.getElementById('eMakh').value,
            Makho: document.getElementById('eMakho').value,
            Ngayxuat: document.getElementById('eNgayXuat').value,
            Ghichu: document.getElementById('eGhichu').value,
            details: details
        };

        try {
            const res = await fetch(`${API_EDIT_V1}/export-receipts/${receiptIdToEdit}`, {
                method: 'PUT',
                headers: editHeaders,
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            if (data.success) {
                showEditNotify('Đã cập nhật phiếu xuất và điều chỉnh tồn kho thành công!', 'success');
                setTimeout(() => window.location.href = 'phieu-xuat-danh-sach', 2000);
            } else {
                showEditNotify(data.message, 'danger');
                btn.disabled = false;
            }
        } catch (error) {
            showEditNotify('Lỗi kết nối server', 'danger');
            btn.disabled = false;
        }
    });

    // Init
    initEditPage();
</script>
