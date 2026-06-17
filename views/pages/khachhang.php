<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Quản lý khách hàng</h2>
        <p class="text-muted mb-0">Thêm, sửa, xóa thông tin khách hàng và phân loại</p>
    </div>
    <button class="btn btn-primary fw-bold px-4 shadow-sm" onclick="openCustomerModal()">
        <i class="fas fa-plus me-2"></i>Thêm khách hàng
    </button>
</div>

<div class="card mb-4">
    <div class="card-body p-3">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input class="form-control border-start-0" id="searchInput" placeholder="Tìm kiếm theo mã, tên, SĐT..." oninput="filterCustomers()">
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Mã KH</th>
                        <th>Tên khách hàng</th>
                        <th>SĐT</th>
                        <th>Địa chỉ</th>
                        <th>Loại KH</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="customerList">
                    <tr><td colspan="7" class="text-center py-5 text-muted">Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Khách hàng -->
<div class="modal fade" id="khModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="customerModalTitle">Thêm khách hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customerForm">
                    <input type="hidden" id="editMode" value="false">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Mã KH *</label>
                        <input class="form-control" id="fMakh" required placeholder="VD: KH001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Tên khách hàng *</label>
                        <input class="form-control" id="fTenkh" required placeholder="Nhập tên đầy đủ">
                    </div>
                    <div class="row g-3">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase">SĐT</label>
                            <input class="form-control" id="fSdt" placeholder="0912345678">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase">Loại KH</label>
                            <select class="form-select" id="fLoai">
                                <option value="">-- Chọn loại --</option>
                            </select>
                        </div>
                    </div>
                   
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Địa chỉ</label>
                        <input class="form-control" id="fDiachi" placeholder="Nhập địa chỉ liên hệ">
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-primary px-4 fw-bold" onclick="saveCustomer()">
                    <i class="fas fa-save me-1"></i>Lưu thông tin
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let allCustomers = [];
    let customerModal;

    document.addEventListener('DOMContentLoaded', () => {
        customerModal = new bootstrap.Modal(document.getElementById('khModal'));
        loadTypes();
        loadCustomers();
    });

    async function loadTypes() {
        const headers = getHeaders();
        if(!headers) return;
        try {
            const res = await fetch(API + '/customer-types', { headers });
            const data = await res.json();
            if(data.success) {
                const sel = document.getElementById('fLoai');
                sel.innerHTML = '<option value="">-- Chọn loại --</option>';
                data.data.types.forEach(t => {
                    sel.innerHTML += `<option value="${t.Maloaikh}">${t.Tenloaikh}</option>`;
                });
            }
        } catch(e) { console.error('Lỗi tải loại khách hàng'); }
    }

    async function loadCustomers() {
        const headers = getHeaders();
        if(!headers) return;
        try {
            const res = await fetch(API + '/customers', { headers });
            const data = await res.json();
            if(data.success) {
                allCustomers = data.data.customers || [];
                renderCustomers(allCustomers);
            }
        } catch(e) {
            document.getElementById('customerList').innerHTML = '<tr><td colspan="7" class="text-danger text-center py-4">Lỗi kết nối máy chủ</td></tr>';
        }
    }

    function renderCustomers(list) {
        const tbody = document.getElementById('customerList');
        tbody.innerHTML = list.length ? '' : '<tr><td colspan="7" class="text-center py-4 text-muted">Không tìm thấy khách hàng nào.</td></tr>';
        list.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-4"><code>${r.Makh}</code></td>
                <td class="fw-bold text-dark">${r.Tenkh}</td>
                <td>${r.Sdtkh || '—'}</td>
                <td><small class="text-muted">${r.Diachikh || '—'}</small></td>
                <td><span class="badge bg-primary bg-opacity-10 text-primary">${r.Tenloaikh || r.Maloaikh || '—'}</span></td>
                <td class="text-center pe-4">
                    <button class="btn btn-outline-primary btn-action" onclick="openCustomerModal('${r.Makh}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-outline-danger btn-action" onclick="deleteCustomer('${r.Makh}')"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterCustomers() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        const filtered = allCustomers.filter(r => 
            r.Makh.toLowerCase().includes(q) || 
            r.Tenkh.toLowerCase().includes(q) || 
            (r.Sdtkh && r.Sdtkh.includes(q))
        );
        renderCustomers(filtered);
    }

    function openCustomerModal(id = null) {
        document.getElementById('customerForm').reset();
        const editModeInput = document.getElementById('editMode');
        const maInput = document.getElementById('fMakh');

        if (id) {
            document.getElementById('customerModalTitle').innerText = 'Sửa khách hàng';
            const r = allCustomers.find(x => x.Makh === id);
            if (r) {
                editModeInput.value = "true";
                maInput.value = r.Makh;
                maInput.readOnly = true;
                document.getElementById('fTenkh').value = r.Tenkh;
                document.getElementById('fSdt').value = r.Sdtkh || '';
                document.getElementById('fDiachi').value = r.Diachikh || '';
                document.getElementById('fLoai').value = r.Maloaikh || '';
            }
        } else {
            document.getElementById('customerModalTitle').innerText = 'Thêm khách hàng';
            editModeInput.value = "false";
            maInput.readOnly = false;
        }
        customerModal.show();
    }

    async function saveCustomer() {
        const headers = getHeaders();
        if(!headers) return;
        const isEdit = document.getElementById('editMode').value === "true";
        const id = document.getElementById('fMakh').value;
        const body = {
            Makh: id,
            Tenkh: document.getElementById('fTenkh').value,
            Sdtkh: document.getElementById('fSdt').value,
            Diachikh: document.getElementById('fDiachi').value,
            Maloaikh: document.getElementById('fLoai').value
        };

        if(!body.Makh || !body.Tenkh) return alert('Vui lòng nhập mã và tên khách hàng!');

        try {
            const url = isEdit ? `${API}/customers/${id}` : `${API}/customers`;
            const method = isEdit ? 'PUT' : 'POST';
            
            const res = await fetch(url, {
                method,
                headers: { ...headers, 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            
            if(data.success) {
                showAlert(isEdit ? 'Cập nhật thành công!' : 'Thêm mới thành công!');
                customerModal.hide();
                loadCustomers();
            } else {
                alert('Lỗi: ' + data.message);
            }
        } catch(e) { alert('Lỗi kết nối API'); }
    }

    async function deleteCustomer(id) {
        if(!confirm(`Xác nhận xóa khách hàng ${id}?`)) return;
        const headers = getHeaders();
        if(!headers) return;
        try {
            const res = await fetch(`${API}/customers/${id}`, { method: 'DELETE', headers });
            const data = await res.json();
            if(data.success) { 
                showAlert('Đã xóa thành công!'); 
                loadCustomers(); 
            } else alert(data.message);
        } catch(e) { alert('Lỗi kết nối API'); }
    }
</script>
