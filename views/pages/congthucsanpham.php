<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-dark">Định mức & Công thức</h2>
</div>

<div class="card mb-4 p-4">
    <h5 class="fw-bold mb-3"><i class="fas fa-plus-circle me-2 text-primary"></i>Thêm/Cập nhật định mức</h5>
    <form id="formulaForm" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-bold">Sản phẩm hoàn thiện</label>
            <select id="Masp" class="form-select" required>
                <option value="">-- Chọn sản phẩm --</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold">Nguyên vật liệu cần dùng</label>
            <select id="Manvl" class="form-select" required>
                <option value="">-- Chọn nguyên vật liệu --</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold">Số lượng</label>
            <input type="number" id="Soluong" class="form-control" step="0.01" value="1" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary w-100 fw-bold" onclick="saveFormula()">
                <i class="fas fa-save me-1"></i>Lưu định mức
            </button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Sản phẩm thành phẩm</th>
                        <th>Nguyên vật liệu thành phần</th>
                        <th class="text-center">Định mức (SL)</th>
                        <th>Đơn vị tính</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="formulaList">
                    <tr><td colspan="5" class="text-center py-5 text-muted">Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadDropdowns();
        loadFormulas();
    });

    async function loadDropdowns() {
        const headers = getHeaders();
        if (!headers) return;
        try {
            const [resP, resM] = await Promise.all([
                fetch(API + '/products', { headers }),
                fetch(API + '/materials', { headers })
            ]);
            const dataP = await resP.json();
            const dataM = await resM.json();

            const selP = document.getElementById('Masp');
            const products = dataP.data.products || [];
            products.forEach(p => selP.innerHTML += `<option value="${p.Masp}">${p.Masp} - ${p.Tensp}</option>`);

            const selM = document.getElementById('Manvl');
            const materials = dataM.data.materials || [];
            materials.forEach(m => selM.innerHTML += `<option value="${m.Manvl}">${m.Manvl} - ${m.Tennvl} (${m.Dvt})</option>`);
        } catch (err) { console.error('Lỗi tải dropdown'); }
    }

    async function loadFormulas() {
        const headers = getHeaders();
        if (!headers) return;
        try {
            const res = await fetch(API + '/formulas', { headers });
            const data = await res.json();
            const tbody = document.getElementById('formulaList');
            tbody.innerHTML = '';
            
            if (data.success && data.data.formulas && data.data.formulas.length > 0) {
                let lastMasp = '';
                data.data.formulas.forEach(f => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="ps-4 ${f.Masp === lastMasp ? 'opacity-25' : 'fw-bold'}" style="${f.Masp !== lastMasp ? 'color:#6610f2' : ''}">
                            ${f.Masp === lastMasp ? '' : (f.Tensp || f.Masp)}
                        </td>
                        <td><span class="text-primary fw-semibold">${f.Tennvl}</span></td>
                        <td class="text-center"><span class="badge bg-info bg-opacity-10 text-info" style="font-size:0.9rem;padding:5px 12px;border-radius:20px;">${f.Soluong}</span></td>
                        <td><small class="text-muted">${f.Dvt || ''}</small></td>
                        <td class="text-center pe-4">
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteFormula('${f.Masp}', '${f.Manvl}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                    lastMasp = f.Masp;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Chưa có công thức nào</td></tr>';
            }
        } catch (err) {
            document.getElementById('formulaList').innerHTML = '<tr><td colspan="5" class="text-danger text-center py-4">Lỗi kết nối máy chủ</td></tr>';
        }
    }

    async function saveFormula() {
        const headers = getHeaders();
        if (!headers) return;
        const body = {
            Masp: document.getElementById('Masp').value,
            Manvl: document.getElementById('Manvl').value,
            Soluong: document.getElementById('Soluong').value
        };

        if (!body.Masp || !body.Manvl || !body.Soluong) return alert('Vui lòng chọn đủ thông tin!');

        try {
            const res = await fetch(API + '/formulas', {
                method: 'POST',
                headers: { ...headers, 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            if (data.success) {
                showAlert('Lưu thành công!');
                loadFormulas();
            } else alert('Lỗi: ' + data.message);
        } catch (err) { alert('Lỗi API'); }
    }

    async function deleteFormula(masp, manvl) {
        if (!confirm(`Xóa thành phần này khỏi công thức?`)) return;
        const headers = getHeaders();
        if (!headers) return;
        try {
            const res = await fetch(`${API}/formulas/${masp}_${manvl}`, { method: 'DELETE', headers });
            const data = await res.json();
            if (data.success) {
                showAlert('Đã xóa!');
                loadFormulas();
            }
            else alert('Lỗi: ' + data.message);
        } catch (err) { alert('Lỗi API'); }
    }
</script>
