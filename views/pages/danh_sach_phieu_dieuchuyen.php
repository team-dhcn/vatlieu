<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0 text-gray-800">Danh sách phiếu điều chuyển</h2>
            <p class="text-muted small mt-1">Quản lý các phiếu điều chuyển kho</p>
        </div>
        <div>
            <a href="/vlxd/phieu-dieuchuyen-tao" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tạo phiếu điều chuyển
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 text-sm">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-4 py-3">Mã phiếu</th>
                            <th class="px-4 py-3">Kho xuất</th>
                            <th class="px-4 py-3">Kho nhập</th>
                            <th class="px-4 py-3">Ngày điều chuyển</th>
                            <th class="px-4 py-3">Ghi chú</th>
                            <th class="px-4 py-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="transferList">
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-muted">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div> Đang tải danh sách...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    async function loadTransfers() {
        try {
            const token = localStorage.getItem('token');
            const res = await fetch('/vlxd/api_gateway.php/transfers', {
                headers: {'Authorization': 'Bearer ' + token}
            });
            const data = await res.json();
            const tbody = document.getElementById('transferList');
            tbody.innerHTML = '';
            
            if(data.success && data.data.transfers.length > 0) {
                data.data.transfers.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-4 py-3 fw-bold text-primary">${p.Madieuchuyen}</td>
                        <td class="px-4 py-3">${p.TenKhoxuat}</td>
                        <td class="px-4 py-3">${p.TenKhonhap}</td>
                        <td class="px-4 py-3">${p.Ngaydieuchuyen}</td>
                        <td class="px-4 py-3">${p.Ghichu || ''}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="/vlxd/phieu-dieuchuyen-chi-tiet?id=${p.Madieuchuyen}" class="btn btn-info btn-sm btn-action me-1" title="Chi tiết"><i class="fas fa-eye text-white"></i></a>
                            <a href="javascript:void(0)" onclick="deleteTransfer('${p.Madieuchuyen}')" class="btn btn-danger btn-sm btn-action" title="Xóa"><i class="fas fa-trash"></i></a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-5 text-center text-muted">Không có dữ liệu điều chuyển</td></tr>';
            }
        } catch(e) {
            document.getElementById('transferList').innerHTML = '<tr><td colspan="6" class="py-5 text-center text-danger">Lỗi kết nối API Điều chuyển: ' + e.message + '</td></tr>';
        }
    }

    async function deleteTransfer(id) {
        if(!confirm('Bạn có chắc muốn xóa phiếu điều chuyển: ' + id + '? Thao tác này KHÔNG hoàn trả lại kho!')) return;
        try {
            const token = localStorage.getItem('token');
            const res = await fetch('/vlxd/api_gateway.php/transfers/' + id, { 
                method: 'DELETE',
                headers: {'Authorization': 'Bearer ' + token}
            });
            const data = await res.json();
            if(data.success) {
                if (typeof showAlert === 'function') showAlert('Đã xóa phiếu điều chuyển', 'success');
                else alert('Đã xóa phiếu điều chuyển');
                loadTransfers();
            } else {
                if (typeof showAlert === 'function') showAlert('Lỗi: ' + data.message, 'danger');
                else alert('Lỗi: ' + data.message);
            }
        } catch(e) { 
            alert('Lỗi máy chủ khi xóa.'); 
        }
    }

    document.addEventListener("DOMContentLoaded", loadTransfers);
</script>