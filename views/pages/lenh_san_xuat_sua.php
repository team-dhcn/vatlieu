<?php
$id = $_GET['id'] ?? null;
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1 text-dark" style="font-size:1.6rem"><i class="fas fa-edit me-2 text-primary"></i>Sửa lệnh sản xuất</h1>
        <p class="text-muted mb-0" id="orderIdDisplay">Cập nhật thông tin chi tiết của lệnh sản xuất</p>
    </div>
    <a href="lenh-san-xuat-danh-sach" class="btn btn-outline-secondary fw-bold shadow-sm px-4">
        <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle me-2 text-primary"></i>Thông tin lệnh</h6>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase mb-1">Mã lệnh</label>
                <input class="form-control bg-light fw-bold text-primary" id="fMalenh" readonly value="<?= htmlspecialchars($id) ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold small text-uppercase mb-1">Sản phẩm *</label>
                <input class="form-control bg-light" id="fTensp" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-uppercase mb-1">Trạng thái hiện tại</label>
                <div id="fTrangthaiDisplay"></div>
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold small text-uppercase mb-1">Số lượng sản xuất *</label>
                <input type="number" class="form-control shadow-none" id="fSlsx" min="1">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-uppercase mb-1">Ngày lập kế hoạch *</label>
                <input type="date" class="form-control shadow-none" id="fNgaysx">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-uppercase mb-1">Ngày bắt đầu dự kiến</label>
                <input type="date" class="form-control shadow-none" id="fNgaybd">
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold small text-uppercase mb-1">Ngày kết thúc dự kiến</label>
                <input type="date" class="form-control shadow-none" id="fNgaykt">
            </div>
            <div class="col-md-8">
                <label class="form-label fw-bold small text-uppercase mb-1">Ghi chú / Mô tả</label>
                <textarea class="form-control shadow-none" id="fGhichu" rows="1"></textarea>
            </div>
        </div>
    </div>
</div>

<div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
    <button class="btn btn-primary btn-lg fw-bold px-5 py-3 shadow" id="btnUpdate" onclick="updateOrder()">
        <i class="fas fa-save me-2"></i>Lưu các thay đổi
    </button>
</div>

<style>
    .badge-status { padding: 4px 12px; border-radius: 20px; font-size: .8rem; font-weight: 700; }
</style>

<script>
const malenh = '<?= htmlspecialchars($id) ?>';
let currentMasp = '';

async function loadData() {
    if (!malenh) return alert('Không tìm thấy mã lệnh!');
    const headers = getHeaders();
    if (!headers) return;
    try {
        const res = await fetch(API + '/production-orders/' + malenh, { headers });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        
        const r = data.data;
        currentMasp = r.Masp;
        document.getElementById('fTensp').value = r.Tensp || r.Masp;
        document.getElementById('fSlsx').value = r.Soluongsanxuat;
        document.getElementById('fNgaysx').value = r.Ngaysanxuat;
        document.getElementById('fNgaybd').value = r.Ngaybatdau;
        document.getElementById('fNgaykt').value = r.Ngayketthuc;
        document.getElementById('fGhichu').value = r.Ghichu || '';
        
        const st = statusBadgeConfig(r.Trangthai);
        document.getElementById('fTrangthaiDisplay').innerHTML = `<span class="badge-status ${st.class}">${st.label}</span>`;
        
        // Disable update if completed or cancelled
        if (r.Trangthai === 'hoan_thanh' || r.Trangthai === 'huy') {
            document.getElementById('btnUpdate').disabled = true;
            document.getElementById('btnUpdate').innerHTML = '<i class="fas fa-lock me-2"></i>Lệnh sản xuất đã đóng (Không thể sửa)';
            
            ['fSlsx', 'fNgaysx', 'fNgaybd', 'fNgaykt'].forEach(id => {
                document.getElementById(id).readOnly = true;
                document.getElementById(id).classList.add('bg-light');
            });
        }
    } catch (e) {
        alert('Lỗi khi tải thông tin chi tiết: ' + e.message);
    }
}

function statusBadgeConfig(s) {
    const config = {
        cho_xu_ly: { class: 'bg-warning text-dark', label: 'CHỜ XỬ LÝ' },
        dang_san_xuat: { class: 'bg-primary text-white', label: 'ĐANG SẢN XUẤT' },
        hoan_thanh: { class: 'bg-success text-white', label: 'HOÀN THÀNH' },
        huy: { class: 'bg-danger text-white', label: 'ĐÃ HỦY' }
    };
    return config[s] || { class: 'bg-secondary text-white', label: s };
}

async function updateOrder() {
    const headers = getHeaders();
    if (!headers) return;
    
    const payload = {
        Masp: currentMasp,
        Soluongsanxuat: parseInt(document.getElementById('fSlsx').value),
        Ngaysanxuat: document.getElementById('fNgaysx').value,
        Ngaybatdau: document.getElementById('fNgaybd').value || null,
        Ngayketthuc: document.getElementById('fNgaykt').value || null,
        Ghichu: document.getElementById('fGhichu').value || null
    };

    if (!payload.Soluongsanxuat || !payload.Ngaysanxuat) return alert('Số lượng và Ngày lập kế hoạch là bắt buộc.');

    try {
        const res = await fetch(API + '/production-orders/' + malenh, {
            method: 'PUT',
            headers: { ...headers, 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            alert('Cập nhật lệnh ' + malenh + ' thành công!');
            window.location.href = 'lenh-san-xuat-danh-sach';
        } else alert('Lỗi: ' + data.message);
    } catch (e) { alert('Lỗi kết nối API'); }
}

document.addEventListener('DOMContentLoaded', loadData);
</script>
