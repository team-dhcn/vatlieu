<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-dark">Nhà cung cấp</h2>
    <button class="btn btn-primary fw-bold px-4 shadow-sm" onclick="openSupplierModal()">
        <i class="fas fa-plus me-2"></i>Thêm Nhà cung cấp
    </button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="tkten" class="form-control border-start-0" placeholder="Tìm theo tên hoặc mã NCC..." onkeyup="filterSuppliers()">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Mã NCC</th>
                        <th>Tên nhà cung cấp</th>
                        <th>Số điện thoại</th>
                        <th>Địa chỉ</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="supplierList">
                    <tr><td colspan="5" class="text-center py-5 text-muted">Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Nhà cung cấp -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="supplierModalTitle">Thêm Nhà cung cấp</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="supplierForm">
                    <input type="hidden" id="editMode" value="false">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Mã Nhà cung cấp *</label>
                        <input type="text" id="Mancc" class="form-control" required placeholder="Ví dụ: NCC001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Tên Nhà cung cấp *</label>
                        <input type="text" id="Tenncc" class="form-control" required placeholder="Nhập tên đầy đủ">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Số điện thoại</label>
                        <input type="text" id="Sdtncc" class="form-control" placeholder="Ví dụ: 0912xxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Địa chỉ</label>
                        <textarea id="Diachincc" class="form-control" rows="3" placeholder="Nhập địa chỉ liên hệ"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" onclick="saveSupplier()">Lưu Nhà cung cấp</button>
            </div>
        </div>
    </div>
</div>

<script>
    let allSuppliers = [];
    let supplierModal;

    document.addEventListener('DOMContentLoaded', () => {
        supplierModal = new bootstrap.Modal(document.getElementById('supplierModal'));
        loadData();
    });

    async function loadData() {
        const headers = getHeaders();
        if (!headers) return;
        try {
            const res = await fetch(API + '/suppliers', { headers });
            const data = await res.json();
            if(data.success) {
                allSuppliers = data.data.suppliers || [];
                renderSuppliers(allSuppliers);
            }
        } catch(err) {
            document.getElementById('supplierList').innerHTML = '<tr><td colspan="5" class="text-danger text-center py-4">Lỗi kết nối API Gateway</td></tr>';
        }
    }

    function renderSuppliers(list) {
        const tbody = document.getElementById('supplierList');
        tbody.innerHTML = list.length ? '' : '<tr><td colspan="5" class="text-center py-4 text-muted">Không tìm thấy NCC nào</td></tr>';
        list.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-4"><code>${item.Mancc}</code></td>
                <td class="fw-bold" style="color: #0d6efd">${item.Tenncc}</td>
                <td>${item.Sdtncc || '—'}</td>
                <td><small class="text-secondary">${item.Diachincc || '—'}</small></td>
                <td class="text-center pe-4">
                    <button class="btn btn-outline-primary btn-action" onclick="openSupplierModal('${item.Mancc}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-outline-danger btn-action" onclick="deleteSupplier('${item.Mancc}')"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterSuppliers() {
        const key = document.getElementById('tkten').value.toLowerCase();
        const filtered = allSuppliers.filter(c => 
            c.Tenncc.toLowerCase().includes(key) || c.Mancc.toLowerCase().includes(key)
        );
        renderSuppliers(filtered);
    }

    function openSupplierModal(id = null) {
        document.getElementById('supplierForm').reset();
        const editModeInput = document.getElementById('editMode');
        const maInput = document.getElementById('Mancc');

        if (id) {
            document.getElementById('supplierModalTitle').innerText = 'Sửa Nhà cung cấp';
            const s = allSuppliers.find(x => x.Mancc === id);
            if (s) {
                editModeInput.value = "true";
                maInput.value = s.Mancc;
                maInput.readOnly = true;
                document.getElementById('Tenncc').value = s.Tenncc;
                document.getElementById('Sdtncc').value = s.Sdtncc || '';
                document.getElementById('Diachincc').value = s.Diachincc || '';
            }
        } else {
            document.getElementById('supplierModalTitle').innerText = 'Thêm Nhà cung cấp';
            editModeInput.value = "false";
            maInput.readOnly = false;
        }
        supplierModal.show();
    }

    async function saveSupplier() {
        const headers = getHeaders();
        if(!headers) return;
        const isEdit = document.getElementById('editMode').value === "true";
        const id = document.getElementById('Mancc').value;
        const body = {
            Mancc: id,
            Tenncc: document.getElementById('Tenncc').value,
            Sdtncc: document.getElementById('Sdtncc').value,
            Diachincc: document.getElementById('Diachincc').value
        };
        const method = isEdit ? 'PUT' : 'POST';
        const url = isEdit ? API + '/suppliers/' + id : API + '/suppliers';

        try {
            const res = await fetch(url, {
                method,
                headers: { ...headers, 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            
            if(data.success) {
                showAlert('Lưu thành công!');
                supplierModal.hide();
                loadData();
            } else {
                alert('Lỗi: ' + data.message);
            }
        } catch(err) { alert('Lỗi kết nối API'); }
    }

    async function deleteSupplier(id) {
        if(!confirm(`Xác nhận xóa nhà cung cấp ${id}?`)) return;
        const headers = getHeaders();
        if(!headers) return;
        try {
            const res = await fetch(`${API}/suppliers/${id}`, { method: 'DELETE', headers });
            const data = await res.json();
            if(data.success) { 
                showAlert('Đã xóa!'); 
                loadData(); 
            } else alert('Lỗi: ' + data.message);
        } catch(err) { alert('Lỗi kết nối API'); }
    }
</script>
