@extends('admin.layouts.app')

@section('title', $category->name . ' — Category Details')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Categories', 'url' => route('admin.categories.index')],
                    ['label' => 'Category Details'],
                ]" />
                <h2 class="page-title">Category Details</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
                <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-primary">
                    <i class="ti ti-pencil me-1"></i>Edit Category
                </a>
            </div>
        </div>
    </div>

    {{-- Category Header Card --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    {!! $category->icon_html !!}
                </div>
                <div class="col">
                    <div class="d-flex align-items-center mb-1 flex-wrap gap-2">
                        <h2 class="mb-0 me-1">{{ $category->name }}</h2>
                        <span class="badge bg-{{ $category->status_color }}-lt">
                            <i class="{{ $category->status_icon }} me-1"></i>{{ $category->status_name }}
                        </span>
                        @if($category->is_featured)
                            <span class="badge bg-yellow-lt"><i class="ti ti-star-filled me-1"></i>Featured</span>
                        @endif
                        <span class="badge bg-primary-lt">Level {{ $category->level }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-3 text-secondary small mt-1">
                        <span><i class="ti ti-link me-1"></i>Slug: <code>{{ $category->slug }}</code></span>
                        @if($category->parent)
                            <span><i class="ti ti-corner-left-up me-1"></i>Parent: <a href="{{ route('admin.categories.show', $category->parent_id) }}" class="text-reset text-decoration-none">{{ $category->parent->name }}</a></span>
                        @endif
                        <span><i class="ti ti-list-numbers me-1"></i>Sort Order: {{ $category->sort_order }}</span>
                        <span><i class="ti ti-clock me-1"></i>Created: {{ $category->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Card --}}
    <div class="card mb-3">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-overview" role="tab">
                        <i class="ti ti-info-circle me-1"></i>Overview
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-subcategories" role="tab">
                        <i class="ti ti-list-tree me-1"></i>Subcategories <span class="badge bg-secondary-lt ms-1">{{ $category->children()->count() }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-products" role="tab">
                        <i class="ti ti-package me-1"></i>Products
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                {{-- Tab: Overview --}}
                <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <h3 class="mb-3">Category Details</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Name</div>
                                    <div class="datagrid-content">{{ $category->name }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Slug</div>
                                    <div class="datagrid-content"><code>{{ $category->slug }}</code></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Parent Category</div>
                                    <div class="datagrid-content">
                                        @if($category->parent)
                                            <a href="{{ route('admin.categories.show', $category->parent_id) }}">{{ $category->parent->name }}</a>
                                        @else
                                            <span class="text-muted">None (Root Category)</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Hierarchy Path</div>
                                    <div class="datagrid-content">
                                        @if($category->ancestors->isNotEmpty())
                                            @foreach($category->ancestors as $ancestor)
                                                {{ $ancestor->name }} &raquo; 
                                            @endforeach
                                        @endif
                                        <strong>{{ $category->name }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h3 class="mb-3">System Info</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Status</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-{{ $category->status_color }}-lt">
                                            <i class="{{ $category->status_icon }} me-1"></i>{{ $category->status_name }}
                                        </span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Featured</div>
                                    <div class="datagrid-content">
                                        @if($category->is_featured)
                                            <span class="badge bg-yellow-lt"><i class="ti ti-star-filled me-1"></i>Yes</span>
                                        @else
                                            <span class="badge bg-secondary-lt">No</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Sort Order</div>
                                    <div class="datagrid-content">{{ $category->sort_order }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Level</div>
                                    <div class="datagrid-content">{{ $category->level }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($category->description)
                        <div class="mt-4">
                            <h3 class="mb-2">Description</h3>
                            <div class="card card-body bg-light border-0">
                                <p class="mb-0" style="white-space: pre-wrap;">{{ $category->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Tab: Subcategories --}}
                <div class="tab-pane fade" id="tab-subcategories" role="tabpanel">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h3 class="mb-1">Subcategories</h3>
                            <p class="text-secondary mb-0">Direct subcategories of this category.</p>
                        </div>
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i>Add Subcategory
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table id="subcategories-table" class="table table-vcenter card-table w-100">
                            <thead>
                                <tr>
                                    <th class="w-1">S.No</th>
                                    <th>Category Info</th>
                                    <th>Sort Order</th>
                                    <th>Featured</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab: Products --}}
                <div class="tab-pane fade" id="tab-products" role="tabpanel">
                    <div class="mb-3">
                        <h3 class="mb-1">Products</h3>
                        <p class="text-secondary mb-0">Products linked to this category and all its subcategories.</p>
                    </div>
                    <div class="table-responsive">
                        <table id="products-table" class="table table-vcenter card-table w-100">
                            <thead>
                                <tr>
                                    <th class="w-1">S.No</th>
                                    <th>Product</th>
                                    <th>Seller</th>
                                    <th>Condition</th>
                                    <th>Price</th>
                                    <th>Featured</th>
                                    <th>Status</th>
                                    <th>Posted On</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('datatables_css')
    @include('admin.layouts.partials._datatable-cdn-css')
@endpush

@push('datatables_js')
    @include('admin.layouts.partials._datatable-cdn-js')

    <script>
        $(function () {

            // Shared options
            const dtLanguage = {
                emptyTable: 'No records found',
                zeroRecords: 'No matching records found',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'Showing 0 to 0 of 0 entries',
                infoFiltered: '(filtered from _MAX_)',
                lengthMenu: 'Show _MENU_',
                paginate: {
                    previous: '<i class="ti ti-chevron-left"></i>',
                    next: '<i class="ti ti-chevron-right"></i>',
                },
            };

            const dtDom = "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 d-flex justify-content-end'f>>" +
                          "<'row'<'col-12'tr>>" +
                          "<'row align-items-center mt-3 pt-3 flex-nowrap'" +
                          "<'col-sm-12 col-md-5'i>" +
                          "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center'p>>";

            // Initialize Subcategories Table
            let subcategoriesTable = $('#subcategories-table').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: '{{ route('admin.categories.data') }}',
                    data: function (d) {
                        d.parent_id = '{{ $category->id }}';
                    }
                },
                columns: [
                    { data: null, name: 'id', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
                    { data: 'name', name: 'name', render: function(data, type, row) {
                        // Link name to the category show page instead of just returning raw HTML
                        return '<a href="/admin/categories/' + row.id + '" class="text-reset text-decoration-none">' + data + '</a>';
                    }},
                    { data: 'sort_order', name: 'sort_order' },
                    { data: 'is_featured', name: 'is_featured' },
                    { data: 'status', name: 'status' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],
                order: [[2, 'asc']], // Order by sort_order
                pageLength: 10,
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                dom: dtDom,
                language: dtLanguage,
                drawCallback: function () {
                    $('[data-bs-toggle="tooltip"]').tooltip({ trigger: 'hover' });
                }
            });

            // Initialize Products Table
            let productsTable = $('#products-table').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: '{{ route('admin.products.data') }}',
                    data: function (d) {
                        d.category_id = '{{ $category->id }}';
                    }
                },
                columns: [
                    { data: null, name: 'id', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
                    { data: 'title', name: 'title' },
                    { data: 'seller', name: 'seller', orderable: false, searchable: false },
                    { data: 'condition', name: 'condition' },
                    { data: 'price', name: 'price' },
                    { data: 'is_featured', name: 'is_featured' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],
                order: [[7, 'desc']], // Order by created_at desc
                pageLength: 10,
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                dom: dtDom,
                language: dtLanguage,
                drawCallback: function () {
                    $('[data-bs-toggle="tooltip"]').tooltip({ trigger: 'hover' });
                }
            });
            
            // Delete Handlers for generic btn-delete logic
            $(document).on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let isCategory = $(this).closest('table').attr('id') === 'subcategories-table';
                let url = isCategory ? '/admin/categories/' + id : '/admin/products/' + id;
                let tableToReload = isCategory ? subcategoriesTable : productsTable;
                
                $(this).tooltip('hide');
                
                Swal.fire({
                    title: 'Delete "' + name + '"?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    $.post(url, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
                        .done(function (res) {
                            tableToReload.ajax.reload(null, false);
                            Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: res.message });
                        })
                        .fail(function (xhr) {
                            let msg = xhr.responseJSON?.message || 'Something went wrong';
                            Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 2500, icon: 'error', title: msg });
                        });
                });
            });

            // Need to fix tabs so tables render correctly if tab is switched
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
            });

        });
    </script>
@endpush
