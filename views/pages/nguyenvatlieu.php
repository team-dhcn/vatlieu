<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-dark">Nguyên vật liệu</h2>
    <button class="btn btn-success fw-bold px-4" onclick="openMaterialModal()">
        <i class="fas fa-plus me-2"></i>Thêm Nguyên vật liệu
    </button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="tkten" class="form-control border-start-0" placeholder="Tìm theo tên hoặc mã nguyên vật liệu..." onkeyup="filterMaterials()">
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
                        <th class="ps-4">Mã NVL</th>
                        <th>Tên Nguyên vật liệu</th>
                        <th>Đơn vị tính</th>
                        <th class="text-end">Giá vốn ước tính</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="materialList">
                    <tr><td colspan="5" class="text-center py-5 text-muted">Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa NVL -->
<div class="modal fade" id="materialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="materialModalTitle">Thêm Nguyên vật liệu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="materialForm">
                    <input type="hidden" id="editMode" value="false">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã Nguyên vật liệu *</label>
                        <input type="text" id="Manvl" class="form-control" required placeholder="Ví dụ: NVL001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên Nguyên vật liệu *</label>
                        <input type="text" id="Tennvl" class="form-control" required placeholder="Nhập tên vật liệu">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Đơn vị tính *</label>
                            <input type="text" id="Dvt" class="form-control" required placeholder="Kg, m3, bao...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Giá vốn (nếu có)</label>
                            <input type="number" id="Giavon" class="form-control" placeholder="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success px-4 fw-bold" onclick="saveMaterial()">Lưu thông tin</button>
            </div>
        </div>
    </div>
</div>

<script>
    let allMaterials = [];
    let materialModal;

    document.addEventListener('DOMContentLoaded', () => {
        materialModal = new bootstrap.Modal(document.getElementById('materialModal'));
        loadData();
    });

    async function loadData() {
        const headers = getHeaders();
        if (!headers) return;
        try {
            const res = await fetch(API + '/materials', { headers });
            const data = await res.json();
            if(data.success) {
                allMaterials = data.data.materials || [];
                renderMaterials(allMaterials);
            }
        } catch(err) {
            document.getElementById('materialList').innerHTML = '<tr><td colspan="5" class="text-danger text-center py-4">Lỗi kết nối API</td></tr>';
        }
    }

    function renderMaterials(list) {
        const tbody = document.getElementById('materialList');
        tbody.innerHTML = list.length ? '' : '<tr><td colspan="5" class="text-center py-4 text-muted">Không tìm thấy dữ liệu</td></tr>';
        list.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-4"><code>${item.Manvl}</code></td>
                <td class="fw-bold" style="color: #198754">${item.Tennvl}</td>
                <td>${item.Dvt || '—'}</td>
                <td class="text-end fw-bold">${parseInt(item.Giavon || 0).toLocaleString('vi-VN')} đ</td>
                <td class="text-center pe-4">
                    <button class="btn btn-outline-primary btn-action" onclick="openMaterialModal('${item.Manvl}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-outline-danger btn-action" onclick="deleteMaterial('${item.Manvl}')"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterMaterials() {
        const key = document.getElementById('tkten').value.toLowerCase();
        const filtered = allMaterials.filter(m => 
            m.Tennvl.toLowerCase().includes(key) || m.Manvl.toLowerCase().includes(key)
        );
        renderMaterials(filtered);
    }

    function openMaterialModal(id = null) {
        document.getElementById('materialForm').reset();
        const editModeInput = document.getElementById('editMode');
        const maInput = document.getElementById('Manvl');

        if (id) {
            document.getElementById('materialModalTitle').innerText = 'Sửa Nguyên vật liệu';
            const m = allMaterials.find(x => x.Manvl === id);
            if (m) {
                editModeInput.value = "true";
                maInput.value = m.Manvl;
                maInput.readOnly = true;
                document.getElementById('Tennvl').value = m.Tennvl;
                document.getElementById('Dvt').value = m.Dvt || '';
                document.getElementById('Giavon').value = m.Giavon || '';
            }
        } else {
            document.getElementById('materialModalTitle').innerText = 'Thêm Nguyên vật liệu';
            editModeInput.value = "false";
            maInput.readOnly = false;
        }
        materialModal.show();
    }

    async function saveMaterial() {
        const headers = getHeaders();
        if(!headers) return;
        const isEdit = document.getElementById('editMode').value === "true";
        const id = document.getElementById('Manvl').value;
        const body = {
            Manvl: id,
            Tennvl: document.getElementById('Tennvl').value,
            Dvt: document.getElementById('Dvt').value,
            Giavon: document.getElementById('Giavon').value
        };
        const method = isEdit ? 'PUT' : 'POST';
        const url = isEdit ? API + '/materials/' + id : API + '/materials';

        if(!body.Manvl || !body.Tennvl) return alert('Vui lòng điền đủ thông tin bắt buộc!');

        try {
            const res = await fetch(url, { 
                method, 
                headers: { ...headers, 'Content-Type': 'application/json' },
                body: JSON.stringify(body) 
            });
            const data = await res.json();
            
            if(data.success) {
                showAlert('Lưu thành công!');
                materialModal.hide();
                loadData();
            } else {
                alert('Lỗi: ' + data.message);
            }
        } catch(err) { alert('Lỗi kết nối API'); }
    }

    async function deleteMaterial(id) {
        if(!confirm(`Xác nhận xóa nguyên vật liệu ${id}?`)) return;
        const headers = getHeaders();
        if(!headers) return;
        try {
            const res = await fetch(`${API}/materials/${id}`, { method: 'DELETE', headers });
            const data = await res.json();
            if(data.success) { 
                showAlert('Đã xóa!'); 
                loadData(); 
            } else alert('Lỗi: ' + data.message);
        } catch(err) { alert('Lỗi kết nối API'); }
    }
</script>
