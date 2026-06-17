<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-dark">Danh sách Sản phẩm</h2>
    <button class="btn btn-success fw-bold px-4" onclick="openModal()">
        <i class="fas fa-plus me-2"></i>Thêm sản phẩm
    </button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-5">
                <input type="text" id="tkten" class="form-control" placeholder="Tìm theo tên sản phẩm..." onkeyup="filterProducts()">
            </div>
            <div class="col-md-4">
                <select id="tkdm" class="form-select" onchange="filterProducts()">
                    <option value="">Tất cả danh mục</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Đơn vị</th>
                        <th class="text-end">Giá bán</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="productList">
                    <tr><td colspan="6" class="text-center py-5 text-muted">Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Sản phẩm -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Thêm Sản phẩm</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <input type="hidden" id="editMode" value="false">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã sản phẩm *</label>
                        <input type="text" id="Masp" class="form-control" required placeholder="Ví dụ: SP001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên sản phẩm *</label>
                        <input type="text" id="Tensp" class="form-control" required placeholder="Nhập tên sản phẩm">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Danh mục *</label>
                        <select id="Madm" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Đơn vị *</label>
                            <input type="text" id="Dvt" class="form-control" required placeholder="Cái, Bộ, m2...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Giá bán *</label>
                            <input type="number" id="Giaban" class="form-control" required placeholder="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" onclick="saveProduct()">Lưu sản phẩm</button>
            </div>
        </div>
    </div>
</div>

<script>
    let allProducts = [];
    let productModal;

    document.addEventListener('DOMContentLoaded', () => {
        productModal = new bootstrap.Modal(document.getElementById('productModal'));
        loadData();
    });

    async function loadData() {
        try {
            const headers = getHeaders();
            if(!headers) return; // User layout logic handles redirect

            const [resP, resC] = await Promise.all([
                fetch(API + '/products', { headers }),
                fetch(API + '/categories', { headers })
            ]);

            const dataP = await resP.json();
            const dataC = await resC.json();

            if(dataC.success) {
                const sel = document.getElementById('Madm');
                const filterSel = document.getElementById('tkdm');
                sel.innerHTML = '<option value="">-- Chọn danh mục --</option>';
                dataC.data.categories.forEach(c => {
                    sel.innerHTML += `<option value="${c.Madm}">${c.Tendm}</option>`;
                    filterSel.innerHTML += `<option value="${c.Madm}">${c.Tendm}</option>`;
                });
            }

            if(dataP.success) {
                allProducts = dataP.data.products || [];
                renderProducts(allProducts);
            }
        } catch(err) {
            document.getElementById('productList').innerHTML = '<tr><td colspan="6" class="text-danger text-center py-4">Lỗi kết nối hệ thống</td></tr>';
        }
    }

    function renderProducts(list) {
        const tbody = document.getElementById('productList');
        tbody.innerHTML = list.length ? '' : '<tr><td colspan="6" class="text-center py-4 text-muted">Không tìm thấy sản phẩm nào</td></tr>';
        list.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-4"><code>${item.Masp}</code></td>
                <td class="chu" style="font-weight:700;color:#d30b0b">${item.Tensp}</td>
                <td><span class="chip">${item.Tendm || item.Madm}</span></td>
                <td>${item.Dvt}</td>
                <td class="text-end fw-bold">${parseInt(item.Giaban || 0).toLocaleString('vi-VN')} đ</td>
                <td class="text-center pe-4">
                    <button class="btn btn-outline-primary btn-action" onclick="openProductModal('${item.Masp}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-outline-danger btn-action" onclick="deleteProduct('${item.Masp}')"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterProducts() {
        const ten = document.getElementById('tkten').value.toLowerCase();
        const dm = document.getElementById('tkdm').value;
        const filtered = allProducts.filter(p => 
            (p.Tensp.toLowerCase().includes(ten) || p.Masp.toLowerCase().includes(ten)) &&
            (dm === '' || p.Madm === dm)
        );
        renderProducts(filtered);
    }

    function openProductModal(id = null) {
        document.getElementById('productForm').reset();
        const editModeInput = document.getElementById('editMode');
        const maInput = document.getElementById('Masp');

        if (id) {
            document.getElementById('modalTitle').innerText = 'Sửa Sản phẩm';
            const p = allProducts.find(x => x.Masp === id);
            if (p) {
                editModeInput.value = "true";
                maInput.value = p.Masp;
                maInput.readOnly = true;
                document.getElementById('Tensp').value = p.Tensp;
                document.getElementById('Madm').value = p.Madm;
                document.getElementById('Dvt').value = p.Dvt;
                document.getElementById('Giaban').value = p.Giaban;
            }
        } else {
            document.getElementById('modalTitle').innerText = 'Thêm Sản phẩm';
            editModeInput.value = "false";
            maInput.readOnly = false;
        }
        productModal.show();
    }
    
    // Alias for openProductModal to support old onclick if any
    const openModal = openProductModal;

    async function saveProduct() {
        const headers = getHeaders();
        if(!headers) return;
        const isEdit = document.getElementById('editMode').value === "true";
        const id = document.getElementById('Masp').value;
        const body = {
            Masp: id,
            Tensp: document.getElementById('Tensp').value,
            Madm: document.getElementById('Madm').value,
            Dvt: document.getElementById('Dvt').value,
            Giaban: document.getElementById('Giaban').value
        };

        if(!body.Masp || !body.Tensp || !body.Madm) return alert('Vui lòng điền đủ thông tin bắt buộc!');

        try {
            const url = isEdit ? `${API}/products/${id}` : `${API}/products`;
            const method = isEdit ? 'PUT' : 'POST';
            
            const res = await fetch(url, { method, headers, body: JSON.stringify(body) });
            const data = await res.json();
            
            if(data.success) {
                showAlert('Lưu thành công!');
                productModal.hide();
                loadData();
            } else {
                alert('Lỗi: ' + data.message);
            }
        } catch(err) {
            alert('Lỗi kết nối API');
        }
    }

    async function deleteProduct(id) {
        if(!confirm(`Xác nhận xóa sản phẩm ${id}?`)) return;
        const headers = getHeaders();
        if(!headers) return;
        try {
            const res = await fetch(`${API}/products/${id}`, { method: 'DELETE', headers });
            const data = await res.json();
            if(data.success) {
                showAlert('Đã xóa!');
                loadData();
            } else alert('Lỗi: ' + data.message);
        } catch(err) { alert('Lỗi kết nối API'); }
    }
</script>
