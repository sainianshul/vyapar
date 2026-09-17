@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Products'],
                ]" />
                <h2 class="page-title">Products</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Add Product
                </a>
            </div>
        </div>
    </div>

    <div class="card">

        {{-- Toolbar --}}
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-3">

                {{-- Search --}}
                <div class="input-icon">
                    <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                    <input type="text" id="dt-search" class="form-control" style="width: 260px;"
                        placeholder="Search products..." />
                </div>

                {{-- Right Controls --}}
                <div class="d-flex align-items-center gap-2">

                    {{-- Refresh Button --}}
                    <button type="button" class="btn btn-icon btn-ghost-secondary" id="refresh-table-btn"
                        data-bs-toggle="tooltip" title="Refresh">
                        <i class="ti ti-refresh"></i>
                    </button>

                    {{-- Category Filter --}}
                    <select id="filter-category" class="form-select" style="width: 150px;">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    {{-- Condition Filter --}}
                    <select id="filter-condition" class="form-select" style="width: 120px;">
                        <option value="">Condition</option>
                        <option value="1">New</option>
                        <option value="2">Used</option>
                    </select>

                    {{-- Date Filter --}}
                    <div class="input-icon" style="width: 140px;">
                        <span class="input-icon-addon"><i class="ti ti-calendar"></i></span>
                        <input type="date" id="filter-date" class="form-control" title="Created Date">
                    </div>

                    {{-- Featured Filter --}}
                    <select id="filter-featured" class="form-select" style="width: 140px;">
                        <option value="">All Featured</option>
                        <option value="1">Featured Yes</option>
                        <option value="0">Featured No</option>
                    </select>

                    {{-- Status Filter --}}
                    <select id="filter-status" class="form-select" style="width: 130px;">
                        <option value="">All Status</option>
                        @foreach (\App\Models\Product::getStatusList() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">

            {{-- Loading Spinner --}}
            <div id="products-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            {{-- Table --}}
            <div id="products-table-wrapper" class="table-responsive d-none">
                <table id="products-table" class="table table-vcenter w-100">
                    <thead>
                        <tr>
                            <th class="w-1 text-nowrap">S.No</th>
                            <th class="text-nowrap">Product Name</th>
                            <th class="text-nowrap">Seller Name</th>
                            <th class="text-nowrap">Category</th>
                            <th class="text-nowrap">Used/New</th>
                            <th class="text-nowrap">Price</th>
                            <th class="text-nowrap">Minimum Quantity</th>
                            <th class="text-nowrap">City</th>
                            <th class="text-nowrap">Featured</th>
                            <th class="text-nowrap">Status</th>
                            <th class="text-nowrap">Created At</th>
                            <th class="text-end text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-nowrap"></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'products-empty',
                'title' => 'No products found',
                'subtitle' => 'Try adjusting your search or filters to find what you are looking for.'
            ])

        </div>
    </div>

    {{-- Status Update Modal --}}
    <div class="modal modal-blur fade" id="status-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="status-modal-form">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="status-modal-id">
                        <div class="mb-3">
                            <label class="form-label">Select Status</label>
                            <select id="status-modal-select" class="form-select">
                                @foreach (\App\Models\Product::getStatusList() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Featured Update Modal --}}
    <div class="modal modal-blur fade" id="featured-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="featured-modal-form">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Featured Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="featured-modal-id">
                        <div class="mb-3">
                            <label class="form-label">Is Featured?</label>
                            <select id="featured-modal-select" class="form-select">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
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

            let table = $('#products-table').DataTable({
                serverSide: true,
                processing: false,

                ajax: {
                    url: '{{ route('admin.products.data') }}',
                    data: function (d) {
                        d.status = $('#filter-status').val();
                        d.category_id = $('#filter-category').val();
                        d.is_featured = $('#filter-featured').val();
                        d.condition = $('#filter-condition').val();
                        d.created_date = $('#filter-date').val();
                    }
                },

                columns: [
                    { data: null, name: 'id', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
                    { data: 'title', name: 'title' },
                    { data: 'seller', name: 'seller', orderable: false, searchable: false },
                    { data: 'category', name: 'category', orderable: false, searchable: false },
                    { data: 'condition', name: 'condition', searchable: false },
                    { data: 'price', name: 'price' },
                    { data: 'minimum_quantity', name: 'minimum_quantity', searchable: false },
                    { data: 'city', name: 'city' },
                    { data: 'is_featured', name: 'is_featured' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],

                order: [[1, 'asc']],
                pageLength: 25,
                lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],

                dom:
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3 pt-3 flex-nowrap'" +
                    "<'col-sm-12 col-md-5'i>" +
                    "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-3'lp>>",

                language: {
                    emptyTable: ' ',
                    zeroRecords: ' ',
                    loadingRecords: ' ',
                    info: 'Showing _START_–_END_ of _TOTAL_ products',
                    infoEmpty: 'No products to show',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },

                initComplete: function () {
                    $('#products-loader').remove();
                    let total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#products-table-wrapper').addClass('d-none');
                        $('#products-empty').removeClass('d-none');
                    } else {
                        $('#products-empty').addClass('d-none');
                        $('#products-table-wrapper').removeClass('d-none');
                    }
                },

                drawCallback: function () {
                    if ($('#products-loader').length === 0) {
                        let total = this.api().page.info().recordsDisplay;
                        if (total === 0) {
                            $('#products-table-wrapper').addClass('d-none');
                            $('#products-empty').removeClass('d-none');
                        } else {
                            $('#products-empty').addClass('d-none');
                            $('#products-table-wrapper').removeClass('d-none');
                        }
                    }
                    $('[data-bs-toggle="tooltip"]').tooltip({ trigger: 'hover' });
                }
            });

            // Search
            let searchTimer;
            $('#dt-search').on('input', function () {
                clearTimeout(searchTimer);
                let query = $(this).val();
                searchTimer = setTimeout(function () {
                    table.search(query).draw();
                }, 400);
            });

            $('#filter-status, #filter-category, #filter-featured, #filter-condition, #filter-date').on('change', function () {
                table.ajax.reload();
            });

            // Refresh Button
            $('#refresh-table-btn').on('click', function () {
                $(this).tooltip('hide');
                table.ajax.reload(null, false);
                Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Refreshed' });
            });

            // Open Status Modal
            $(document).on('click', '.status-modal-btn', function (e) {
                e.preventDefault();
                let id = $(this).data('id');
                let currentStatus = $(this).data('status');
                
                $('#status-modal-id').val(id);
                $('#status-modal-select').val(currentStatus);
                $('#status-modal').modal('show');
            });

            // Submit Status Modal
            $('#status-modal-form').on('submit', function (e) {
                e.preventDefault();
                let id = $('#status-modal-id').val();
                let status = $('#status-modal-select').val();
                let submitBtn = $(this).find('button[type="submit"]');
                let originalText = submitBtn.html();
                
                submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...').prop('disabled', true);

                $.ajax({
                    url: '/admin/products/' + id + '/status',
                    type: 'POST',
                    data: { status: status, _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        $('#status-modal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: res.message });
                    },
                    error: function () {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to update' });
                    },
                    complete: function () {
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Open Featured Modal
            $(document).on('click', '.featured-modal-btn', function (e) {
                e.preventDefault();
                let id = $(this).data('id');
                let isFeatured = $(this).data('featured');
                
                $('#featured-modal-id').val(id);
                $('#featured-modal-select').val(isFeatured);
                $('#featured-modal').modal('show');
            });

            // Submit Featured Modal
            $('#featured-modal-form').on('submit', function (e) {
                e.preventDefault();
                let id = $('#featured-modal-id').val();
                let featured = $('#featured-modal-select').val();
                let submitBtn = $(this).find('button[type="submit"]');
                let originalText = submitBtn.html();
                
                submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...').prop('disabled', true);

                $.ajax({
                    url: '/admin/products/' + id + '/featured',
                    type: 'POST',
                    data: { is_featured: featured, _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        $('#featured-modal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: res.message });
                    },
                    error: function () {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to update' });
                    },
                    complete: function () {
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Delete
            $(document).on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
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
                    $.post('/admin/products/' + id, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
                        .done(function (res) {
                            table.ajax.reload(null, false);
                            Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: res.message });
                        })
                        .fail(function (xhr) {
                            let msg = xhr.responseJSON?.message || 'Something went wrong';
                            Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 2500, icon: 'error', title: msg });
                        });
                });
            });

        });
    </script>
@endpush
