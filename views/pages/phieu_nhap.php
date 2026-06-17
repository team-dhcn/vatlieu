<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1 text-dark" style="font-size:1.6rem"><i class="fas fa-file-import me-2 text-primary"></i>Tạo phiếu nhập kho</h1>
        <p class="text-muted mb-0">Ghi nhận nguyên vật liệu nhập và cập nhật tồn kho</p>
    </div>
    <a href="phieu-nhap-danh-sach" class="btn btn-outline-secondary fw-bold shadow-sm px-4">
        <i class="fas fa-arrow-left me-2"></i>Quay lại
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light py-3 border-bottom-0">
        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle me-2 text-primary"></i>Thông tin chung</h6>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase mb-1">Mã nhập hàng</label>
                <input class="form-control" id="fMa" placeholder="VD: PN2024001">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase mb-1">Nhà cung cấp *</label>
                <select class="form-select" id="fNcc shadow-none"><option value="">-- Chọn NCC --</option></select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase mb-1">Kho nhập *</label>
                <select class="form-select" id="fKho shadow-none"><option value="">-- Chọn kho --</option></select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase mb-1">Ngày nhập *</label>
                <input type="date" class="form-control" id="fNgay">
            </div>
        </div>
        <div class="mb-0">
            <label class="form-label fw-bold small text-uppercase mb-1">Ghi chú</label>
            <input class="form-control" id="fGhichu" placeholder="Nhập ghi chú (nếu có)">
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center py-3 border-bottom-0">
        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-list-ul me-2 text-primary"></i>Chi tiết hàng nhập</h6>
        <button class="btn btn-sm btn-primary fw-bold" onclick="addRow()"><i class="fas fa-plus me-1"></i>Thêm NVL</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-borderless table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Nguyên vật liệu</th>
                        <th width="150px">Số lượng</th>
                        <th width="180px">Đơn giá nhập (đ)</th>
                        <th class="text-end" width="180px">Thành tiền</th>
                        <th width="80px" class="text-center pe-4">Xóa</th>
                    </tr>
                </thead>
                <tbody id="detailContainer"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 mb-4">
    <div class="card-body bg-light rounded shadow-sm d-flex justify-content-between align-items-center py-4 px-4">
        <h5 class="fw-bold text-dark mb-0">TỔNG GIÁ TRỊ PHIẾU:</h5>
        <h4 class="fw-bold text-primary mb-0" id="totalAmount">0 đ</h4>
    </div>
</div>

<div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
    <button class="btn btn-lg btn-success fw-bold px-5 py-3 shadow" onclick="submitReceipt()">
        <i class="fas fa-save me-2"></i>Lưu phiếu nhập kho
    </button>
</div>

<style>
    .detail-row input, .detail-row select { border-radius: 8px; }
    .table-light th { font-size: 0.75rem; color: #64748b; letter-spacing: 0.5px; }
</style>

<script>
let materialsData = [];

async function loadDropdowns() {
    const headers = getHeaders();
    if (!headers) return;
    try {
        const [resNcc, resKho, resMat] = await Promise.all([
            fetch(API + '/suppliers', { headers }),
            fetch(API + '/warehouses', { headers }),
            fetch(API + '/materials', { headers })
        ]);
        const [dNcc, dKho, dMat] = await Promise.all([resNcc.json(), resKho.json(), resMat.json()]);
        
        if (dNcc.success) { 
            const selNcc = document.getElementById('fNcc shadow-none'); 
            selNcc.innerHTML = '<option value="">-- Chọn NCC --</option>' + dNcc.data.suppliers.map(s => `<option value="${s.Mancc}">${s.Tenncc}</option>`).join(''); 
        }
        if (dKho.success) { 
            const selKho = document.getElementById('fKho shadow-none'); 
            selKho.innerHTML = '<option value="">-- Chọn kho --</option>' + dKho.data.warehouses.map(k => `<option value="${k.Makho}">[${k.Makho}] ${k.Tenkho}</option>`).join(''); 
        }
        if (dMat.success) { materialsData = dMat.data.materials; }
    } catch (e) { showAlert('Lỗi tải dữ liệu cơ sở: ' + e.message, 'danger'); }
}

function addRow() {
    const tbody = document.getElementById('detailContainer');
    const tr = document.createElement('tr');
    tr.className = 'detail-row border-bottom';
    tr.innerHTML = `
        <td class="ps-4">
            <select class="form-select border-0 bg-transparent ps-0 fw-semibold inp-mat" onchange="updateLine(this)">
                <option value="">-- Chọn NVL --</option>
                ${materialsData.map(m => `<option value="${m.Manvl}">${m.Tennvl} (${m.Dvt})</option>`).join('')}
            </select>
        </td>
        <td>
            <input type="number" class="form-control border-0 bg-transparent inp-sl" value="1" min="1" oninput="calcTotal()">
        </td>
        <td>
            <input type="number" class="form-control border-0 bg-transparent inp-dg" value="0" min="0" oninput="calcTotal()">
        </td>
        <td class="text-end fw-bold text-dark pe-4 line-total">0 đ</td>
        <td class="text-center pe-4">
            <button class="btn btn-link text-danger p-0" onclick="removeRow(this)"><i class="fas fa-times-circle fs-5"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
    calcTotal();
}

function removeRow(btn) {
    btn.closest('tr').remove();
    calcTotal();
}

function updateLine(sel) {
    calcTotal();
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('.detail-row').forEach(tr => {
        const sl = parseFloat(tr.querySelector('.inp-sl').value || 0);
        const dg = parseFloat(tr.querySelector('.inp-dg').value || 0);
        const st = sl * dg;
        tr.querySelector('.line-total').textContent = Number(st).toLocaleString('vi-VN') + ' đ';
        total += st;
    });
    document.getElementById('totalAmount').textContent = Number(total).toLocaleString('vi-VN') + ' đ';
}

async function submitReceipt() {
    const headers = getHeaders();
    if (!headers) return;
    
    const mancc = document.getElementById('fNcc shadow-none').value;
    const makho = document.getElementById('fKho shadow-none').value;
    const ngay = document.getElementById('fNgay').value;
    const ghichu = document.getElementById('fGhichu').value;
    
    if (!mancc || !makho || !ngay) return alert('Vui lòng nhập đầy đủ NCC, Kho và Ngày nhập hàng.');

    const details = [];
    document.querySelectorAll('.detail-row').forEach(tr => {
        const manvl = tr.querySelector('.inp-mat').value;
        const sl = parseFloat(tr.querySelector('.inp-sl').value || 0);
        const dg = parseFloat(tr.querySelector('.inp-dg').value || 0);
        if (manvl && sl > 0) details.push({ Manvl: manvl, Soluong: sl, Dongianhap: dg });
    });

    if (details.length === 0) return alert('Vui lòng thêm ít nhất một mặt hàng.');

    const payload = {
        Manhaphang: document.getElementById('fMa').value || undefined,
        Mancc: mancc,
        Makho: makho,
        Ngaynhaphang: ngay,
        Ghichu: ghichu,
        details: details
    };

    try {
        const res = await fetch(API + '/import-receipts', {
            method: 'POST',
            headers: { ...headers, 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            alert('Tạo phiếu nhập thành công!');
            window.location.href = 'phieu-nhap-danh-sach';
        } else alert('Lỗi: ' + data.message);
    } catch (e) { alert('Lỗi kết nối máy chủ'); }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('fNgay').valueAsDate = new Date();
    loadDropdowns().then(() => {
        addRow(); // Start with one row
    });
});
</script>
