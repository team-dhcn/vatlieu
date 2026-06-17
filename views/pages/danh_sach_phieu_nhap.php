<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1 text-dark" style="font-size:1.6rem"><i class="fas fa-file-import me-2 text-primary"></i>Danh sách phiếu nhập kho</h1>
        <p class="text-muted mb-0">Quản lý và theo dõi các phiếu nhập nguyên vật liệu</p>
    </div>
    <a href="phieu-nhap-tao" class="btn btn-primary fw-bold shadow-sm px-4">
        <i class="fas fa-plus me-2"></i>Tạo phiếu nhập
    </a>
</div>

<div class="card mb-4">
    <div class="card-body p-3">
        <div class="row g-2">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input class="form-control border-start-0" id="searchInput" placeholder="Tìm theo mã phiếu, NCC, kho..." oninput="filterTable()">
                </div>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-secondary" onclick="loadReceipts()"><i class="fas fa-sync-alt"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Mã phiếu</th>
                        <th>Nhà cung cấp</th>
                        <th>Kho nhập</th>
                        <th>Ngày nhập</th>
                        <th class="text-center">Số mặt hàng</th>
                        <th class="text-end">Tổng tiền</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <tr><td colspan="7" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .stat-badge { background: #eff6ff; color: #1d4ed8; padding: 2px 10px; border-radius: 20px; font-size: .75rem; font-weight: 600; }
    .money-text { color: #15803d; font-weight: 700; }
    code { font-size: 0.9rem; }
</style>

<script>
async function loadReceipts() {
    const headers = getHeaders();
    if (!headers) return;
    try {
        const res = await fetch(API + '/import-receipts', { headers });
        const data = await res.json();
        const tb = document.getElementById('tbody');
        if (!data.success) throw new Error(data.message);
        
        const rows = data.data.receipts || [];
        if (!rows.length) {
            tb.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">Chưa có phiếu nhập nào.</td></tr>';
            return;
        }

        tb.innerHTML = rows.map(r => `
            <tr>
                <td class="ps-4"><code class="fw-bold text-primary">${r.Manhaphang}</code></td>
                <td class="fw-bold text-dark">${r.Tenncc || r.Mancc || '—'}</td>
                <td><small class="text-muted"><i class="fas fa-warehouse me-1"></i> ${r.Tenkho || r.Makho || '—'}</small></td>
                <td>${fmtDate(r.Ngaynhaphang)}</td>
                <td class="text-center"><span class="stat-badge">${r.SoMatHang || 0}</span></td>
                <td class="text-end money-text">${fmtMoney(r.Tongtiennhap)}</td>
                <td class="text-center pe-4">
                    <a href="phieu-nhap-chi-tiet?id=${encodeURIComponent(r.Manhaphang)}" class="btn btn-sm btn-outline-primary btn-action" title="Chi tiết"><i class="fas fa-eye"></i></a>
                    <button class="btn btn-sm btn-outline-danger btn-action" onclick="deleteReceipt('${r.Manhaphang}')" title="Xóa"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        document.getElementById('tbody').innerHTML = `<tr><td colspan="7" class="text-center text-danger py-5">Lỗi: ${e.message}</td></tr>`;
    }
}

function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

function fmtDate(s) { 
    if (!s) return '—'; 
    const d = new Date(s); 
    return d.toLocaleDateString('vi-VN'); 
}

function fmtMoney(n) { 
    return Number(n || 0).toLocaleString('vi-VN') + ' đ'; 
}

async function deleteReceipt(id) {
    if (!confirm('Xác nhận xóa phiếu nhập ' + id + '?\nHành động này sẽ cập nhật lại tồn kho.')) return;
    const headers = getHeaders();
    if (!headers) return;
    try {
        const res = await fetch(API + '/import-receipts/' + id, { method: 'DELETE', headers });
        const data = await res.json();
        if (data.success) { 
            showAlert('Đã xóa phiếu nhập thành công'); 
            loadReceipts(); 
        } else alert(data.message);
    } catch (e) { alert('Lỗi kết nối máy chủ'); }
}

document.addEventListener('DOMContentLoaded', loadReceipts);
</script>
