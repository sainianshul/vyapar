@extends('admin.layouts.app')

@section('title', 'Login History')

@section('content')

    <x-breadcrumb :items="[
        ['label' => 'System'],
        ['label' => 'Login History'],
    ]" />

    <div class="card">
        {{-- Toolbar --}}
        <div class="card-header d-flex justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex gap-2">
                <div class="input-icon" style="width: 250px;">
                    <span class="input-icon-addon">
                        <i class="ti ti-search"></i>
                    </span>
                    <input type="text" id="dt-search" class="form-control" placeholder="Search by name or phone...">
                </div>
            </div>
            
            <div class="d-flex gap-2 align-items-center">
                <select id="filter-status" class="form-select w-auto">
                    <option value="">All Status</option>
                    <option value="1">Success</option>
                    <option value="0">Failed</option>
                </select>

                <button type="button" id="btn-empty-logs" class="btn btn-outline-danger">
                    <i class="ti ti-trash me-2"></i>Empty Logs
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">

            {{-- Loading Spinner --}}
            <div id="login-history-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            <div id="login-history-table-wrapper" class="table-responsive d-none">
                <table id="login-history-table" class="table table-vcenter w-100">
                    <thead>
                        <tr>
                            <th class="w-1">S.No</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Phone Number</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th>Login Time</th>
                            <th class="text-end pe-3">Options</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'login-history-empty'
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

            var table = $('#login-history-table').DataTable({
                serverSide: true,
                processing: false,
                ajax: {
                    url: '{{ route('admin.login-history.data') }}',
                    data: function (d) {
                        d.status = $('#filter-status').val();
                    }
                },

                columns: [
                    {
                        data: null,
                        name: 'id',
                        render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; },
                        orderable: false,
                        searchable: false
                    },

                    {
                        data: 'user_name',
                        name: 'user_name',
                        orderable: false
                    },

                    {
                        data: 'user_type',
                        name: 'user_type',
                        orderable: false,
                        searchable: false
                    },

                    {
                        data: 'user_phone',
                        name: 'user_phone',
                        orderable: false
                    },

                    {
                        data: 'ip',
                        name: 'ip',
                        searchable: false
                    },

                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },

                    {
                        data: 'logged_in_at',
                        name: 'logged_in_at'
                    },

                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end pe-3'
                    },
                ],

                order: [[5, 'desc']],
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
                    info: 'Showing _START_–_END_ of _TOTAL_ login records',
                    infoEmpty: 'No login records to show',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },

                initComplete: function () {
                    $('#login-history-loader').remove();
                    $('#login-history-table-wrapper').removeClass('d-none');
                },

                drawCallback: function () {

                    var total = this.api().page.info().recordsDisplay;

                    if (total === 0) {

                        $('#login-history-table-wrapper').addClass('d-none');

                        $('#login-history-empty').removeClass('d-none');

                    } else {

                        $('#login-history-empty').addClass('d-none');

                        $('#login-history-table-wrapper').removeClass('d-none');
                    }
                }
            });

            // ── Search ─────────────────────────────────────────────
            var searchTimer;

            $('#dt-search').on('input', function () {

                clearTimeout(searchTimer);

                var q = $(this).val();

                searchTimer = setTimeout(function () {

                    table.search(q).draw();

                }, 400);
            });

            // ── Filter ─────────────────────────────────────────────
            $('#filter-status').on('change', function () {

                table.ajax.reload();
            });



            // ── Empty Logs ─────────────────────────────────────────
            $('#btn-empty-logs').on('click', function () {

                Swal.fire({
                    title: 'Empty Login History?',
                    text: 'All login history records will be permanently removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Empty Logs',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-light ms-2'
                    },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.post('{{ route('admin.login-history.empty') }}', {

                        _token: '{{ csrf_token() }}'

                    })

                    .done(function () {

                        table.ajax.reload();

                        toastr.success('Login history cleared successfully.');

                    })

                    .fail(function () {

                        toastr.error('Something went wrong.');
                    });
                });
            });

        });

    </script>

@endpush



