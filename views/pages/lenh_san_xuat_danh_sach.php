<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1 text-dark" style="font-size:1.6rem"><i class="fas fa-cogs me-2 text-primary"></i>Danh sách lệnh sản xuất</h1>
        <p class="text-muted mb-0">Theo dõi và quản lý quy trình sản xuất thành phẩm</p>
    </div>
    <a href="lenh-san-xuat-tao" class="btn btn-primary fw-bold shadow-sm px-4">
        <i class="fas fa-plus me-2"></i>Tạo lệnh sản xuất
    </a>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input class="form-control border-start-0" id="searchInput" placeholder="Tìm theo mã lệnh, sản phẩm..." oninput="filterTable()">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterStatus" onchange="load()">
                    <option value="">Tất cả trạng thái</option>
                    <option value="cho_xu_ly">Chờ xử lý</option>
                    <option value="dang_san_xuat">Đang sản xuất</option>
                    <option value="hoan_thanh">Hoàn thành</option>
                    <option value="huy">Đã hủy</option>
                </select>
            </div>
            <div class="col-auto ms-auto">
                <button class="btn btn-outline-secondary" onclick="load()"><i class="fas fa-sync-alt"></i></button>
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
                        <th class="ps-4">Mã lệnh</th>
                        <th>Sản phẩm</th>
                        <th>Ngày SX</th>
                        <th class="text-center">Số lượng SX</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <tr><td colspan="6" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Hoàn thành & Nhập kho -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-dark">Hoàn thành & Nhập kho</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-muted small mb-3">Nhập kho thành phẩm cho lệnh: <strong id="htMalenh" class="text-primary"></strong></p>
                <label class="form-label fw-bold small text-uppercase">Kho nhập hàng *</label>
                <select class="form-select shadow-none" id="htKho">
                    <option value="">-- Chọn kho --</option>
                </select>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success btn-sm px-3 fw-bold" onclick="confirmComplete()">
                    <i class="fas fa-check me-1"></i>Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .badge-status { padding: 4px 12px; border-radius: 20px; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
    .status-cho { background: #fef9c3; color: #854d0e; }
    .status-dang { background: #dbeafe; color: #1e40af; }
    .status-hoan { background: #dcfce7; color: #166534; }
    .status-huy { background: #fee2e2; color: #991b1b; }
    code { font-size: 0.9rem; color: #7c3aed; }
</style>

<script>
let currentMalenh = null;

async function load() {
    const headers = getHeaders();
    if (!headers) return;
    try {
        const res = await fetch(API + '/production-orders', { headers });
        const data = await res.json();
        const tb = document.getElementById('tbody');
        if (!data.success) throw new Error(data.message);
        
        let rows = data.data.orders || [];
        const flt = document.getElementById('filterStatus').value;
        if (flt) rows = rows.filter(r => r.Trangthai === flt);

        if (!rows.length) {
            tb.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">Không có lệnh sản xuất nào được tìm thấy.</td></tr>';
            return;
        }

        tb.innerHTML = rows.map(r => `
            <tr>
                <td class="ps-4"><code class="fw-bold">${r.Malenh}</code></td>
                <td>
                    <div class="fw-bold text-dark">${r.Tensp || r.Masp}</div>
                    <small class="text-muted text-uppercase" style="font-size:0.65rem">Mã SP: ${r.Masp}</small>
                </td>
                <td>${fmtDate(r.Ngaysanxuat)}</td>
                <td class="text-center fw-bold text-dark fs-6">${Number(r.Soluongsanxuat || 0).toLocaleString('vi-VN')}</td>
                <td class="text-center">${statusBadge(r.Trangthai)}</td>
                <td class="text-center pe-4">
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="lenh-san-xuat-chi-tiet?id=${r.Malenh}" class="btn btn-sm btn-outline-info" title="Xem chi tiết hồ sơ"><i class="fas fa-info-circle"></i></a>
                        
                        ${r.Trangthai !== 'hoan_thanh' ? `
                            <a href="lenh-san-xuat-sua?id=${r.Malenh}" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                        ` : ''}
                        
                        ${r.Trangthai !== 'hoan_thanh' && r.Trangthai !== 'huy' ? `
                            <button class="btn btn-sm btn-outline-success" onclick="openCompleteModal('${r.Malenh}')" title="Hoàn thành & Nhập kho">
                                <i class="fas fa-check-circle"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="updateStatus('${r.Malenh}', 'dang_san_xuat')" title="Đánh dấu đang SX">
                                <i class="fas fa-play"></i>
                            </button>
                        ` : ''}
                        
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteOrder('${r.Malenh}')" title="Xóa lệnh"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        document.getElementById('tbody').innerHTML = `<tr><td colspan="6" class="text-center text-danger py-5">Lỗi: ${e.message}</td></tr>`;
    }
}

function statusBadge(s) {
    const config = {
        cho_xu_ly: { class: 'status-cho', label: 'Chờ xử lý' },
        dang_san_xuat: { class: 'status-dang', label: 'Đang SX' },
        hoan_thanh: { class: 'status-hoan', label: 'Hoàn thành' },
        huy: { class: 'status-huy', label: 'Đã hủy' }
    };
    const c = config[s] || { class: 'bg-secondary text-white', label: s };
    return `<span class="badge-status ${c.class}">${c.label}</span>`;
}

function fmtDate(s) { 
    if (!s) return '—'; 
    return new Date(s).toLocaleDateString('vi-VN'); 
}

function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

async function updateStatus(id, status) {
    const headers = getHeaders();
    if (!headers) return;
    try {
        const res = await fetch(API + '/production-orders/' + id, {
            method: 'PUT',
            headers: { ...headers, 'Content-Type': 'application/json' },
            body: JSON.stringify({ Trangthai: status })
        });
        const data = await res.json();
        if (data.success) {
            showAlert('Đã cập nhật trạng thái lệnh: ' + id);
            load();
        } else showAlert(data.message, 'danger');
    } catch (e) { showAlert('Lỗi kết nối máy chủ', 'danger'); }
}

async function deleteOrder(id) {
    if (!confirm('Xác nhận xóa lệnh sản xuất ' + id + '?')) return;
    const headers = getHeaders();
    if (!headers) return;
    try {
        const res = await fetch(API + '/production-orders/' + id, { method: 'DELETE', headers });
        const data = await res.json();
        if (data.success) {
            showAlert('Đã xóa lệnh sản xuất thành công');
            load();
        } else showAlert(data.message, 'danger');
    } catch (e) { showAlert('Lỗi kết nối máy chủ', 'danger'); }
}

async function openCompleteModal(id) {
    currentMalenh = id;
    document.getElementById('htMalenh').textContent = id;
    const headers = getHeaders();
    if (!headers) return;
    
    // Load warehouses if not already loaded
    const sel = document.getElementById('htKho');
    if (sel.options.length <= 1) {
        try {
            const res = await fetch(API + '/warehouses', { headers });
            const data = await res.json();
            if (data.success) {
                data.data.warehouses.forEach(k => {
                    sel.innerHTML += `<option value="${k.Makho}">[${k.Makho}] ${k.Tenkho}</option>`;
                });
            }
        } catch (e) { console.error('Error loading warehouses'); }
    }
    
    new bootstrap.Modal(document.getElementById('completeModal')).show();
}

async function confirmComplete() {
    const makho = document.getElementById('htKho').value;
    if (!makho) return alert('Vui lòng chọn kho nhập thành phẩm!');
    
    const headers = getHeaders();
    if (!headers) return;
    
    try {
        const res = await fetch(API + '/complete-production', {
            method: 'POST',
            headers: { ...headers, 'Content-Type': 'application/json' },
            body: JSON.stringify({ Malenh: currentMalenh, Makho: makho })
        });
        const data = await res.json();
        if (data.success) {
            alert('Lệnh ' + currentMalenh + ' đã hoàn thành và nhập kho thành công!');
            bootstrap.Modal.getInstance(document.getElementById('completeModal')).hide();
            load();
        } else alert('Lỗi: ' + data.message);
    } catch (e) { alert('Lỗi kết nối API'); }
}

document.addEventListener('DOMContentLoaded', load);
</script>
