@extends('admin.layouts.app')

@section('title', 'Banners')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Banners'],
                ]" />
                <h2 class="page-title">Banners</h2>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary btn-add-banner">
                    <i class="ti ti-plus me-1"></i>Add Banner
                </button>
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
                        placeholder="Search banners..." />
                </div>

                {{-- Right Controls --}}
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-icon btn-ghost-secondary" id="refresh-table-btn"
                        data-bs-toggle="tooltip" title="Refresh">
                        <i class="ti ti-refresh"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">
            <div id="banners-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            <div id="banners-table-wrapper" class="table-responsive d-none">
                <table id="banners-table" class="table table-vcenter w-100">
                    <thead>
                        <tr>
                            <th class="w-1">S.No</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'banners-empty',
                'title' => 'No banners found',
                'subtitle' => 'Try adjusting your search or add a new banner.'
            ])
        </div>
    </div>

    {{-- Banner Modal (Add/Edit) --}}
    <div class="modal modal-blur fade" id="banner-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="banner-form" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="banner-modal-title">Add Banner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="banner-id" name="id">
                        
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" id="banner-name" name="name" placeholder="Banner Name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Type</label>
                            <select class="form-select" id="banner-type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="1">Category</option>
                                <option value="2">Product</option>
                                <option value="3">Seller</option>
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="reference-category-wrapper">
                            <label class="form-label required">Select Category</label>
                            <select class="form-select reference-input" id="reference-category">
                                <option value="">Choose Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="reference-product-wrapper">
                            <label class="form-label required">Product ID</label>
                            <input type="number" class="form-control reference-input" id="reference-product" placeholder="Enter Product ID">
                        </div>

                        <div class="mb-3 d-none" id="reference-seller-wrapper">
                            <label class="form-label required">Select Seller</label>
                            <select class="form-select reference-input" id="reference-seller">
                                <option value="">Choose Seller</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="reference_id" id="banner-reference-id">

                        <div class="mb-3">
                            <label class="form-label required">Status</label>
                            <select class="form-select" id="banner-status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" id="banner-image" name="image" accept="image/*">
                            <div class="mt-2" id="banner-image-preview"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="save-banner-btn">Save Banner</button>
                    </div>
                </form>
            </div>
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
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="draft">Draft</option>
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
            let table = $('#banners-table').DataTable({
                serverSide: true,
                processing: false,
                ajax: '{{ route('admin.banners.data') }}',
                columns: [
                    { data: null, name: 'id', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
                    { data: 'image', name: 'image', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'type', name: 'type' },
                    { data: 'status', name: 'status' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],
                order: [[2, 'asc']],
                pageLength: 25,
                dom: "<'row'<'col-12'tr>>" +
                     "<'row align-items-center mt-3 pt-3 flex-nowrap'" +
                     "<'col-sm-12 col-md-5'i>" +
                     "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-3'lp>>",
                language: {
                    emptyTable: ' ', zeroRecords: ' ', loadingRecords: ' ',
                    info: 'Showing _START_–_END_ of _TOTAL_ banners',
                    infoEmpty: 'No banners to show', infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: { previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>' },
                },
                initComplete: function () {
                    $('#banners-loader').remove();
                    let total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#banners-table-wrapper').addClass('d-none');
                        $('#banners-empty').removeClass('d-none');
                    } else {
                        $('#banners-empty').addClass('d-none');
                        $('#banners-table-wrapper').removeClass('d-none');
                    }
                },
                drawCallback: function () {
                    if ($('#banners-loader').length === 0) {
                        let total = this.api().page.info().recordsDisplay;
                        if (total === 0) {
                            $('#banners-table-wrapper').addClass('d-none');
                            $('#banners-empty').removeClass('d-none');
                        } else {
                            $('#banners-empty').addClass('d-none');
                            $('#banners-table-wrapper').removeClass('d-none');
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

            // Refresh Button
            $('#refresh-table-btn').on('click', function () {
                $(this).tooltip('hide');
                table.ajax.reload(null, false);
                Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Refreshed' });
            });

            // Banner Type Change
            $('#banner-type').on('change', function() {
                let val = $(this).val();
                $('#reference-category-wrapper, #reference-product-wrapper, #reference-seller-wrapper').addClass('d-none');
                $('.reference-input').prop('required', false).val('');
                $('#banner-reference-id').val('');

                if (val == 1) {
                    $('#reference-category-wrapper').removeClass('d-none');
                    $('#reference-category').prop('required', true);
                } else if (val == 2) {
                    $('#reference-product-wrapper').removeClass('d-none');
                    $('#reference-product').prop('required', true);
                } else if (val == 3) {
                    $('#reference-seller-wrapper').removeClass('d-none');
                    $('#reference-seller').prop('required', true);
                }
            });

            // Set Reference ID before submit
            $('.reference-input').on('change input', function() {
                $('#banner-reference-id').val($(this).val());
            });

            // Add Banner Modal
            $('.btn-add-banner').on('click', function() {
                $('#banner-form')[0].reset();
                $('#banner-id').val('');
                $('#banner-reference-id').val('');
                $('#banner-image-preview').html('');
                $('#banner-type').trigger('change');
                $('#banner-modal-title').text('Add Banner');
                $('#banner-modal').modal('show');
            });

            // Edit Banner Modal
            $(document).on('click', '.btn-edit', function() {
                $('#banner-form')[0].reset();
                let id = $(this).data('id');
                let name = $(this).data('name');
                let type = $(this).data('type');
                let status = $(this).data('status');
                let image = $(this).data('image');
                let ref_id = $(this).data('reference_id');

                $('#banner-id').val(id);
                $('#banner-name').val(name);
                $('#banner-type').val(type).trigger('change');
                $('#banner-status').val(status);
                $('#banner-reference-id').val(ref_id);

                if (type == 1) {
                    $('#reference-category').val(ref_id);
                } else if (type == 2) {
                    $('#reference-product').val(ref_id);
                } else if (type == 3) {
                    $('#reference-seller').val(ref_id);
                }
                
                if (image) {
                    $('#banner-image-preview').html('<img src="'+image+'" class="img-thumbnail mt-2" style="max-height: 80px">');
                } else {
                    $('#banner-image-preview').html('');
                }

                $('#banner-modal-title').text('Edit Banner');
                $('#banner-modal').modal('show');
            });

            // Save Banner
            $('#banner-form').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#banner-id').val();
                let url = id ? '/admin/banners/' + id : '/admin/banners';
                if (id) {
                    formData.append('_method', 'PUT');
                }
                formData.append('_token', '{{ csrf_token() }}');

                let btn = $('#save-banner-btn');
                let originalText = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#banner-modal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: res.message });
                    },
                    error: function(xhr) {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 2500, icon: 'error', title: 'Error saving banner' });
                    },
                    complete: function() {
                        btn.html(originalText).prop('disabled', false);
                    }
                });
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
                
                submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...').prop('disabled', true);

                $.ajax({
                    url: '/admin/banners/' + id + '/status',
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

            // Delete Banner
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
                    $.post('/admin/banners/' + id, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
                        .done(function (res) {
                            table.ajax.reload(null, false);
                            Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: res.message });
                        })
                        .fail(function (xhr) {
                            Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 2500, icon: 'error', title: 'Delete failed' });
                        });
                });
            });

        });
    </script>
@endpush
