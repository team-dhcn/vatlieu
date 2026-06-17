<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Quản lý Loại khách hàng</h2>
        <p class="text-muted mb-0">Thêm, sửa, xóa các loại phân nhóm khách hàng</p>
    </div>
    <button class="btn btn-primary fw-bold px-4 shadow-sm" onclick="openLKHModal()">
        <i class="fas fa-plus me-2"></i>Thêm loại KH
    </button>
</div>

<div id="lkhAlertBox" class="alert d-none mb-3"></div>

<div class="card mb-4">
    <div class="card-body p-3">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input class="form-control border-start-0" id="lkhSearchInput" placeholder="Tìm kiếm theo tên, mô tả..." oninput="filterLKHTable()">
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Mã loại</th>
                        <th>Tên loại</th>
                        <th>Mô tả</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="lkhTbody"><tr><td colspan="4" class="text-center py-5 text-muted">Đang tải dữ liệu...</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Loại KH -->
<div class="modal fade" id="lkhModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius:12px">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="lkhModalTitle">Thêm loại KH</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mã loại</label>
                    <input class="form-control" id="lkhFMa" placeholder="Hệ thống tự động tạo mã" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên loại *</label>
                    <select class="form-select" id="lkhFTen" required>
                        <option value="">-- Chọn loại khách hàng --</option>
                        <option value="Khách lẻ">Khách lẻ</option>
                        <option value="Khách sỉ">Khách sỉ</option>
                        <option value="Đại lý cấp 1">Đại lý cấp 1</option>
                        <option value="Đại lý cấp 2">Đại lý cấp 2</option>
                        <option value="Đại lý cấp 3">Đại lý cấp 3</option>
                        <option value="Nhà thầu/Dự án">Nhà thầu/Dự án</option>
                        <option value="Nhà phân phối">Nhà phân phối</option>
                        <option value="VIP">VIP</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả</label>
                    <textarea class="form-control" id="lkhFMota" rows="2" placeholder="Mô tả (tuỳ chọn)"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-primary px-4 fw-bold" onclick="saveLKH()">
                    <i class="fas fa-save me-1"></i>Lưu
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let lkhEditId = null;
let lkhModal;

document.addEventListener('DOMContentLoaded', () => {
    lkhModal = new bootstrap.Modal(document.getElementById('lkhModal'));
    loadLKH();
});

function showLKHAlert(msg, type='success') {
    const a = document.getElementById('lkhAlertBox');
    a.className = `alert alert-${type}`;
    a.textContent = msg;
    a.classList.remove('d-none');
    setTimeout(() => a.classList.add('d-none'), 4000);
}

function filterLKHTable() {
    const q = document.getElementById('lkhSearchInput').value.toLowerCase();
    document.querySelectorAll('#lkhTbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

async function loadLKH() {
    const headers = getHeaders();
    if (!headers) return;
    try {
        const res = await fetch(API + '/customer-types', { headers });
        const data = await res.json();
        const tb = document.getElementById('lkhTbody');
        if (!data.success) throw new Error(data.message);
        if (!data.data.types.length) {
            tb.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Chưa có loại khách hàng nào.</td></tr>';
            return;
        }
        tb.innerHTML = data.data.types.map(r => `<tr>
            <td class="ps-4"><code class="fw-bold text-primary">${r.Maloaikh}</code></td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">${r.Tenloaikh}</span></td>
            <td class="text-muted"><small>${r.Mota || '—'}</small></td>
            <td class="text-center pe-4">
                <button class="btn btn-outline-primary btn-action me-1" onclick="openLKHModal('${r.Maloaikh}','${r.Tenloaikh}','${(r.Mota||'').replace(/'/g,"\\'")}')"><i class="fas fa-edit"></i></button>
                <button class="btn btn-outline-danger btn-action" onclick="deleteLKH('${r.Maloaikh}')"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`).join('');
    } catch(e) {
        document.getElementById('lkhTbody').innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">Lỗi: ${e.message}</td></tr>`;
    }
}

function openLKHModal(id=null, ten='', mota='') {
    lkhEditId = id;
    document.getElementById('lkhModalTitle').textContent = id ? 'Sửa loại KH' : 'Thêm loại KH';
    document.getElementById('lkhFMa').value = id || '';
    document.getElementById('lkhFTen').value = ten;
    document.getElementById('lkhFMota').value = mota;
    lkhModal.show();
}

async function saveLKH() {
    const headers = getHeaders();
    if (!headers) return;
    const isEdit = lkhEditId !== null;
    const body = {
        Maloaikh: document.getElementById('lkhFMa').value,
        Tenloaikh: document.getElementById('lkhFTen').value,
        Mota: document.getElementById('lkhFMota').value
    };
    if (!body.Tenloaikh) return alert('Vui lòng chọn tên loại khách hàng!');

    try {
        const url = isEdit ? `${API}/customer-types/${lkhEditId}` : `${API}/customer-types`;
        const method = isEdit ? 'PUT' : 'POST';
        const res = await fetch(url, { method, headers: {...headers, 'Content-Type': 'application/json'}, body: JSON.stringify(body) });
        const data = await res.json();
        if (data.success) {
            showLKHAlert(isEdit ? 'Cập nhật thành công!' : 'Thêm mới thành công!');
            lkhModal.hide();
            loadLKH();
        } else {
            alert('Lỗi: ' + data.message);
        }
    } catch(e) { alert('Lỗi kết nối API'); }
}

async function deleteLKH(id) {
    if (!confirm(`Xác nhận xóa loại khách hàng #${id}?`)) return;
    const headers = getHeaders();
    if (!headers) return;
    try {
        const res = await fetch(`${API}/customer-types/${id}`, { method: 'DELETE', headers });
        const data = await res.json();
        if (data.success) { showLKHAlert('Đã xóa loại KH', 'warning'); loadLKH(); }
        else alert(data.message);
    } catch(e) { alert('Lỗi kết nối'); }
}
</script>
