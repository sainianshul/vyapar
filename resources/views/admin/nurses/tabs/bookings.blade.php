<div id="nurse-bookings-table-wrapper" class="table-responsive d-none">
    <table id="nurse-bookings-table" class="table table-vcenter w-100" data-server-side="true">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Patient</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Total</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<div id="nurse-bookings-loader" class="d-flex justify-content-center align-items-center py-4">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading…</span>
    </div>
</div>

<script>
    if (typeof jQuery !== 'undefined') {
        $('#nurse-bookings-table').DataTable({
            processing: false,
            serverSide: true,
            ajax: '{{ route('admin.nurses.bookings.data', $user->id) }}',
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
                emptyTable: "No bookings assigned to this nurse.",
                info: "Showing _START_ to _END_ of _TOTAL_ bookings",
                infoEmpty: "Showing 0 to 0 of 0 bookings",
                paginate: {
                    previous: '<i class="ti ti-chevron-left"></i>',
                    next: '<i class="ti ti-chevron-right"></i>',
                }
            },
            columns: [
                { data: 'reference_id' },
                { data: 'user' },
                { data: 'status' },
                { data: 'payment_status' },
                { data: 'total_amount' },
                { data: 'created_at' }
            ],
            initComplete: function () {
                $('#nurse-bookings-loader').remove();
                $('#nurse-bookings-table-wrapper').removeClass('d-none');
            }
        });
    }
</script>
