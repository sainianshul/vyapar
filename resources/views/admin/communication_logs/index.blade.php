@extends('admin.layouts.app')

@section('title', 'Communication Logs')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[['label' => 'Communication Logs']]" />
                <h2 class="page-title">Communication Logs</h2>
            </div>
            
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <button type="button" class="btn btn-outline-danger" id="empty-logs-btn">
                        <i class="ti ti-trash-x me-2"></i>
                        Empty Logs
                    </button>
                    
                    <form id="empty-logs-form" action="{{ route('admin.communication-logs.truncate') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 w-100">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <div class="input-icon" style="min-width: 250px;">
                        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                        <input type="text" id="dt-search" class="form-control" placeholder="Search logs...">
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-icon btn-outline-secondary" id="refresh-table-btn" data-bs-toggle="tooltip" title="Refresh">
                        <i class="ti ti-refresh"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">

            <div id="communicationlog-loader" class="d-flex justify-content-center align-items-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            {{-- Table --}}
            <div id="communicationlog-table-wrapper" class="table-responsive d-none">
                {{ $dataTable->table(['class' => 'table table-vcenter w-100']) }}
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'communicationlog-empty',
                'title' => 'No logs found',
                'subtitle' => 'Try adjusting your search query or send a test OTP.'
            ])

        </div>
    </div>

@endsection

@push('datatables_css')
    @include('admin.layouts.partials._datatable-cdn-css')
@endpush

@push('datatables_js')
    @include('admin.layouts.partials._datatable-cdn-js')
    {{ $dataTable->scripts() }}

    <script>
        $(function () {
            let table = window.LaravelDataTables["communicationlog-table"];

            // Custom init/draw callbacks for loading state and empty state
            table.on('init.dt', function () {
                $('#communicationlog-loader').remove();
                let total = table.page.info().recordsDisplay;
                if (total === 0) {
                    $('#communicationlog-table-wrapper').addClass('d-none');
                    $('#communicationlog-empty').removeClass('d-none');
                } else {
                    $('#communicationlog-empty').addClass('d-none');
                    $('#communicationlog-table-wrapper').removeClass('d-none');
                }
            });

            table.on('draw.dt', function () {
                if ($('#communicationlog-loader').length === 0) {
                    let total = table.page.info().recordsDisplay;
                    if (total === 0) {
                        $('#communicationlog-table-wrapper').addClass('d-none');
                        $('#communicationlog-empty').removeClass('d-none');
                    } else {
                        $('#communicationlog-empty').addClass('d-none');
                        $('#communicationlog-table-wrapper').removeClass('d-none');
                    }
                }
                $('[data-bs-toggle="tooltip"]').tooltip({ trigger: 'hover' });
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

            // Delete Record
            $(document).on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                
                Swal.fire({
                    title: 'Delete this log?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                    customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/communication-logs/' + id,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function (res) {
                                if (res.success) {
                                    table.ajax.reload(null, false);
                                    Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Log deleted' });
                                }
                            },
                            error: function () {
                                Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to delete' });
                            }
                        });
                    }
                });
            });

            // Empty All Logs Button
            $('#empty-logs-btn').on('click', function() {
                Swal.fire({
                    title: 'Empty all communication logs?',
                    text: 'Are you absolutely sure? This will delete all logged messages permanently and cannot be undone!',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, empty all logs',
                    cancelButtonText: 'Cancel',
                    customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $('#empty-logs-form').submit();
                    }
                });
            });
        });
    </script>
@endpush
