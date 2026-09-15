<div id="patient-requests-table-wrapper" class="table-responsive d-none">
    <table id="patient-requests-table" class="table table-vcenter w-100" data-server-side="true">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Status</th>
                <th>Date & Time</th>
                <th>Location</th>
                <th>Bidding Ends At</th>
                <th>Created At</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<div id="patient-requests-loader" class="d-flex justify-content-center align-items-center py-4">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading…</span>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        if (typeof jQuery !== 'undefined') {
            $('#patient-requests-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: '{{ route('admin.patients.requests.data', $patient->id) }}',
                paging: true,
                pageLength: 10,
                searching: false,
                info: true,
                ordering: false,
                dom: "<'row'<'col-sm-12'tr>>" +
                     "<'row mt-3'" +
                     "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                     "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>>",
                language: {
                    emptyTable: "No requests found for this patient.",
                    info: "Showing _START_ to _END_ of _TOTAL_ requests",
                    infoEmpty: "Showing 0 to 0 of 0 requests",
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    }
                },
                columns: [
                    { data: 'reference_id', name: 'reference_id' },
                    { data: 'status', name: 'status' },
                    { data: 'date_time', name: 'date_time', orderable: false, searchable: false },
                    { data: 'location', name: 'location', orderable: false, searchable: false },
                    { data: 'bidding_ends_at', name: 'bidding_ends_at' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],
                order: [[5, 'desc']],
                initComplete: function () {
                    $('#patient-requests-loader').remove();
                    $('#patient-requests-table-wrapper').removeClass('d-none');
                }
            });
        }

        // ── Delete ───────────────────────────────────────────────────────
        $(document).on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Delete Request?',
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
                $.post('/admin/requests/' + id, {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                })
                .done(function () {
                    $('#patient-requests-table').DataTable().ajax.reload(null, false);
                    Swal.fire({ toast: true, position: 'top', icon: 'success', title: 'Care request deleted.', showConfirmButton: false, timer: 1500 });
                })
                .fail(function () {
                    Swal.fire({ toast: true, position: 'top', icon: 'error', title: 'Something went wrong.', showConfirmButton: false, timer: 1500 });
                });
            });
        });

    });
</script>
@endpush
