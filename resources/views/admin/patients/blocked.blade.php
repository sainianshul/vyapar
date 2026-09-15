@extends('admin.layouts.app')

@section('title', 'Blocked Patients')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'People'],
                    ['label' => 'Patients', 'url' => route('admin.patients.index')],
                    ['label' => 'Blocked Patients'],
                ]" />
                <h2 class="page-title">Blocked Patients</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-users me-1"></i>All Patients
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
                        placeholder="Search blocked patients..." />
                </div>

                {{-- Refresh --}}
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

            <div id="patients-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            <div id="patients-table-wrapper" class="table-responsive d-none">

                <table id="patients-table" class="table table-vcenter w-100">
                    <thead>
                        <tr>
                            <th class="w-1">S.No</th>
                            <th>Patient</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Blocked Reason</th>
                            <th>Blocked Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'patients-empty'
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

            // ── Init ──────────────────────────────────────────────────────────
            let table = $('#patients-table').DataTable({
                serverSide: true,
                processing: false,

                ajax: {
                    url: '{{ route('admin.patients.blocked.data') }}'
                },

                columns: [
                    { data: null, name: 'id', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email', orderable: false },
                    { data: 'phone', name: 'phone', orderable: false },
                    { data: 'status', name: 'status' },
                    { data: 'blocked_reason', name: 'blocked_reason' },
                    { data: 'blocked_at', name: 'blocked_at' },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    },
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
                    info: 'Showing _START_–_END_ of _TOTAL_ blocked patients',
                    infoEmpty: 'No blocked patients',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },

                initComplete: function () {
                    $('#patients-loader').remove();
                    let total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#patients-table-wrapper').addClass('d-none');
                        $('#patients-empty').removeClass('d-none');
                    } else {
                        $('#patients-empty').addClass('d-none');
                        $('#patients-table-wrapper').removeClass('d-none');
                    }
                },

                drawCallback: function () {
                    if ($('#patients-loader').length === 0) {
                        let total = this.api().page.info().recordsDisplay;
                        if (total === 0) {
                            $('#patients-table-wrapper').addClass('d-none');
                            $('#patients-empty').removeClass('d-none');
                        } else {
                            $('#patients-empty').addClass('d-none');
                            $('#patients-table-wrapper').removeClass('d-none');
                        }
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

            // ── Delete ───────────────────────────────────────────────────────
            $(document).on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Blocked Patient?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-light ms-2'
                    },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    $.post('/admin/patients/' + id, {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    }).done(function () {
                        table.ajax.reload(null, false);
                        Swal.fire({ toast: true, position: 'top', icon: 'success', title: 'Patient deleted.', showConfirmButton: false, timer: 1500 });
                    }).fail(function () {
                        Swal.fire({ toast: true, position: 'top', icon: 'error', title: 'Something went wrong.', showConfirmButton: false, timer: 1500 });
                    });
                });
            });

            // ── Unblock ───────────────────────────────────────────────────────
            $(document).on('click', '.btn-unblock', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Unblock Patient?',
                    text: 'They will regain access to their account.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Unblock',
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-light ms-2'
                    },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    $.post('/admin/patients/' + id + '/unblock', {
                        _token: '{{ csrf_token() }}'
                    }).done(function () {
                        table.ajax.reload(null, false);
                        Swal.fire({ toast: true, position: 'top', icon: 'success', title: 'Patient unblocked.', showConfirmButton: false, timer: 1500 });
                    }).fail(function () {
                        Swal.fire({ toast: true, position: 'top', icon: 'error', title: 'Failed to unblock.', showConfirmButton: false, timer: 1500 });
                    });
                });
            });

        });
    </script>
@endpush
