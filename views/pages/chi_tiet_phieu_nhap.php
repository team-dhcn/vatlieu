<?php
$id = $_GET['id'] ?? null;
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1 text-dark" style="font-size:1.6rem"><i class="fas fa-eye me-2 text-primary"></i>Chi tiết
            phiếu nhập kho</h1>
        <p class="text-muted mb-0">Xem thông tin chi tiết và danh sách mặt hàng của phiếu nhập</p>
    </div>
    <a href="phieu-nhap-danh-sach" class="btn btn-outline-secondary fw-bold shadow-sm px-4">
        <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
    </a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h6 class="fw-bold mb-0">Thông tin chung</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold">Mã phiếu:</label>
                    <div class="fw-bold fs-5 text-primary" id="dMa">--</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold">Nhà cung cấp:</label>
                    <div class="fw-bold text-dark" id="dNcc">--</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold">Kho nhập hàng:</label>
                    <div class="fw-bold text-dark text-truncate" id="dKho">--</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold">Ngày lập phiếu:</label>
                    <div class="text-dark" id="dNgay">--</div>
                </div>
                <div class="mb-0">
                    <label class="text-muted small text-uppercase fw-bold">Ghi chú:</label>
                    <p class="text-muted italic mb-0" id="dGhichu">Không có ghi chú.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">Danh sách nguyên vật liệu nhập</h6>
                <span class="badge bg-primary rounded-pill px-3" id="dCount">0 mặt hàng</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Mã NVL</th>
                                <th>Tên hàng</th>
                                <th class="text-center">S.Lượng</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end pe-4">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="detailList">
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Đang tải chi tiết...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light py-4 px-4 border-top-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">TỔNG CỘNG:</h5>
                <h3 class="fw-bold text-success mb-0" id="dTotal">0 đ</h3>
            </div>
        </div>
    </div>
</div>

<script>
    const receiptId = '<?= htmlspecialchars($id) ?>';

    async function loadDetail() {
        if (!receiptId) return alert('Không tìm thấy mã phiếu!');
        const headers = getHeaders();
        if (!headers) return;
        try {
            const res = await fetch(API + '/import-receipts/' + receiptId, { headers });
            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            const r = data.data.receipt;
            const details = r.details || [];

            // Fill Master
            document.getElementById('dMa').textContent = r.Manhaphang;
            document.getElementById('dNcc').textContent = r.Tenncc || r.Mancc;
            document.getElementById('dKho').textContent = `[${r.Makho}] ${r.Tenkho}`;
            document.getElementById('dNgay').textContent = new Date(r.Ngaynhaphang).toLocaleDateString('vi-VN');
            document.getElementById('dGhichu').textContent = r.Ghichu || 'Không có ghi chú.';
            document.getElementById('dCount').textContent = details.length + ' mặt hàng';
            document.getElementById('dTotal').textContent = Number(r.Tongtiennhap || 0).toLocaleString('vi-VN') + ' đ';

            // Fill Details
            const tb = document.getElementById('detailList');
            if (!details.length) {
                tb.innerHTML = '<tr><td colspan="5" class="text-center py-5">Phiếu này không có chi tiết hàng hóa.</td></tr>';
                return;
            }
            tb.innerHTML = details.map(d => `
            <tr>
                <td class="ps-4"><code>${d.Manvl}</code></td>
                <td><span class="fw-bold text-dark">${d.Tennvl}</span><br><small class="text-muted">${d.Dvt}</small></td>
                <td class="text-center fw-bold">${Number(d.Soluong).toLocaleString('vi-VN')}</td>
                <td class="text-end text-muted">${Number(d.Dongianhap).toLocaleString('vi-VN')} đ</td>
                <td class="text-end fw-bold text-dark pe-4">${Number(d.Soluong * d.Dongianhap).toLocaleString('vi-VN')} đ</td>
            </tr>
        `).join('');
        } catch (e) {
            document.getElementById('detailList').innerHTML = `<tr><td colspan="5" class="text-center text-danger py-5">Lỗi: ${e.message}</td></tr>`;
        }
    }

    document.addEventListener('DOMContentLoaded', loadDetail);
</script>
