<style>
    /* Ch? gi? l?i các CSS d?c thù cho component này */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
    .info-item { background: #f0fdf4; border-radius: 8px; padding: 12px 16px; border: 1px solid #bbf7d0; }
    .info-label { font-size: 0.75rem; color: #065f46; text-transform: uppercase; font-weight: 700; margin-bottom: 2px; }
    .info-value { font-size: 1rem; font-weight: 700; color: #1e293b; }
    .warning-box { background: #fffbeb; border: 2px solid #fcd34d; border-radius: 10px; padding: 16px 20px; margin-bottom: 20px; }
    .arrow-flow { display: flex; align-items: center; gap: 12px; background: #f0fdf4; border-radius: 10px; padding: 14px 20px; margin-bottom: 20px; border: 1px solid #86efac; }
    .kho-chip { background: white; border: 1px solid #6ee7b7; border-radius: 8px; padding: 8px 14px; font-weight: 700; color: #065f46; font-size: 0.9rem; }
    .ton-ok { color: #16a34a; font-weight: 700; }
    .ton-low { color: #dc2626; font-weight: 700; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 p-4 text-white rounded shadow-sm" style="background: linear-gradient(135deg, #065f46, #059669);">
        <div>
            <h1 class="h3 mb-1 fw-bold"><i class="fas fa-check-double me-2"></i>Xác nh?n th?c hi?n di?u chuy?n</h1>
            <p class="mb-0 opacity-75" id="subTitle">Ðang t?i thông tin phi?u...</p>
        </div>
        <a id="backBtn" href="index.php?page=phieu-dieuchuyen-danh-sach" class="btn btn-light btn-sm fw-bold">
            <i class="fas fa-arrow-left me-1"></i>Danh sách
        </a>
    </div>

    <div id="alertBox" class="alert d-none mb-3 shadow-sm"></div>

    <div id="mainContent" class="d-none">
        <div class="arrow-flow shadow-sm" id="arrowFlow"></div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <h6 class="fw-bold text-success mb-3"><i class="fas fa-info-circle me-1"></i>Thông tin phi?u</h6>
                <div class="info-grid" id="infoGrid"></div>
                
                <h6 class="fw-bold text-success mb-3 mt-4"><i class="fas fa-boxes-stacked me-1"></i>Chi ti?t s?n ph?m & t?n kho hi?n t?i</h6>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Mã SP</th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Tên s?n ph?m</th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">ÐVT</th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-end">S? lu?ng ÐC</th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-end">T?n kho xu?t</th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Tr?ng thái</th>
                            </tr>
                        </thead>
                        <tbody id="tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="warning-box shadow-sm" id="warnBox">
            <div class="d-flex align-items-start gap-3">
                <i class="fas fa-exclamation-triangle text-warning fs-4 mt-1"></i>
                <div>
                    <strong>C?nh báo quan tr?ng!</strong>
                    <p class="mb-0 mt-1 text-muted">Hành d?ng này s? <strong>c?p nh?t t?n kho vinh vi?n</strong>: kho xu?t gi?m, kho nh?p tang s? lu?ng tuong ?ng. Hãy ki?m tra k? t?n kho tru?c khi xác nh?n.</p>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 justify-content-center mb-5" id="actionBar">
            <a href="index.php?page=phieu-dieuchuyen-danh-sach" class="btn btn-secondary px-4 shadow-sm">H?y b?</a>
            <button class="btn btn-success fw-bold px-5 shadow-sm" id="btnConfirm" onclick="execute()">
                <i class="fas fa-check me-2"></i>Xác nh?n th?c hi?n
            </button>
        </div>
    </div>
</div>

<script>
    const API = '/api_gateway.php';
    const headers = { 'Authorization': 'Bearer ' + localStorage.getItem('token') };
    const id = new URLSearchParams(location.search).get('id');
    let transferData = null;

    function fmtDate(s) { if (!s) return '—'; return new Date(s).toLocaleDateString('vi-VN'); }
    function showAlert(msg, type = 'success') {
        const a = document.getElementById('alertBox');
        a.className = `alert alert-${type} shadow-sm`;
        a.innerHTML = msg;
        a.classList.remove('d-none');
    }

    async function load() {
        if (!id) {
            // ÐÃ S?A: Ðu?ng d?n quay l?i danh sách
            showAlert('Không có mã phi?u trong URL. <a href="index.php?page=phieu-dieuchuyen-danh-sach" class="fw-bold">Quay l?i danh sách</a>', 'danger');
            return;
        }
        try {
            const res = await fetch(API + '/transfers/' + encodeURIComponent(id), { headers });
            const data = await res.json();
            if (!data.success) throw new Error(data.message);
            const r = data.data.transfer;
            transferData = r;
            document.getElementById('subTitle').textContent = 'Phi?u: ' + r.Madieuchuyen;
            // ÐÃ S?A: Ðu?ng d?n nút Back trên Header
            document.getElementById('backBtn').href = 'index.php?page=phieu-dieuchuyen-chi-tiet&id=' + encodeURIComponent(id);

            if (r.Trangthai === 'da_thuc_hien') {
                // ÐÃ S?A: Ðu?ng d?n quay l?i danh sách
                showAlert('<strong>Phi?u này dã du?c th?c hi?n r?i.</strong> T?n kho dã du?c c?p nh?t tru?c dó. <a href="index.php?page=phieu-dieuchuyen-danh-sach" class="fw-bold">Quay l?i danh sách</a>', 'warning');
                return;
            }

            document.getElementById('arrowFlow').innerHTML = `
                <div class="kho-chip"><i class="fas fa-store-alt me-1"></i>${r.TenKhoXuat || r.Khoxuat}</div>
                <div class="text-center flex-grow-1"><i class="fas fa-long-arrow-alt-right fa-2x text-success"></i><div class="small text-muted">Ði?u chuy?n</div></div>
                <div class="kho-chip"><i class="fas fa-store me-1"></i>${r.TenKhoNhap || r.Khonhap}</div>`;

            document.getElementById('infoGrid').innerHTML = `
                <div class="info-item"><div class="info-label">Mã phi?u</div><div class="info-value text-primary">${r.Madieuchuyen}</div></div>
                <div class="info-item"><div class="info-label">Ngày di?u chuy?n</div><div class="info-value">${fmtDate(r.Ngaydieuchuyen)}</div></div>
                <div class="info-item"><div class="info-label">Kho xu?t</div><div class="info-value">${r.TenKhoXuat || r.Khoxuat}</div></div>
                <div class="info-item"><div class="info-label">Kho nh?p</div><div class="info-value">${r.TenKhoNhap || r.Khonhap}</div></div>
                ${r.Ghichu ? `<div class="info-item" style="grid-column:1/-1"><div class="info-label">Ghi chú</div><div class="info-value text-muted">${r.Ghichu}</div></div>` : ''}`;

            const details = r.details || [];
            let allOk = true;
            document.getElementById('tbody').innerHTML = details.map(d => {
                const ok = (d.TonKhoXuat === undefined) ? true : (d.TonKhoXuat >= d.Soluong);
                if (!ok) allOk = false;
                return `<tr>
                    <td><span class="badge bg-light text-dark border">${d.Masp}</span></td>
                    <td class="fw-bold">${d.Tensp || '—'}</td>
                    <td>${d.Dvt || '—'}</td>
                    <td class="text-end fw-bold text-primary">${Number(d.Soluong || 0).toLocaleString('vi-VN')}</td>
                    <td class="text-end ${ok ? 'ton-ok' : 'ton-low'}">${d.TonKhoXuat !== undefined ? Number(d.TonKhoXuat).toLocaleString('vi-VN') : '—'}</td>
                    <td class="text-center">${ok ? '<span class="badge bg-success"><i class="fas fa-check me-1"></i> Ð?</span>' : '<span class="badge bg-danger"><i class="fas fa-times me-1"></i> Thi?u</span>'}</td>
                </tr>`;
            }).join('');

            if (!allOk) {
                document.getElementById('btnConfirm').disabled = true;
                showAlert('M?t s? s?n ph?m không d? t?n kho. Không th? th?c hi?n di?u chuy?n.', 'danger');
            }
            document.getElementById('mainContent').classList.remove('d-none');
        } catch (e) {
            showAlert('L?i t?i d? li?u: ' + e.message, 'danger');
        }
    }

    async function execute() {
        if (!confirm('Xác nh?n th?c hi?n di?u chuy?n?\nT?n kho ' + (transferData?.TenKhoXuat || 'kho xu?t') + ' s? gi?m và ' + (transferData?.TenKhoNhap || 'kho nh?p') + ' s? tang.')) return;
        
        const btn = document.getElementById('btnConfirm');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ðang x? lý...';
        
        try {
            const res = await fetch(API + '/transfers/' + encodeURIComponent(id) + '/execute', { 
                method: 'POST', 
                headers: { ...headers, 'Content-Type': 'application/json' } 
            });
            const data = await res.json();
            
            if (data.success) {
                document.getElementById('actionBar').innerHTML = '';
                document.getElementById('warnBox').classList.add('d-none');
                // ÐÃ S?A: Ðu?ng d?n quay l?i danh sách sau khi th?c hi?n thành công
                showAlert(`<strong><i class="fas fa-check-circle me-1"></i>Ði?u chuy?n hoàn thành!</strong> T?n kho hai kho dã du?c c?p nh?t. <a href="index.php?page=phieu-dieuchuyen-danh-sach" class="btn btn-success btn-sm ms-3 shadow-sm"><i class="fas fa-arrow-left me-1"></i> V? danh sách</a>`);
            } else {
                showAlert(data.message || 'Có l?i x?y ra', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Xác nh?n th?c hi?n';
            }
        } catch (e) {
            showAlert('L?i k?t n?i server', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-2"></i>Xác nh?n th?c hi?n';
        }
    }

    // Kh?i ch?y khi t?i trang
    document.addEventListener("DOMContentLoaded", load);
</script>
