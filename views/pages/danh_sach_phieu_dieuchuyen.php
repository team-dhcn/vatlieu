<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0 text-gray-800">Danh sách phi?u di?u chuy?n</h2>
            <p class="text-muted small mt-1">Qu?n lý các phi?u di?u chuy?n kho</p>
        </div>
        <div>
            <a href="/phieu-dieuchuyen-tao" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> T?o phi?u di?u chuy?n
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 text-sm">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-4 py-3">Mã phi?u</th>
                            <th class="px-4 py-3">Kho xu?t</th>
                            <th class="px-4 py-3">Kho nh?p</th>
                            <th class="px-4 py-3">Ngày di?u chuy?n</th>
                            <th class="px-4 py-3">Ghi chú</th>
                            <th class="px-4 py-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="transferList">
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-muted">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div> Ðang t?i danh sách...
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
            const res = await fetch('/api_gateway.php/transfers', {
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
                            <a href="/phieu-dieuchuyen-chi-tiet?id=${p.Madieuchuyen}" class="btn btn-info btn-sm btn-action me-1" title="Chi ti?t"><i class="fas fa-eye text-white"></i></a>
                            <a href="javascript:void(0)" onclick="deleteTransfer('${p.Madieuchuyen}')" class="btn btn-danger btn-sm btn-action" title="Xóa"><i class="fas fa-trash"></i></a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-5 text-center text-muted">Không có d? li?u di?u chuy?n</td></tr>';
            }
        } catch(e) {
            document.getElementById('transferList').innerHTML = '<tr><td colspan="6" class="py-5 text-center text-danger">L?i k?t n?i API Ði?u chuy?n: ' + e.message + '</td></tr>';
        }
    }

    async function deleteTransfer(id) {
        if(!confirm('B?n có ch?c mu?n xóa phi?u di?u chuy?n: ' + id + '? Thao tác này KHÔNG hoàn tr? l?i kho!')) return;
        try {
            const token = localStorage.getItem('token');
            const res = await fetch('/api_gateway.php/transfers/' + id, { 
                method: 'DELETE',
                headers: {'Authorization': 'Bearer ' + token}
            });
            const data = await res.json();
            if(data.success) {
                if (typeof showAlert === 'function') showAlert('Ðã xóa phi?u di?u chuy?n', 'success');
                else alert('Ðã xóa phi?u di?u chuy?n');
                loadTransfers();
            } else {
                if (typeof showAlert === 'function') showAlert('L?i: ' + data.message, 'danger');
                else alert('L?i: ' + data.message);
            }
        } catch(e) { 
            alert('L?i máy ch? khi xóa.'); 
        }
    }

    document.addEventListener("DOMContentLoaded", loadTransfers);
</script>
