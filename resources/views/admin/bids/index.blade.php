@extends('admin.layouts.app')

@section('title', 'Bids')

@section('content')

    <x-breadcrumb :items="[
        ['label' => 'Bids', 'url' => route('admin.bids.index')],
        ['label' => $title ?? 'All Bids'],
    ]" />

    <div class="card shadow-sm">

        {{-- Toolbar --}}
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-3">

                {{-- Search --}}
                <div class="input-icon">
                    <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                    <input
                        type="text"
                        id="dt-search"
                        class="form-control"
                        style="width: 260px;"
                        placeholder="Search bids..."
                    />
                </div>

                {{-- Right Controls --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">

                    {{-- Refresh Button --}}
                    <button type="button" class="btn btn-icon btn-ghost-secondary" id="refresh-table-btn" data-bs-toggle="tooltip" title="Refresh">
                        <i class="ti ti-refresh"></i>
                    </button>

                    {{-- Date Filter --}}
                    <div class="input-icon" style="width: 175px;">
                        <span class="input-icon-addon"><i class="ti ti-calendar"></i></span>
                        <input
                            type="text"
                            class="form-control cursor-pointer"
                            placeholder="Filter by Date"
                            id="filter-date"
                        />
                        <span class="input-icon-addon cursor-pointer d-none" id="clear-date-btn" title="Clear Date">
                            <i class="ti ti-x"></i>
                        </span>
                    </div>

                    @if(!isset($hideStatusFilter))
                    {{-- Status Filter --}}
                    <select id="filter-status" class="form-select" style="width: 160px;">
                        <option value="">All Status</option>
                        @foreach (\App\Models\RequestBid::getStatusList() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body py-4">

            {{-- Loading Spinner --}}
            <div id="bids-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            <div id="bids-table-wrapper" class="table-responsive d-none">
                <table
                    id="bids-table"
                    class="table table-vcenter w-100"
                >
                    <thead>
                        <tr>
                            <th class="w-1">ID</th>
                            <th>Care Request</th>
                            <th>Nurse</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'bids-empty'
            ])

        </div>
    </div>

@endsection

{{-- DataTables Bundle --}}
@push('datatables_css')
    @include('admin.layouts.partials._datatable-cdn-css')
@endpush

@push('datatables_js')
    @include('admin.layouts.partials._datatable-cdn-js')

    <script>
        $(function () {
            // ── Init ──────────────────────────────────────────────────────────
            let table = $('#bids-table').DataTable({
                serverSide: true,
                processing: false,
                ajax: {
                    url: '{!! $dataUrl ?? route("admin.bids.data") !!}',
                    data: function (d) {
                        if ($('#filter-status').length) {
                            d.status = $('#filter-status').val();
                        }
                        d.date = $('#filter-date').val();
                    }
                },
                columns: [
                    { data: null, name: 'id', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
                    { data: 'care_request', name: 'care_request', orderable: false, searchable: false },
                    { data: 'nurse', name: 'nurse', orderable: false, searchable: false },
                    { data: 'amount', name: 'total_amount' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end pe-3' },
                ],
                order: [[5, 'desc']],
                pageLength: 15,
                lengthMenu: [[10, 15, 25, 50], [10, 15, 25, 50]],
                dom:
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3 pt-3 gy-2'" +
                    "<'col-sm-12 col-md-5'i>" +
                    "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-3'lp>>",
                language: {
                    emptyTable: ' ',
                    zeroRecords: ' ',
                    loadingRecords: ' ',
                    info: 'Showing _START_–_END_ of _TOTAL_ bids',
                    infoEmpty: 'No bids to show',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },
                initComplete: function () {
                    $('#bids-loader').remove();
                    $('#bids-table-wrapper').removeClass('d-none');
                },
                drawCallback: function () {
                    let total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#bids-table-wrapper').addClass('d-none');
                        $('#bids-empty').removeClass('d-none');
                    } else {
                        $('#bids-empty').addClass('d-none');
                        $('#bids-table-wrapper').removeClass('d-none');
                    }
                    $('[data-bs-toggle="tooltip"]').tooltip({ trigger: 'hover' });
                }
            });

            // ── Search ───────────────────────────────────────────────────────
            let searchTimer;
            $('#dt-search').on('input', function () {
                clearTimeout(searchTimer);
                let query = $(this).val();
                searchTimer = setTimeout(function () {
                    table.search(query).draw();
                }, 400);
            });

            // ── Filters ─────────────────────────────────────────────────────
            $('#filter-status').on('change', function () {
                table.ajax.reload();
            });

            // ── Refresh Button ───────────────────────────────────────────────
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
