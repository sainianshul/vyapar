@extends('admin.layouts.app')
@section('title', 'All Nurses')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'People'],
                    ['label' => 'Nurses'],
                ]" />
                <h2 class="page-title">All Nurses</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.nurses.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Add Nurse
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
                        placeholder="Search nurses..." />
                </div>

                {{-- Right Controls --}}
                <div class="d-flex align-items-center gap-2">

                    {{-- Refresh Button --}}
                    <button type="button" class="btn btn-icon btn-ghost-secondary" id="refresh-table-btn"
                        data-bs-toggle="tooltip" title="Refresh">
                        <i class="ti ti-refresh"></i>
                    </button>

                    {{-- Status Filter --}}
                    <select id="filter-profile-status" class="form-select" style="width: 170px;">
                        <option value="">All Status</option>
                        @foreach (\App\Models\NurseProfile::getStatusList() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">

            {{-- Loading Spinner --}}
            <div id="nurses-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            {{-- Table --}}
            <div id="nurses-table-wrapper" class="table-responsive d-none">
                <table id="nurses-table" class="table table-vcenter w-100">
                    <thead>
                        <tr>
                            <th class="w-1">S.No</th>
                            <th>Nurse</th>
                            <th>Phone Number</th>
                            <th>City</th>
                            <th>Joined At</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'nurses-empty'
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

            var table = $('#nurses-table').DataTable({
                serverSide: true,
                processing: false,
                ajax: {
                    url: '{{ route('admin.nurses.data') }}',
                    data: function (d) {
                        d.profile_status = $('#filter-profile-status').val();
                    }
                },
                columns: [
                    { data: null, name: 'id', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
                    { data: 'nurse', name: 'nurse', orderable: false },
                    { data: 'phone', name: 'phone', orderable: false },
                    { data: 'location', name: 'location', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'profile_status', name: 'profile_status', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
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
                    info: 'Showing _START_–_END_ of _TOTAL_ nurses',
                    infoEmpty: 'No nurses to show',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },
                initComplete: function () {
                    $('#nurses-loader').remove();
                    var total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#nurses-table-wrapper').addClass('d-none');
                        $('#nurses-empty').removeClass('d-none');
                    } else {
                        $('#nurses-empty').addClass('d-none');
                        $('#nurses-table-wrapper').removeClass('d-none');
                    }
                },
                drawCallback: function () {
                    if ($('#nurses-loader').length === 0) {
                        var total = this.api().page.info().recordsDisplay;
                        if (total === 0) {
                            $('#nurses-table-wrapper').addClass('d-none');
                            $('#nurses-empty').removeClass('d-none');
                        } else {
                            $('#nurses-empty').addClass('d-none');
                            $('#nurses-table-wrapper').removeClass('d-none');
                        }
                    }
                    $('[data-bs-toggle="tooltip"]').tooltip({ trigger: 'hover' });
                }
            });

            // ── Search ───────────────────────────────────────────────────────
            var searchTimer;
            $('#dt-search').on('input', function () {
                clearTimeout(searchTimer);
                var q = $(this).val();
                searchTimer = setTimeout(function () { table.search(q).draw(); }, 400);
            });

            // ── Status Filter ────────────────────────────────────────────────
            $('#filter-profile-status').on('change', function () { table.ajax.reload(); });

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

            // ── Delete ───────────────────────────────────────────────────────
            $(document).on('click', '.btn-delete', function () {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Nurse?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' },
                    buttonsStyling: false,
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.post('/admin/nurses/' + id, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
                        .done(function () { 
                            table.ajax.reload(null, false); 
                            Swal.fire({
                                toast: true,
                                position: 'top',
                                showConfirmButton: false,
                                timer: 1500,
                                icon: 'success',
                                title: 'Nurse deleted successfully'
                            });
                        })
                        .fail(function () {
                            Swal.fire({
                                toast: true,
                                position: 'top',
                                showConfirmButton: false,
                                timer: 1500,
                                icon: 'error',
                                title: 'Something went wrong'
                            });
                        });
                });
            });
        });
    </script>
@endpush
