<?php
$id = $_GET['id'] ?? null;
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1 text-dark" style="font-size:1.6rem"><i class="fas fa-file-invoice me-2 text-primary"></i>Chi tiết lệnh sản xuất</h1>
        <p class="text-muted mb-0">Xem hồ sơ sản xuất và lịch sử tiêu hao vật tư</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary fw-bold shadow-sm px-4" onclick="window.print()">
            <i class="fas fa-print me-2"></i>In lệnh
        </button>
        <a href="lenh-san-xuat-danh-sach" class="btn btn-outline-secondary fw-bold shadow-sm px-4">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

<div id="loader" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-2 text-muted">Đang tải dữ liệu hồ sơ...</p>
</div>

<div id="content" class="d-none">
    <div class="row g-4 mb-4">
        <!-- Thông tin chính -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <span class="text-uppercase small fw-bold text-muted mb-1 d-block font-monospace">Production Order</span>
                            <h2 class="fw-bold text-primary mb-0" id="dMalenh"></h2>
                        </div>
                        <div id="dStatusBadge"></div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Sản phẩm</label>
                                <div class="fw-bold text-dark fs-5" id="dTensp"></div>
                                <div class="small text-muted" id="dMasp"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Số lượng</label>
                                <div class="fw-bold text-dark fs-5" id="dSoluong"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Đơn vị</label>
                                <div class="fw-bold text-dark fs-5" id="dDvt"></div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 opacity-50">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-calendar-alt me-2 text-muted"></i>
                                <span class="small text-muted">Ngày lập kế hoạch:</span>
                            </div>
                            <div class="fw-bold" id="dNgaysx"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-play-circle me-2 text-muted"></i>
                                <span class="small text-muted">Ngày bắt đầu:</span>
                            </div>
                            <div class="fw-bold text-primary" id="dNgaybd">N/A</div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-check-double me-2 text-muted"></i>
                                <span class="small text-muted">Ngày hoàn thành:</span>
                            </div>
                            <div class="fw-bold text-success" id="dNgaykt">N/A</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ghi chú & Tóm tắt -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-uppercase mb-3"><i class="fas fa-comment-dots me-2"></i>Ghi chú nội bộ</h6>
                    <p class="mb-4 opacity-75 italic" id="dGhichu">Không có ghi chú nào cho lệnh này.</p>
                    
                    <div class="p-3 bg-white bg-opacity-10 rounded-3">
                        <div class="small opacity-75 mb-2">Thông tin hệ thống</div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Phân xưởng:</span>
                            <span class="fw-bold">Xưởng A</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Giám sát:</span>
                            <span class="fw-bold">Hệ thống</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chi tiết tiêu hao -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-leaf me-2 text-success"></i>Nguyên vật liệu đã tiêu hao</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Nguyên vật liệu</th>
                        <th>Mã NVL</th>
                        <th class="text-end">Số lượng tiêu hao</th>
                        <th class="pe-4 text-center">Đơn vị</th>
                    </tr>
                </thead>
                <tbody id="tblConsumed">
                    <!-- Data here -->
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white text-muted small py-3" id="noConsumedMsg" style="display:none;">
            Lệnh này chưa ghi nhận tiêu hao (Có thể đang trong quá trình chuẩn bị hoặc đã bị hủy).
        </div>
    </div>

    <!-- Chi tiết nhập kho -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-warehouse me-2 text-warning"></i>Chi tiết nhập thành phẩm</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Thành phẩm</th>
                        <th>Kho nhập</th>
                        <th class="text-end">Số lượng nhập</th>
                        <th class="pe-4 text-center">Đơn vị</th>
                    </tr>
                </thead>
                <tbody id="tblProduced">
                    <!-- Data here -->
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white text-muted small py-3" id="noProducedMsg" style="display:none;">
            Lệnh này chưa hoàn thành nhập kho.
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary { color: white; }
    .fs-7 { font-size: 0.85rem; }
    .badge-status { padding: 6px 16px; border-radius: 50px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; }
    @media print {
        .btn, .page-header, .sidebar, .navbar { display: none !important; }
        .card { border: 1px solid #ddd !important; shadow: none !important; }
        body { background: white !important; }
    }
</style>

<script>
const malenh = '<?= htmlspecialchars($id) ?>';

async function loadDetails() {
    if (!malenh) return showAlert('Mã lệnh không hợp lệ', 'danger');
    
    const headers = getHeaders();
    if (!headers) return;
    
    try {
        const res = await fetch(API + '/production-orders/' + malenh + '/details', { headers });
        const data = await res.json();
        
        if (!data.success) throw new Error(data.message);
        
        const { order, consumed, produced } = data.data;
        
        // Fill order info
        document.getElementById('dMalenh').textContent = order.Malenh;
        document.getElementById('dTensp').textContent = order.Tensp || 'N/A';
        document.getElementById('dMasp').textContent = 'Mã sản phẩm: ' + order.Masp;
        document.getElementById('dSoluong').textContent = (parseFloat(order.Soluongsanxuat)).toLocaleString('vi-VN');
        document.getElementById('dDvt').textContent = 'Cái / Bộ'; 
        document.getElementById('dNgaysx').textContent = formatDate(order.Ngaysanxuat);
        document.getElementById('dNgaybd').textContent = order.Ngaybatdau ? formatDate(order.Ngaybatdau) : 'Chưa bắt đầu';
        document.getElementById('dNgaykt').textContent = order.Ngayketthuc ? formatDate(order.Ngayketthuc) : 'Chưa kết thúc';
        document.getElementById('dGhichu').textContent = order.Ghichu || 'Không có ghi chú.';
        
        const status = statusBadgeConfig(order.Trangthai);
        document.getElementById('dStatusBadge').innerHTML = `<span class="badge-status ${status.class}">${status.label}</span>`;
        
        // Fill Consumed
        const tblConsumed = document.getElementById('tblConsumed');
        if (consumed && consumed.length) {
            tblConsumed.innerHTML = consumed.map(c => `
                <tr>
                    <td class="ps-4 fw-bold text-dark">${c.Tennvl}</td>
                    <td><span class="font-monospace fs-7 text-muted">${c.Manvl}</span></td>
                    <td class="text-end fw-bold text-danger">${(parseFloat(c.Soluong)).toLocaleString('vi-VN')}</td>
                    <td class="text-center"><span class="badge bg-light text-dark border">${c.Dvt}</span></td>
                </tr>
            `).join('');
            document.getElementById('noConsumedMsg').style.display = 'none';
        } else {
            document.getElementById('noConsumedMsg').style.display = 'block';
        }
        
        // Fill Produced
        const tblProduced = document.getElementById('tblProduced');
        if (produced && produced.length) {
            tblProduced.innerHTML = produced.map(p => `
                <tr>
                    <td class="ps-4 fw-bold text-dark">${p.Tensp}</td>
                    <td><i class="fas fa-warehouse me-1 text-muted"></i> ${p.Tenkho}</td>
                    <td class="text-end fw-bold text-success">${(parseFloat(p.Soluong)).toLocaleString('vi-VN')}</td>
                    <td class="text-center"><span class="badge bg-light text-dark border">${p.Dvt}</span></td>
                </tr>
            `).join('');
            document.getElementById('noProducedMsg').style.display = 'none';
        } else {
            document.getElementById('noProducedMsg').style.display = 'block';
        }
        
        document.getElementById('loader').classList.add('d-none');
        document.getElementById('content').classList.remove('d-none');
        
    } catch (e) {
        showAlert('Lỗi: ' + e.message, 'danger');
    }
}

function formatDate(d) {
    if (!d) return 'N/A';
    return new Date(d).toLocaleDateString('vi-VN', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric' 
    });
}

function statusBadgeConfig(s) {
    const config = {
        cho_xu_ly: { class: 'bg-warning text-dark', label: 'Chờ xử lý' },
        dang_san_xuat: { class: 'bg-primary text-white', label: 'Đang sản xuất' },
        hoan_thanh: { class: 'bg-success text-white', label: 'Hoàn thành' },
        huy: { class: 'bg-danger text-white', label: 'Đã hủy' }
    };
    return config[s] || { class: 'bg-secondary text-white', label: s };
}

document.addEventListener('DOMContentLoaded', loadDetails);
</script>
