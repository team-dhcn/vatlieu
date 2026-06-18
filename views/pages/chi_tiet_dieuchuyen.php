<style>
    /* Ch? gi? l?i các CSS d?c thù cho component này */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
    .info-item { background: #f0fdf4; border-radius: 8px; padding: 12px 16px; border: 1px solid #bbf7d0; }
    .info-label { font-size: 0.78rem; color: #047857; text-transform: uppercase; font-weight: 700; margin-bottom: 2px; }
    .info-value { font-size: 1rem; font-weight: 600; color: #1e293b; }
    .arrow-box { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 2px solid #86efac; border-radius: 10px; padding: 14px 20px; display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
    .kho-badge { background: white; border: 1px solid #bbf7d0; border-radius: 8px; padding: 8px 14px; font-weight: 700; font-size: 0.9rem; color: #047857; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 p-4 text-white rounded shadow-sm" style="background: linear-gradient(135deg, #0f766e, #14b8a6);">
        <div>
            <h1 class="h3 mb-1 fw-bold"><i class="fas fa-exchange-alt me-2"></i>Chi ti?t phi?u di?u chuy?n</h1>
            <p class="mb-0 opacity-75" id="subTitle">Ðang t?i...</p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?page=phieu-dieuchuyen-danh-sach" class="btn btn-light btn-sm fw-bold">
                <i class="fas fa-arrow-left me-1"></i>Danh sách
            </a>
            <button class="btn btn-warning btn-sm fw-bold shadow-sm text-dark" id="btnThucHien" disabled onclick="goToExecutePage()">
                <i class="fas fa-play me-1"></i>Th?c hi?n di?u chuy?n
            </button>
        </div>
    </div>

    <div id="alertBox" class="alert d-none mb-3 shadow-sm"></div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="arrow-box shadow-sm" id="arrowBox">
                <span class="text-muted small">Ðang t?i...</span>
            </div>
            
            <div class="info-grid" id="infoGrid"></div>
            
            <h6 class="fw-bold text-success mb-3 mt-4"><i class="fas fa-boxes-stacked me-1"></i>S?n ph?m di?u chuy?n</h6>
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Mã SP</th>
                            <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Tên s?n ph?m</th>
                            <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">ÐVT</th>
                            <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-end">S? lu?ng</th>
                        </tr>
                    </thead>
                    <tbody id="tbody">
                        <tr><td colspan="4" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Ðang t?i...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const API = '/api_gateway.php';
    const headers = { 'Authorization': 'Bearer ' + localStorage.getItem('token') };
    const id = new URLSearchParams(location.search).get('id');

    function fmtDate(s) { if (!s) return '—'; return new Date(s).toLocaleDateString('vi-VN'); }
    function showAlert(msg, type = 'success') {
        const a = document.getElementById('alertBox');
        a.className = `alert alert-${type} shadow-sm`;
        a.innerHTML = msg;
        a.classList.remove('d-none');
    }

    async function load() {
        if (!id) { 
            // ÐÃ S?A: Link quay l?i danh sách
            showAlert('Không có mã phi?u di?u chuy?n. <a href="index.php?page=phieu-dieuchuyen-danh-sach" class="fw-bold">V? danh sách</a>', 'danger'); 
            return; 
        }
        try {
            const res = await fetch(API + '/transfers/' + encodeURIComponent(id), { headers });
            const data = await res.json();
            if (!data.success) throw new Error(data.message);
            
            const r = data.data.transfer;
            document.getElementById('subTitle').textContent = 'Phi?u: ' + r.Madieuchuyen;
            
            document.getElementById('arrowBox').innerHTML = `
                <div class="kho-badge shadow-sm"><i class="fas fa-store-alt me-1"></i>${r.TenKhoXuat || r.Khoxuat}</div>
                <div class="text-center flex-grow-1"><i class="fas fa-arrow-right fa-2x text-success"></i><div class="small text-muted mt-1 fw-bold">${fmtDate(r.Ngaydieuchuyen)}</div></div>
                <div class="kho-badge shadow-sm"><i class="fas fa-store me-1"></i>${r.TenKhoNhap || r.Khonhap}</div>`;
                
            document.getElementById('infoGrid').innerHTML = `
                <div class="info-item shadow-sm"><div class="info-label">Mã phi?u</div><div class="info-value text-primary">${r.Madieuchuyen}</div></div>
                <div class="info-item shadow-sm"><div class="info-label">Ngày di?u chuy?n</div><div class="info-value">${fmtDate(r.Ngaydieuchuyen)}</div></div>
                <div class="info-item shadow-sm"><div class="info-label">Kho xu?t</div><div class="info-value">${r.TenKhoXuat || r.Khoxuat}</div></div>
                <div class="info-item shadow-sm"><div class="info-label">Kho nh?p</div><div class="info-value">${r.TenKhoNhap || r.Khonhap}</div></div>
                <div class="info-item shadow-sm" style="grid-column:1/-1"><div class="info-label">Ghi chú</div><div class="info-value text-muted">${r.Ghichu || '—'}</div></div>`;
                
            const details = r.details || [];
            if (!details.length) { 
                document.getElementById('tbody').innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Không có chi ti?t s?n ph?m</td></tr>'; 
                return; 
            }
            
            document.getElementById('tbody').innerHTML = details.map(d => `<tr>
                <td><span class="badge bg-light text-dark border">${d.Masp}</span></td>
                <td class="fw-bold">${d.Tensp || '—'}</td>
                <td>${d.Dvt || '—'}</td>
                <td class="text-end fw-bold text-primary">${Number(d.Soluong || 0).toLocaleString('vi-VN')}</td>
            </tr>`).join('');

            // X? lý nút Th?c hi?n
            const btn = document.getElementById('btnThucHien');
            if (r.Trangthai !== 'da_thuc_hien') {
                btn.disabled = false;
            } else {
                btn.textContent = 'Ðã th?c hi?n xong';
                btn.className = 'btn btn-sm btn-secondary fw-bold shadow-sm';
            }
        } catch (e) { 
            showAlert('L?i: ' + e.message, 'danger'); 
        }
    }

    // ÐÃ S?A: Logic chuy?n hu?ng sang trang "Th?c hi?n"
    function goToExecutePage() {
        if (!id) return;
        window.location.href = 'index.php?page=phieu-dieuchuyen-thuc-hien&id=' + encodeURIComponent(id);
    }

    // Kh?i ch?y
    document.addEventListener("DOMContentLoaded", load);
</script>
