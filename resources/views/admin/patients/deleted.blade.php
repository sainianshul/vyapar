@extends('admin.layouts.app')
@section('title', 'Deleted Patients')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'People'],
                    ['label' => 'Patients', 'url' => route('admin.patients.index')],
                    ['label' => 'Deleted Patients'],
                ]" />
                <h2 class="page-title">Deleted Patients</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>All Patients
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-3">
                {{-- Search --}}
                <div class="input-icon">
                    <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                    <input type="text" id="dt-search" class="form-control" style="width: 260px;"
                        placeholder="Search deleted patients..." />
                </div>

                <div class="d-flex align-items-center gap-2">
                    {{-- Refresh Button --}}
                    <button type="button" class="btn btn-icon btn-ghost-secondary" id="refresh-table-btn"
                        data-bs-toggle="tooltip" title="Refresh">
                        <i class="ti ti-refresh"></i>
                    </button>
                </div>
            </div>
        </div>

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
                            <th>Last Login</th>
                            <th>Deleted At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', ['id' => 'patients-empty'])

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
            var table = $('#patients-table').DataTable({
                serverSide: true,
                processing: false,
                ajax: {
                    url: '{{ route('admin.patients.deleted.data') }}'
                },
                columns: [
                    { data: null, name: 'id', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
                    { data: 'name', name: 'name', orderable: false },
                    { data: 'email', name: 'email', orderable: false },
                    { data: 'phone', name: 'phone', orderable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'last_login_at', name: 'last_login_at', orderable: false, searchable: false },
                    { data: 'deleted_at', name: 'deleted_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
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
                    info: 'Showing _START_–_END_ of _TOTAL_ patients',
                    infoEmpty: 'No patients to show',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },
                initComplete: function () {
                    $('#patients-loader').remove();
                    var total = this.api().page.info().recordsDisplay;
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
                        var total = this.api().page.info().recordsDisplay;
                        if (total === 0) {
                            $('#patients-table-wrapper').addClass('d-none');
                            $('#patients-empty').removeClass('d-none');
                        } else {
                            $('#patients-empty').addClass('d-none');
                            $('#patients-table-wrapper').removeClass('d-none');
                        }
                    }
                }
            });

            var searchTimer;
            $('#dt-search').on('input', function () {
                clearTimeout(searchTimer);
                var q = $(this).val();
                searchTimer = setTimeout(function () { table.search(q).draw(); }, 400);
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

            // Restore
            $(document).on('click', '.btn-restore', function () {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Restore Patient?',
                    text: 'This patient will be active again.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    customClass: { confirmButton: 'btn btn-success', cancelButton: 'btn btn-light ms-2' },
                    buttonsStyling: false,
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.post('/admin/patients/' + id + '/restore', { _token: '{{ csrf_token() }}' })
                        .done(function () { table.ajax.reload(null, false); toastr.success('Patient restored.'); })
                        .fail(function () { toastr.error('Something went wrong.'); });
                });
            });
        });
    </script>
@endpush
