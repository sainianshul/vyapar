<div id="nurse-care-requests-table-wrapper" class="table-responsive d-none">
    <table id="nurse-care-requests-table" class="table table-vcenter w-100" data-server-side="true">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Patient</th>
                <th>Status</th>
                <th>Expires At</th>
                <th>Notified At</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<div id="nurse-care-requests-loader" class="d-flex justify-content-center align-items-center py-4">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading…</span>
    </div>
</div>

<script>
    if (typeof jQuery !== 'undefined') {
        $('#nurse-care-requests-table').DataTable({
            processing: false,
            serverSide: true,
            ajax: '{{ route('admin.nurses.care-requests.data', $user->id) }}',
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
                emptyTable: "No active care requests found for this nurse.",
                info: "Showing _START_ to _END_ of _TOTAL_ requests",
                infoEmpty: "Showing 0 to 0 of 0 requests",
                paginate: {
                    previous: '<i class="ti ti-chevron-left"></i>',
                    next: '<i class="ti ti-chevron-right"></i>',
                }
            },
            columns: [
                { data: 'request' },
                { data: 'patient' },
                { data: 'status' },
                { data: 'expires_at' },
                { data: 'created_at' }
            ],
            initComplete: function () {
                $('#nurse-care-requests-loader').remove();
                $('#nurse-care-requests-table-wrapper').removeClass('d-none');
            }
        });
    }
</script>
