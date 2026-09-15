@extends('admin.layouts.app')

@section('title', 'Nurse Payouts')

@section('content')

    <x-breadcrumb :items="[
        ['label' => 'Nurse Payouts'],
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
                        placeholder="Search by ID or Nurse Name..."
                    />
                </div>

                {{-- Right Controls --}}
                <div class="d-flex align-items-center gap-2">

                    {{-- Refresh Button --}}
                    <button type="button" class="btn btn btn-ghost-secondary border border w-35px h-35px" id="refresh-table-btn" data-bs-toggle="tooltip" title="Refresh">
                        <i class="ti ti-refresh fs-3"></i>
                    </button>

                    {{-- Date Filter --}}
                    <div style="width: 175px;">
                        <div class="position-relative">
                            <i class="ti ti-calendar text-body position-absolute top-50 start-0 translate-middle-y ms-4">
                                
                                
                            </i>
                            <input
                                type="text"
                                class="form-control form-control border border text-body form-control-sm fw-semibold ps-11 pe-8 shadow-sm cursor-pointer"
                                placeholder="Filter by Date"
                                id="filter-date"
                            />
                            <i class="ti ti-x fs-3 text-muted position-absolute top-50 end-0 translate-middle-y me-2 cursor-pointer d-none" id="clear-date-btn">
                                
                                
                            </i>
                        </div>
                    </div>

                    {{-- Status Filter --}}
                    <div style="width: 160px;">
                        <div class="position-relative">
                            <i class="ti ti-filter text-body position-absolute top-50 start-0 translate-middle-y ms-4">
                                
                                
                            </i>
                            <select
                                id="filter-status"
                                class="form-select form-select border border text-body form-select-sm fw-semibold ps-11 shadow-sm"
                                data-placeholder="All Statuses"
                            >
                                <option></option>
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Body --}}
            {{-- Loading Spinner --}}
            <div id="payouts-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            <div id="payouts-table-wrapper" class="table-responsive d-none">
                <table
                    id="payouts-table"
                    class="table align-middle w-100"
                >
                    <thead>
                        <tr class="text-start text-body fw-medium small text-uppercase border-bottom border border-1">
                            <th class="w-50px">ID</th>
                            <th class="min-w-200px">Nurse</th>
                            <th class="min-w-120px">Amount</th>
                            <th class="min-w-200px">Bank Details</th>
                            <th class="min-w-120px">Status</th>
                            <th class="min-w-150px">Processed Info</th>
                            <th class="min-w-130px">Created At</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'payouts-empty'
            ])

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
            let table = $('#payouts-table').DataTable({
                serverSide: true,
                processing: false,
                ajax: {
                    url: '{{ route("admin.payments.payouts-data") }}',
                    data: function (d) {
                        d.status = $('#filter-status').val();
                        d.date = $('#filter-date').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'user', name: 'user', orderable: false, searchable: false },
                    { data: 'amount', name: 'amount' },
                    { data: 'bank_details', name: 'bank_details', orderable: false, searchable: false },
                    { data: 'status', name: 'status' },
                    { data: 'processed_info', name: 'processed_info', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                ],
                order: [[6, 'desc']],
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
                    info: 'Showing _START_–_END_ of _TOTAL_ payouts',
                    infoEmpty: 'No payouts to show',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },
                initComplete: function () {
                    $('#payouts-loader').remove();
                    $('#payouts-table-wrapper').removeClass('d-none');
                },
                drawCallback: function () {
                    let total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#payouts-table-wrapper').addClass('d-none');
                        $('#payouts-empty').removeClass('d-none');
                    } else {
                        $('#payouts-empty').addClass('d-none');
                        $('#payouts-table-wrapper').removeClass('d-none');
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

            $('#filter-status').on('change', function () {
                table.ajax.reload();
            });

            $('#refresh-table-btn').on('click', function () {
                $(this).tooltip('hide');
                table.ajax.reload(null, false);
                Swal.fire({
                    toast: true,
                    position: 'top',
                    showConfirmButton: false,
                    timer: 1500,
                    icon: 'success',
                    title: 'Data refreshed successfully'
                });
            });

            let fp = $('#filter-date').flatpickr({
                altInput: true,
                altFormat: "d M Y",
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr, instance) {
                    if (dateStr) {
                        $('#clear-date-btn').removeClass('d-none');
                    } else {
                        $('#clear-date-btn').addClass('d-none');
                    }
                    table.ajax.reload();
                }
            });

            $('#clear-date-btn').on('click', function(e) {
                e.stopPropagation();
                fp.clear();
            });
        });
    </script>
@endpush
