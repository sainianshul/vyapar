@extends('admin.layouts.app')

@section('title', 'Feedbacks')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Feedbacks'],
                ]" />
                <h2 class="page-title">Feedbacks</h2>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary btn-add-feedback">
                    <i class="ti ti-plus me-1"></i>Add Feedback
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
                        placeholder="Search feedbacks..." />
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
            <div id="feedbacks-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            <div id="feedbacks-table-wrapper" class="table-responsive d-none">
                <table id="feedbacks-table" class="table table-vcenter w-100">
                    <thead>
                        <tr>
                            <th class="w-1">S.No</th>
                            <th>User ID</th>
                            <th>Feedback</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'feedbacks-empty',
                'title' => 'No feedbacks found',
                'subtitle' => 'Try adjusting your search or add a new feedback.'
            ])
        </div>
    </div>

    {{-- Feedback Modal (Add/Edit) --}}
    <div class="modal modal-blur fade" id="feedback-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="feedback-form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="feedback-modal-title">Add Feedback</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="feedback-id" name="id">
                        
                        <div class="mb-3">
                            <label class="form-label required">Select User</label>
                            <select class="form-select" id="feedback-user_id" name="user_id" required>
                                <option value="">Choose User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} (ID: {{ $user->id }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Feedback</label>
                            <textarea class="form-control" id="feedback-text" name="feedback" rows="4" placeholder="Feedback message..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Status</label>
                            <select class="form-select" id="feedback-status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="save-feedback-btn">Save Feedback</button>
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
            let table = $('#feedbacks-table').DataTable({
                serverSide: true,
                processing: false,
                ajax: '{{ route('admin.feedbacks.data') }}',
                columns: [
                    { data: null, name: 'id', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
                    { data: 'user_id', name: 'user_id' },
                    { data: 'feedback', name: 'feedback' },
                    { data: 'status', name: 'status' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],
                order: [[1, 'desc']],
                pageLength: 25,
                dom: "<'row'<'col-12'tr>>" +
                     "<'row align-items-center mt-3 pt-3 flex-nowrap'" +
                     "<'col-sm-12 col-md-5'i>" +
                     "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-3'lp>>",
                language: {
                    emptyTable: ' ', zeroRecords: ' ', loadingRecords: ' ',
                    info: 'Showing _START_–_END_ of _TOTAL_ feedbacks',
                    infoEmpty: 'No feedbacks to show', infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: { previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>' },
                },
                initComplete: function () {
                    $('#feedbacks-loader').remove();
                    let total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#feedbacks-table-wrapper').addClass('d-none');
                        $('#feedbacks-empty').removeClass('d-none');
                    } else {
                        $('#feedbacks-empty').addClass('d-none');
                        $('#feedbacks-table-wrapper').removeClass('d-none');
                    }
                },
                drawCallback: function () {
                    if ($('#feedbacks-loader').length === 0) {
                        let total = this.api().page.info().recordsDisplay;
                        if (total === 0) {
                            $('#feedbacks-table-wrapper').addClass('d-none');
                            $('#feedbacks-empty').removeClass('d-none');
                        } else {
                            $('#feedbacks-empty').addClass('d-none');
                            $('#feedbacks-table-wrapper').removeClass('d-none');
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

            // Add Feedback Modal
            $('.btn-add-feedback').on('click', function() {
                $('#feedback-form')[0].reset();
                $('#feedback-id').val('');
                $('#feedback-modal-title').text('Add Feedback');
                $('#feedback-modal').modal('show');
            });

            // Edit Feedback Modal
            $(document).on('click', '.btn-edit', function() {
                $('#feedback-form')[0].reset();
                let id = $(this).data('id');
                let user_id = $(this).data('user_id');
                let feedback = $(this).data('feedback');
                let status = $(this).data('status');

                $('#feedback-id').val(id);
                $('#feedback-user_id').val(user_id);
                $('#feedback-text').val(feedback);
                $('#feedback-status').val(status);

                $('#feedback-modal-title').text('Edit Feedback');
                $('#feedback-modal').modal('show');
            });

            // Save Feedback
            $('#feedback-form').on('submit', function(e) {
                e.preventDefault();
                let id = $('#feedback-id').val();
                let url = id ? '/admin/feedbacks/' + id : '/admin/feedbacks';
                let data = $(this).serialize();
                if (id) {
                    data += '&_method=PUT';
                }
                data += '&_token={{ csrf_token() }}';

                let btn = $('#save-feedback-btn');
                let originalText = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function(res) {
                        $('#feedback-modal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: res.message });
                    },
                    error: function(xhr) {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 2500, icon: 'error', title: 'Error saving feedback' });
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
                    url: '/admin/feedbacks/' + id + '/status',
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

            // Delete Feedback
            $(document).on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                $(this).tooltip('hide');
                
                Swal.fire({
                    title: 'Delete this feedback?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    $.post('/admin/feedbacks/' + id, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
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
