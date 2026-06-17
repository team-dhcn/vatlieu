<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-dark">Danh mục Sản phẩm</h2>
    <button class="btn btn-success fw-bold px-4 shadow-sm" onclick="openCategoryModal()">
        <i class="fas fa-plus me-2"></i>Thêm danh mục
    </button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" id="tkten" class="form-control" placeholder="Tìm theo tên danh mục hoặc mã..." onkeyup="filterCategories()">
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
                        <th class="ps-4">Mã DM</th>
                        <th>Tên danh mục</th>
                        <th>Mô tả</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="categoryList">
                    <tr><td colspan="4" class="text-center py-5 text-muted">Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Danh mục -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="categoryModalTitle">Thêm Danh mục</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <input type="hidden" id="editMode" value="false">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã danh mục *</label>
                        <input type="text" id="Madm" class="form-control" required placeholder="Ví dụ: DM001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên danh mục *</label>
                        <input type="text" id="Tendm" class="form-control" required placeholder="Nhập tên danh mục">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mô tả</label>
                        <textarea id="Mota" class="form-control" rows="3" placeholder="Nhập mô tả chi tiết"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" onclick="saveCategory()">Lưu danh mục</button>
            </div>
        </div>
    </div>
</div>

<script>
    let allCategories = [];
    let categoryModal;

    document.addEventListener('DOMContentLoaded', () => {
        categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
        loadData();
    });

    async function loadData() {
        const headers = getHeaders();
        if (!headers) return;
        try {
            const res = await fetch(API + '/categories', { headers });
            const data = await res.json();
            if(data.success) {
                allCategories = data.data.categories || [];
                renderCategories(allCategories);
            }
        } catch(err) {
            document.getElementById('categoryList').innerHTML = '<tr><td colspan="4" class="text-danger text-center py-4">Lỗi kết nối máy chủ</td></tr>';
        }
    }

    function renderCategories(list) {
        const tbody = document.getElementById('categoryList');
        tbody.innerHTML = list.length ? '' : '<tr><td colspan="4" class="text-center py-4 text-muted">Không có dữ liệu</td></tr>';
        list.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-4"><code>${item.Madm}</code></td>
                <td class="fw-bold text-dark">${item.Tendm}</td>
                <td><small class="text-muted">${item.Mota || '—'}</small></td>
                <td class="text-center pe-4">
                    <button class="btn btn-outline-primary btn-action" onclick="openCategoryModal('${item.Madm}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-outline-danger btn-action" onclick="deleteCategory('${item.Madm}')"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterCategories() {
        const key = document.getElementById('tkten').value.toLowerCase();
        const filtered = allCategories.filter(c => 
            c.Tendm.toLowerCase().includes(key) || c.Madm.toLowerCase().includes(key)
        );
        renderCategories(filtered);
    }

    function openCategoryModal(id = null) {
        document.getElementById('categoryForm').reset();
        const editModeInput = document.getElementById('editMode');
        const maInput = document.getElementById('Madm');

        if (id) {
            document.getElementById('categoryModalTitle').innerText = 'Sửa Danh mục';
            const c = allCategories.find(x => x.Madm === id);
            if (c) {
                editModeInput.value = "true";
                maInput.value = c.Madm;
                maInput.readOnly = true;
                document.getElementById('Tendm').value = c.Tendm;
                document.getElementById('Mota').value = c.Mota || '';
            }
        } else {
            document.getElementById('categoryModalTitle').innerText = 'Thêm Danh mục';
            editModeInput.value = "false";
            maInput.readOnly = false;
        }
        categoryModal.show();
    }

    async function saveCategory() {
        const headers = getHeaders();
        if(!headers) return;
        const isEdit = document.getElementById('editMode').value === "true";
        const id = document.getElementById('Madm').value;
        const body = {
            Madm: id,
            Tendm: document.getElementById('Tendm').value,
            Mota: document.getElementById('Mota').value
        };
        const method = isEdit ? 'PUT' : 'POST';
        const url = isEdit ? API + '/categories/' + id : API + '/categories';

        if(!body.Madm || !body.Tendm) return alert('Vui lòng điền đủ mã và tên danh mục!');

        try {
            const res = await fetch(url, {
                method,
                headers: { ...headers, 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            
            if(data.success) {
                showAlert('Lưu thành công!');
                categoryModal.hide();
                loadData();
            } else {
                alert('Lỗi: ' + data.message);
            }
        } catch(err) { alert('Lỗi kết nối API'); }
    }

    async function deleteCategory(id) {
        if(!confirm(`Xác nhận xóa danh mục ${id}?`)) return;
        const headers = getHeaders();
        if(!headers) return;
        try {
            const res = await fetch(`${API}/categories/${id}`, { method: 'DELETE', headers });
            const data = await res.json();
            if(data.success) { 
                showAlert('Đã xóa!'); 
                loadData(); 
            } else alert('Lỗi: ' + data.message);
        } catch(err) { alert('Lỗi kết nối API'); }
    }
</script>
