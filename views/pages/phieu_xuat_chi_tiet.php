<!-- views/pages/phieu_xuat_chi_tiet.php -->
<div class="page-header d-flex justify-content-between align-items-center mb-4 p-4 rounded-3 shadow-sm bg-gradient-export">
    <div>
        <h1 class="mb-1 text-white fw-bold fs-3"><i class="fas fa-file-invoice me-2"></i>Chi tiết phiếu xuất kho</h1>
        <p class="mb-0 text-white opacity-75">Thông tin chi tiết về các mặt hàng đã xuất</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-light fw-bold text-dark border-0 shadow-sm"><i class="fas fa-print me-2"></i>In phiếu</button>
        <a href="phieu-xuat-danh-sach" class="btn btn-outline-light fw-bold border-1 shadow-sm"><i class="fas fa-arrow-left me-2"></i>Danh sách</a>
    </div>
</div>

<div id="detailLoading" class="text-center py-5">
    <div class="spinner-grow text-warning" role="status"></div>
    <div class="mt-2 text-muted">Đang tải thông tin phiếu xuất...</div>
</div>

<div id="detailContent" class="d-none">
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-header bg-white border-bottom p-3">
                    <h5 class="mb-0 fw-bold text-export"><i class="fas fa-list-ul me-2"></i>Danh sách sản phẩm xuất</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 py-3 text-muted small fw-bold">STT</th>
                                    <th class="py-3 text-muted small fw-bold">Sản phẩm</th>
                                    <th class="py-3 text-muted small fw-bold text-center">Số lượng</th>
                                    <th class="py-3 text-muted small fw-bold text-end">Đơn giá</th>
                                    <th class="pe-3 py-3 text-muted small fw-bold text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody id="detailItemsTable">
                                <!-- Loaded via JS -->
                            </tbody>
                            <tfoot>
                                <tr class="bg-light bg-opacity-50">
                                    <td colspan="4" class="text-end fw-bold py-3">TỔNG CỘNG:</td>
                                    <td class="text-end pe-3 fw-bold fs-5 text-success py-3" id="totalAmount">0 đ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4 rounded-3">
                <div class="card-header bg-white border-bottom p-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-info-circle me-2"></i>Thông tin chung</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex border-0 px-0 py-2">
                            <span class="text-muted small w-50">Mã phiếu:</span>
                            <span class="fw-bold text-export" id="receiptId">—</span>
                        </li>
                        <li class="list-group-item d-flex border-0 px-0 py-2">
                            <span class="text-muted small w-50">Ngày xuất:</span>
                            <span class="fw-bold" id="receiptDate">—</span>
                        </li>
                        <li class="list-group-item d-flex border-0 px-0 py-2">
                            <span class="text-muted small w-50">Khách hàng:</span>
                            <span class="fw-bold" id="customerName">—</span>
                        </li>
                        <li class="list-group-item d-flex border-0 px-0 py-2">
                            <span class="text-muted small w-50">Kho xuất:</span>
                            <span class="fw-bold" id="warehouseName">—</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom p-3 text-muted small fw-bold">Ghi chú</div>
                <div class="card-body bg-light bg-opacity-50 min-vh-10" id="receiptNote">
                    —
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-export { background: linear-gradient(135deg, #7c2d12 0%, #ea580c 100%); }
    .text-export { color: #ea580c; }
    @media print {
        .page-header, .btn, .main-content > div:not(#detailContent), .sidebar { display: none !important; }
        #detailContent { display: block !important; margin: 0; padding: 0; width: 100%; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
    }
</style>

<script>
    const API_DETAIL = '/vlxd/api_gateway.php';
    const detailHeaders = { 'Authorization': 'Bearer ' + localStorage.getItem('token') };

    async function loadExportDetail() {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');
        
        if (!id) {
            alert('Không tìm thấy mã phiếu xuất.');
            window.location.href = 'phieu-xuat-danh-sach';
            return;
        }

        try {
            const res = await fetch(`${API_DETAIL}/export-receipts/${id}`, { headers: detailHeaders });
            const result = await res.json();
            
            if (!result.success) throw new Error(result.message);
            
            const r = result.data.receipt;
            
            // Fill header info
            document.getElementById('receiptId').textContent = r.Maxuathang;
            document.getElementById('receiptDate').textContent = new Date(r.Ngayxuat).toLocaleDateString('vi-VN');
            document.getElementById('customerName').textContent = r.Tenkh || r.Makh || '—';
            document.getElementById('warehouseName').textContent = r.Tenkho || r.Makho || '—';
            document.getElementById('receiptNote').textContent = r.Ghichu || '—';
            document.getElementById('totalAmount').textContent = Number(r.Tongtienxuat || 0).toLocaleString('vi-VN') + ' đ';

            // Fill items table
            const tbody = document.getElementById('detailItemsTable');
            tbody.innerHTML = r.details.map((d, index) => `
                <tr>
                    <td class="ps-3 text-muted small">${index + 1}</td>
                    <td>
                        <div class="fw-bold">${d.Tensp || '—'}</div>
                        <div class="small text-muted font-monospace">${d.Masp}</div>
                    </td>
                    <td class="text-center fw-bold text-dark">${Number(d.Soluong).toLocaleString('vi-VN')} <small class="text-muted fw-normal">${d.Dvt || ''}</small></td>
                    <td class="text-end">${Number(d.Dongiaxuat).toLocaleString('vi-VN')} đ</td>
                    <td class="pe-3 text-end fw-bold">${(d.Soluong * d.Dongiaxuat).toLocaleString('vi-VN')} đ</td>
                </tr>
            `).join('');

            // Show content
            document.getElementById('detailLoading').classList.add('d-none');
            document.getElementById('detailContent').classList.remove('d-none');
            
        } catch (e) {
            document.getElementById('detailLoading').innerHTML = `<div class="alert alert-danger mx-5 shadow-sm border-0"><i class="fas fa-exclamation-triangle me-2"></i>Lỗi: ${e.message}</div>`;
        }
    }

    document.addEventListener('DOMContentLoaded', loadExportDetail);
</script>
