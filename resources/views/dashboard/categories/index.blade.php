@extends('layouts.app')

@section('title', 'Danh mục - WebShop Admin')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-3">Danh mục</h2>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Danh sách danh mục</h5>
                        <div class="mt-2 d-flex">
                            <input id="searchName" class="form-control form-control-sm me-2" placeholder="Tìm kiếm theo tên...">
                            <button id="btnSearch" class="btn btn-sm btn-outline-primary me-2">Tìm</button>
                            <button id="btnClearSearch" class="btn btn-sm btn-outline-secondary">Xóa</button>
                        </div>
                    </div>
                    <button id="btnAddCategory" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">Thêm danh mục</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên</th>
                                    <th>Mô tả</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Rows will be populated by JS using API --}}
                            </tbody>
                        </table>
                        <nav>
                            <ul class="pagination pagination-sm" id="categoriesPagination"></ul>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Category Modal (Add/Edit) -->
            <div class="modal fade" id="categoryModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="categoryModalTitle">Thêm danh mục</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="categoryForm">
                                <input type="hidden" id="categoryId" name="id" />
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Tên</label>
                                    <input type="text" class="form-control" id="categoryName" name="name" required maxlength="150">
                                    <div id="errorName" class="text-danger small mt-1"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="categoryDescription" class="form-label">Mô tả</label>
                                    <textarea class="form-control" id="categoryDescription" name="description"></textarea>
                                    <div id="errorDescription" class="text-danger small mt-1"></div>
                                </div>
                                <div id="categoryErrors" class="text-danger small"></div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="button" class="btn btn-primary" id="saveCategoryBtn">Lưu</button>
                        </div>
                    </div>
                </div>
            </div>

            @section('scripts')
            <script>
                (function(){
                    const apiBase = '/api/categories';
                    // For write operations use web proxy routes (use same-origin session auth)
                    const webBase = '/dashboard/categories';
                    const tableBody = document.querySelector('#categoriesTable tbody');
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    function showError(msg){
                        const err = document.getElementById('categoryErrors');
                        err.textContent = msg || '';
                    }

                    async function fetchCategories(params = {}){
                        try{
                            const qs = new URLSearchParams(params);
                            const url = qs.toString() ? `${apiBase}?${qs.toString()}` : apiBase;
                            const res = await fetch(url, {credentials: 'same-origin'});
                            const json = await res.json();
                            // Support both paginated and plain collection
                            const rows = json.data?.data ?? json.data ?? json;
                            renderRows(rows);
                            renderPagination(json.meta || {});
                        }catch(e){
                            console.error(e);
                        }
                    }

                    function renderRows(categories){
                        tableBody.innerHTML = '';
                        if(!categories || categories.length === 0){
                            tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Không có danh mục</td></tr>';
                            return;
                        }

                        categories.forEach(cat => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${cat.category_id ?? cat.id ?? ''}</td>
                                <td>${escapeHtml(cat.name ?? '')}</td>
                                <td>${escapeHtml(cat.description ?? '')}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary btn-edit" data-id="${cat.category_id ?? cat.id}">Sửa</button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${cat.category_id ?? cat.id}">Xóa</button>
                                </td>
                            `;
                            tableBody.appendChild(tr);
                        });

                        // attach handlers
                        document.querySelectorAll('.btn-edit').forEach(btn => btn.addEventListener('click', onEdit));
                        document.querySelectorAll('.btn-delete').forEach(btn => btn.addEventListener('click', onDelete));
                    }

                    function renderPagination(meta){
                        const pager = document.getElementById('categoriesPagination');
                        pager.innerHTML = '';
                        if(!meta || !meta.current_page){
                            return; // no pagination
                        }

                        const current = meta.current_page;
                        const last = meta.last_page || 1;

                        function pageItem(page, text = null, disabled = false, active = false){
                            const li = document.createElement('li');
                            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
                            const a = document.createElement('a');
                            a.className = 'page-link';
                            a.href = '#';
                            a.textContent = text ?? page;
                            a.addEventListener('click', function(e){ e.preventDefault(); if(!disabled && !active) fetchCategories({name: document.getElementById('searchName').value.trim(), page}); });
                            li.appendChild(a);
                            return li;
                        }

                        pager.appendChild(pageItem(current - 1, '«', current <= 1, false));

                        // show nearby pages
                        const start = Math.max(1, current - 2);
                        const end = Math.min(last, current + 2);
                        for(let p = start; p <= end; p++){
                            pager.appendChild(pageItem(p, p, false, p === current));
                        }

                        pager.appendChild(pageItem(current + 1, '»', current >= last, false));
                    }

                    function escapeHtml(str){
                        return String(str)
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#039;');
                    }

                    async function onEdit(e){
                        const id = this.dataset.id;
                        try{
                            const res = await fetch(`${apiBase}/${id}`, {credentials: 'same-origin'});
                            const json = await res.json();
                            const cat = json.data || json;
                            document.getElementById('categoryId').value = cat.category_id ?? cat.id;
                            document.getElementById('categoryName').value = cat.name || '';
                            document.getElementById('categoryDescription').value = cat.description || '';
                            document.getElementById('categoryModalTitle').textContent = 'Sửa danh mục';
                            showError('');
                            const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
                            modal.show();
                        }catch(err){
                            console.error(err);
                        }
                    }

                    async function onDelete(e){
                        const id = this.dataset.id;
                        if(!confirm('Bạn có chắc muốn xóa danh mục này?')) return;
                        try{
                            // Use web proxy route for DELETE so Laravel web auth + role middleware applies
                            const res = await fetch(`${webBase}/${id}`, {
                                method: 'DELETE',
                                credentials: 'same-origin',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                }
                            });
                            if(!res.ok){
                                const err = await res.json();
                                alert(err.message || 'Xóa thất bại');
                                return;
                            }
                            await fetchCategories();
                        }catch(err){
                            console.error(err);
                        }
                    }

                    // Save (create/update)
                    document.getElementById('saveCategoryBtn').addEventListener('click', async function(){
                        const id = document.getElementById('categoryId').value;
                        const name = document.getElementById('categoryName').value.trim();
                        const description = document.getElementById('categoryDescription').value.trim();
                        showError('');

                        if(!name){ showError('Tên danh mục không được để trống.'); return; }

                        const payload = { name, description };
                        // Use web proxy routes for create/update
                        const method = id ? 'PUT' : 'POST';
                        const url = id ? `${webBase}/${id}` : webBase;

                        try{
                            const res = await fetch(url, {
                                method,
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });

                                if(res.status === 422){
                                    const json = await res.json();
                                    const errs = json.errors || {};
                                    // field-level
                                    document.getElementById('errorName').textContent = (errs.name || []).join(' ');
                                    document.getElementById('errorDescription').textContent = (errs.description || []).join(' ');
                                    showError('');
                                    return;
                                }

                                if(!res.ok){
                                    const json = await res.json().catch(()=>({message: 'Lỗi server'}));
                                    showError(json.message || 'Lỗi khi lưu');
                                    return;
                                }

                            // success
                            const modalEl = document.getElementById('categoryModal');
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            modal.hide();
                            document.getElementById('categoryForm').reset();
                            document.getElementById('errorName').textContent = '';
                            document.getElementById('errorDescription').textContent = '';
                            await fetchCategories();
                        }catch(err){
                            console.error(err);
                            showError('Lỗi khi kết nối tới server');
                        }
                    });

                    // Prepare modal for 'Add'
                    document.getElementById('btnAddCategory').addEventListener('click', function(){
                        document.getElementById('categoryForm').reset();
                        document.getElementById('categoryId').value = '';
                        document.getElementById('categoryModalTitle').textContent = 'Thêm danh mục';
                        showError('');
                    });

                    // Attach search handlers
                    document.getElementById('btnSearch').addEventListener('click', function(){
                        const name = document.getElementById('searchName').value.trim();
                        fetchCategories({name});
                    });
                    document.getElementById('btnClearSearch').addEventListener('click', function(){
                        document.getElementById('searchName').value = '';
                        fetchCategories();
                    });

                    // Initial load
                    fetchCategories();
                })();
            </script>
            @endsection
        </div>
    </div>
</div>
@endsection
