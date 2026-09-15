@extends('admin.layouts.app')

@section('title', 'FAQs')

@section('content')

    <x-breadcrumb :items="[
        ['label' => 'Support', 'url' => route('admin.support.index')],
        ['label' => 'FAQs'],
    ]" />

    <div class="card shadow-sm">

        {{-- Toolbar --}}
        <div class="card-header border-0 pt-5 pb-3">
            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-3">

                {{-- Search --}}
                <div class="d-flex align-items-center position-relative">
                    <i class="ti ti-search text-body position-absolute ms-4">
                        
                        
                    </i>
                    <input
                        type="text"
                        id="dt-search"
                        class="form-control form-control border border text-body w-250px ps-11 pe-4 small fw-semibold shadow-sm"
                        placeholder="Search FAQs..."
                    />
                </div>

                {{-- Right Controls --}}
                <div class="d-flex align-items-center gap-2">

                    {{-- Category Filter --}}
                    <div style="width: 200px;">
                        <div class="position-relative">
                            <i class="ti ti-filter text-body position-absolute top-50 start-0 translate-middle-y ms-4">
                                
                                
                            </i>
                            <select
                                id="filter-category"
                                class="form-select form-select border border text-body form-select-sm fw-semibold ps-11 shadow-sm"
                                data-placeholder="All Categories"
                            >
                                <option></option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Status Filter --}}
                    <div style="width: 145px;">
                        <div class="position-relative">
                            <i class="ti ti-filter text-body position-absolute top-50 start-0 translate-middle-y ms-4">
                                
                                
                            </i>
                            <select
                                id="filter-status"
                                class="form-select form-select border border text-body form-select-sm fw-semibold ps-11 shadow-sm"
                                data-placeholder="All Status"
                            >
                                <option></option>
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Add FAQ --}}
                    <a href="{{ route('admin.support.faqs.create') }}" class="btn btn-sm btn-primary fw-semibold ">
                        <i class="ti ti-plus me-1">
                            
                            
                            
                        </i>
                        Add FAQ
                    </a>

                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body py-4">

            {{-- Loading Spinner --}}
            <div id="faqs-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            <div id="faqs-table-wrapper" class="table-responsive d-none">
                <table id="faqs-table" class="table align-middle w-100">
                    <thead>
                        <tr class="text-start text-body fw-medium small text-uppercase border-bottom border border-1">
                            <th class="w-50px">Sr. No.</th>
                            <th class="min-w-250px">Question</th>
                            <th class="min-w-150px">Category</th>
                            <th class="min-w-120px">Status</th>
                            <th class="min-w-150px">Created At</th>
                            <th class="text-end min-w-100px pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', ['id' => 'faqs-empty'])

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
            let table = $('#faqs-table').DataTable({
                serverSide: true,
                processing: false,
                ajax: {
                    url: '{{ route('admin.support.faqs.data') }}',
                    data: function (d) {
                        d.status = $('#filter-status').val();
                        d.support_category_id = $('#filter-category').val();
                    }
                },
                columns: [
                    { data: null, name: 'id', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
                    { data: 'question', name: 'question' },
                    { data: 'category', name: 'supportCategory.name' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end pe-3' },
                ],
                order: [[0, 'desc']],
                pageLength: 15,
                lengthMenu: [[10, 15, 25, 50], [10, 15, 25, 50]],
                dom:
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3 pt-3 flex-nowrap'" +
                    "<'col-sm-12 col-md-5'i>" +
                    "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-3'lp>>",
                language: {
                    emptyTable: ' ',
                    zeroRecords: ' ',
                    loadingRecords: ' ',
                    info: 'Showing _START_–_END_ of _TOTAL_ FAQs',
                    infoEmpty: 'No FAQs to show',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },
                initComplete: function () {
                    $('#faqs-loader').remove();
                    $('#faqs-table-wrapper').removeClass('d-none');
                },
                drawCallback: function () {
                    let total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#faqs-table-wrapper').addClass('d-none');
                        $('#faqs-empty').removeClass('d-none');
                    } else {
                        $('#faqs-empty').addClass('d-none');
                        $('#faqs-table-wrapper').removeClass('d-none');
                    }
                    $('[data-bs-toggle="tooltip"]').tooltip({ trigger: 'hover' });
                }
            });

            let searchTimer;
            $('#dt-search').on('input', function () {
                clearTimeout(searchTimer);
                let query = $(this).val();
                searchTimer = setTimeout(function () {
                    table.search(query).draw();
                }, 400);
            });

            $('#filter-status, #filter-category').on('change', function () {
                table.ajax.reload();
            });
        });

        function confirmDelete(url) {
            Swal.fire({
                title: 'Delete FAQ?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' },
                buttonsStyling: false,
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.post(url, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
                .done(function () {
                    $('#faqs-table').DataTable().ajax.reload(null, false);
                    toastr.success('FAQ deleted.');
                })
                .fail(function () {
                    toastr.error('Something went wrong.');
                });
            });
        }
    </script>
@endpush
