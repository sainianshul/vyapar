<div id="nurse-login-table-wrapper" class="table-responsive d-none">
    <table id="nurse-login-table" class="table table-vcenter w-100" data-server-side="true">
        <thead>
            <tr>
                <th>IP Address</th>
                <th>Device / Browser</th>
                <th>Date & Time</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<div id="nurse-login-loader" class="d-flex justify-content-center align-items-center py-4">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading…</span>
    </div>
</div>

<script>
    if (typeof jQuery !== 'undefined') {
        $('#nurse-login-table').DataTable({
            processing: false,
            serverSide: true,
            ajax: '{{ route('admin.nurses.login-history.data', $user->id) }}',
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
                emptyTable: "No login history found for this nurse.",
                info: "Showing _START_ to _END_ of _TOTAL_ logins",
                infoEmpty: "Showing 0 to 0 of 0 logins",
                paginate: {
                    previous: '<i class="ti ti-chevron-left"></i>',
                    next: '<i class="ti ti-chevron-right"></i>',
                }
            },
            columns: [
                { data: 'ip_address' },
                { data: 'user_agent' },
                { data: 'created_at' },
                { data: 'action', className: 'text-end', orderable: false, searchable: false }
            ],
            initComplete: function () {
                $('#nurse-login-loader').remove();
                $('#nurse-login-table-wrapper').removeClass('d-none');
            }
        });
    }
</script>
