<!-- views/pages/phieu_xuat_danh_sach.php -->
<div class="page-header d-flex justify-content-between align-items-center mb-4 p-4 rounded-3 shadow-sm bg-gradient-export">
    <div>
        <h1 class="mb-1 text-white fw-bold fs-3"><i class="fas fa-file-export me-2"></i>Danh sách phiếu xuất kho</h1>
        <p class="mb-0 text-white opacity-75">Quản lý và theo dõi các phiếu xuất thành phẩm cho khách hàng</p>
    </div>
    <a href="phieu-xuat-tao" class="btn btn-light btn-lg fw-bold text-export shadow-sm"><i class="fas fa-plus-circle me-2"></i>Tạo phiếu mới</a>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="p-3 bg-light border-bottom d-flex gap-3 align-items-center">
            <div class="input-group input-group-sm" style="max-width: 400px;">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control border-start-0 ps-0" id="receiptSearch" placeholder="Tìm kiếm theo mã, khách hàng, kho..." oninput="filterReceipts()">
            </div>
            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="loadReceipts()"><i class="fas fa-sync-alt me-1"></i> Làm mới</button>
            <div class="ms-auto">
                <span class="badge bg-export-light text-export rounded-pill px-3 py-2" id="receiptCount">Đang tải...</span>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase small fw-bold text-muted" style="width: 15%">Mã phiếu</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted" style="width: 25%">Khách hàng</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted" style="width: 20%">Kho xuất</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted" style="width: 15%">Ngày xuất</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted text-center" style="width: 10%">Số SP</th>
                        <th class="py-3 text-uppercase small fw-bold text-muted text-end" style="width: 15%">Tổng tiền</th>
                        <th class="pe-4 py-3 text-uppercase small fw-bold text-muted text-center" style="width: 10%">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="receiptTableBody">
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                            <span class="text-muted">Đang tải danh sách phiếu xuất...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-gradient-export { background: linear-gradient(135deg, #7c2d12 0%, #ea580c 100%); }
    .text-export { color: #ea580c; }
    .bg-export-light { background-color: #ffedd5; }
    .stat-badge { background: #fef3c7; color: #92400e; padding: 0.2rem 0.6rem; border-radius: 50rem; font-size: 0.75rem; font-weight: 700; }
    .money-text { font-family: 'Inter', sans-serif; font-weight: 700; color: #059669; }
    .table-hover tbody tr:hover { background-color: #fffaf5; }
</style>

<script>
    const API_EXPORT = '/vlxd/api_gateway.php';
    const authHeaders = { 'Authorization': 'Bearer ' + localStorage.getItem('token') };

    async function loadReceipts() {
        const tbody = document.getElementById('receiptTableBody');
        const countBadge = document.getElementById('receiptCount');
        
        try {
            const response = await fetch(`${API_EXPORT}/export-receipts`, { headers: authHeaders });
            const result = await response.json();
            
            if (!result.success) throw new Error(result.message);
            
            const receipts = result.data.receipts;
            countBadge.textContent = `${receipts.length} phiếu xuất`;
            
            if (receipts.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-folder-open mb-2 fs-3 d-block"></i>Chưa có phiếu xuất hàng nào.</td></tr>`;
                return;
            }
            
            tbody.innerHTML = receipts.map(r => `
                <tr>
                    <td class="ps-4"><code class="fw-bold text-export fs-6">${r.Maxuathang}</code></td>
                    <td>
                        <div class="fw-bold">${r.Tenkh || '—'}</div>
                        <div class="small text-muted">${r.Makh || ''}</div>
                    </td>
                    <td>
                        <div class="small fw-semibold"><i class="fas fa-warehouse me-1 opacity-50"></i> ${r.Tenkho || '—'}</div>
                    </td>
                    <td>
                        <div class="small font-monospace">${new Date(r.Ngayxuat).toLocaleDateString('vi-VN')}</div>
                    </td>
                    <td class="text-center">
                        <span class="stat-badge">${r.SoMatHang || 0}</span>
                    </td>
                    <td class="text-end pe-3">
                        <span class="money-text">${Number(r.Tongtienxuat || 0).toLocaleString('vi-VN')} đ</span>
                    </td>
                    <td class="pe-4 text-center">
                        <div class="btn-group">
                            <a href="phieu-xuat-chi-tiet?id=${encodeURIComponent(r.Maxuathang)}" class="btn btn-sm btn-outline-info rounded-start-pill" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger rounded-end-pill" onclick="deleteReceipt('${r.Maxuathang}')" title="Xóa phiếu">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
            
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Lỗi: ${error.message}</td></tr>`;
            countBadge.textContent = 'Lỗi kết nối';
        }
    }

    function filterReceipts() {
        const query = document.getElementById('receiptSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#receiptTableBody tr');
        rows.forEach(row => {
            if (row.cells.length < 2) return;
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }

    async function deleteReceipt(id) {
        if (!confirm(`Bạn có chắc chắn muốn xóa phiếu xuất ${id}?\nHành động này sẽ hoàn lại tồn kho sản phẩm.`)) return;
        
        try {
            const res = await fetch(`${API_EXPORT}/export-receipts/${id}`, {
                method: 'DELETE',
                headers: authHeaders
            });
            const data = await res.json();
            if (data.success) {
                // Show toast or alert
                loadReceipts();
            } else {
                alert('Lỗi: ' + data.message);
            }
        } catch (e) {
            alert('Lỗi kết nối server khi xóa phiếu.');
        }
    }

    // Initial load
    document.addEventListener('DOMContentLoaded', loadReceipts);
</script>
