<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1" style="font-size:1.6rem text-dark"><i class="fas fa-cubes me-2 text-primary"></i>Tồn kho thành phẩm</h1>
        <p class="text-muted mb-0">Danh sách tồn kho sản phẩm hoàn thiện chi tiết theo từng kho hàng</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-3">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input class="form-control border-start-0" id="searchInput" placeholder="Tìm theo kho, mã SP, tên..." oninput="filterTable()">
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kho</th>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>ĐVT</th>
                        <th class="text-end pe-4">Số lượng tồn</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <tr><td colspan="5" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .badge-kho { background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 20px; font-size: .75rem; font-weight: 600; }
    .badge-dvt { background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: .75rem; }
    code { font-size: 0.9rem; color: #0284c7; }
</style>

<script>
async function load() {
    const headers = getHeaders();
    if (!headers) return;
    try {
        const res = await fetch(API + '/inventory', { headers });
        const data = await res.json();
        const tb = document.getElementById('tbody');
        if (!data.success) throw new Error(data.message);
        
        const rows = data.data.products || [];
        if (!rows.length) {
            tb.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">Chưa có dữ liệu tồn kho sản phẩm.</td></tr>';
            return;
        }

        tb.innerHTML = rows.map(r => `
            <tr>
                <td class="ps-4"><span class="badge-kho"><i class="fas fa-warehouse me-1"></i> [${r.Makho}] ${r.Tenkho}</span></td>
                <td><code class="fw-bold">${r.Masp}</code></td>
                <td><span class="fw-bold text-dark">${r.Tensp}</span></td>
                <td><span class="badge-dvt">${r.Dvt}</span></td>
                <td class="text-end pe-4">
                    <span class="fw-bold fs-6 ${parseInt(r.Soluongton) < 10 ? 'text-danger' : 'text-success'}">
                        ${Number(r.Soluongton || 0).toLocaleString('vi-VN')}
                    </span>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        document.getElementById('tbody').innerHTML = `<tr><td colspan="5" class="text-center text-danger py-5">Lỗi: ${e.message}</td></tr>`;
    }
}

function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', load);
</script>
