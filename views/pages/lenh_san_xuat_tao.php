<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1 text-dark" style="font-size:1.6rem"><i class="fas fa-plus-circle me-2 text-primary"></i>Tạo lệnh sản xuất</h1>
        <p class="text-muted mb-0">Lên kế hoạch sản xuất thành phẩm theo quy trình</p>
    </div>
    <a href="lenh-san-xuat-danh-sach" class="btn btn-outline-secondary fw-bold shadow-sm px-4">
        <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
    </a>
</div>

<div class="row g-4">
    <!-- Form thông tin chính -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle me-2 text-primary"></i>Thông tin chung</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase mb-1">Mã lệnh sản xuất (Tùy chọn)</label>
                    <input class="form-control shadow-none" id="fMalenh" placeholder="Hệ thống sẽ tự tạo nếu để trống">
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase mb-1">Sản phẩm cần sản xuất *</label>
                    <select class="form-select shadow-none" id="fMasp" onchange="loadFormula()">
                        <option value="">-- Chọn sản phẩm --</option>
                    </select>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase mb-1">Số lượng SX *</label>
                        <input type="number" class="form-control shadow-none" id="fSlsx" min="1" value="1" placeholder="Nhập số lượng">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase mb-1">Ngày lập kế hoạch *</label>
                        <input type="date" class="form-control shadow-none" id="fNgaysx">
                    </div>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase mb-1">Ngày bắt đầu dự kiến</label>
                        <input type="date" class="form-control shadow-none" id="fNgaybd">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase mb-1">Ngày kết thúc dự kiến</label>
                        <input type="date" class="form-control shadow-none" id="fNgaykt">
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-bold small text-uppercase mb-1">Ghi chú / Mô tả</label>
                    <textarea class="form-control shadow-none" id="fGhichu" rows="2" placeholder="Nhập ghi chú hoặc mô tả cho lệnh sản xuất này..."></textarea>
                </div>
                
                <div class="mt-5 d-grid">
                    <button class="btn btn-primary btn-lg fw-bold px-4 py-3 shadow" onclick="submitOrder()">
                        <i class="fas fa-save me-2"></i>Lưu lệnh sản xuất
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hiển thị công thức (BOM) -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-success text-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-flask me-2"></i>Công thức sản xuất (BOM)</h6>
            </div>
            <div class="card-body" id="formulaContainer">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-arrow-left fa-2x mb-3 opacity-25"></i>
                    <p class="mb-0 small">Chọn sản phẩm để xem định mức nguyên vật liệu cần thiết</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .formula-list { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 12px; }
    .formula-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid rgba(187, 247, 208, 0.5); }
    .formula-item:last-child { border-bottom: none; }
</style>

<script>
let formulaData = [];

async function loadInitialData() {
    const headers = getHeaders();
    if (!headers) return;
    try {
        const res = await fetch(API + '/products', { headers });
        const data = await res.json();
        if (data.success) {
            const sel = document.getElementById('fMasp');
            sel.innerHTML = '<option value="">-- Chọn sản phẩm --</option>' + 
                data.data.products.map(p => `<option value="${p.Masp}">${p.Tensp} [${p.Masp}]</option>`).join('');
        }
    } catch (e) {
        showAlert('Lỗi tải danh sách sản phẩm', 'danger');
    }
}

async function loadFormula() {
    const masp = document.getElementById('fMasp').value;
    const container = document.getElementById('formulaContainer');
    if (!masp) {
        container.innerHTML = `<div class="text-center py-5 text-muted"><p class="mb-0 small">Chọn sản phẩm để xem định mức nguyên vật liệu</p></div>`;
        return;
    }
    
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm me-2 text-success"></div>Đang tính báo cáo...</div>';
    
    const headers = getHeaders();
    if (!headers) return;
    
    try {
        const res = await fetch(API + '/formulas?Masp=' + masp, { headers });
        const data = await res.json();
        
        if (!data.success || !data.data.formulas.length) {
            container.innerHTML = `<div class="alert alert-warning border-0 small shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i>Sản phẩm này chưa được cấu hình công thức sản xuất.</div>`;
            return;
        }

        formulaData = data.data.formulas;
        updateFormulaUI();
    } catch (e) {
        container.innerHTML = `<div class="alert alert-danger border-0 small shadow-sm">Lỗi tải dữ liệu.</div>`;
    }
}

function updateFormulaUI() {
    const sl = parseInt(document.getElementById('fSlsx').value) || 1;
    const container = document.getElementById('formulaContainer');
    
    container.innerHTML = `
        <div class="formula-list shadow-sm">
            ${formulaData.map(f => `
                <div class="formula-item">
                    <div>
                        <div class="fw-bold text-dark fs-7">${f.Tennvl || f.Manvl}</div>
                        <small class="text-muted" style="font-size:0.65rem">Mã NVL: ${f.Manvl}</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-success" style="font-size:0.9rem">${(f.Soluong * sl).toLocaleString('vi-VN')}</div>
                        <small class="badge bg-white text-success border border-success-subtle rounded-pill font-monospace" style="font-size:0.6rem">
                            ${f.Soluong} ${f.Dvt || ''} × ${sl}
                        </small>
                    </div>
                </div>
            `).join('')}
        </div>
        <div class="mt-3 p-3 bg-light rounded text-center small text-muted italic">
            <i class="fas fa-info-circle me-1"></i> Số lượng này là định mức dự kiến dựa trên lệnh sản xuất.
        </div>
    `;
}

async function submitOrder() {
    const masp = document.getElementById('fMasp').value;
    const sl = document.getElementById('fSlsx').value;
    const ngaysx = document.getElementById('fNgaysx').value;
    
    if (!masp || !sl || !ngaysx) {
        return alert('Vui lòng nhập đầy đủ Sản phẩm, Số lượng và Ngày lập kế hoạch.');
    }

    const payload = {
        Malenh: document.getElementById('fMalenh').value || undefined,
        Masp: masp,
        Soluongsanxuat: parseInt(sl),
        Ngaysanxuat: ngaysx,
        Ngaybatdau: document.getElementById('fNgaybd').value || null,
        Ngayketthuc: document.getElementById('fNgaykt').value || null,
        Ghichu: document.getElementById('fGhichu').value || null,
        Trangthai: 'cho_xu_ly'
    };

    const headers = getHeaders();
    if (!headers) return;

    try {
        const res = await fetch(API + '/production-orders', {
            method: 'POST',
            headers: { ...headers, 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        if (data.success) {
            alert('Đã tạo lệnh sản xuất thành công!');
            window.location.href = 'lenh-san-xuat-danh-sach';
        } else {
            if (data.data && data.data.missing) {
                let msg = 'KHÔNG ĐỦ NGUYÊN VẬT LIỆU:\n\n';
                data.data.missing.forEach(m => {
                    msg += `- ${m.Tennvl}: Cần ${m.Required}, Hiện có ${m.Available} (Thiếu ${m.Missing})\n`;
                });
                msg += '\nVui lòng nhập hàng trước khi tạo lệnh.';
                alert(msg);
            } else {
                alert('Lỗi: ' + data.message);
            }
        }
    } catch (e) { alert('Lỗi kết nối API'); }
}

document.getElementById('fSlsx').addEventListener('input', () => {
    if (formulaData.length) updateFormulaUI();
});

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('fNgaysx').valueAsDate = new Date();
    document.getElementById('fNgaybd').valueAsDate = new Date();
    loadInitialData();
});
</script>
