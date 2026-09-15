<div id="patient-bookings-table-wrapper" class="table-responsive d-none">
    <table id="patient-bookings-table" class="table table-vcenter w-100" data-server-side="true">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Nurse</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Sessions</th>
                <th>Total</th>
                <th>Created At</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<div id="patient-bookings-loader" class="d-flex justify-content-center align-items-center py-4">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading…</span>
    </div>
</div>

<script>
    if (typeof jQuery !== 'undefined') {
        $('#patient-bookings-table').DataTable({
            processing: false,
            serverSide: true,
            ajax: '{{ route('admin.patients.bookings.data', $patient->id) }}',
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
                emptyTable: "No bookings found for this patient.",
                info: "Showing _START_ to _END_ of _TOTAL_ bookings",
                infoEmpty: "Showing 0 to 0 of 0 bookings",
                paginate: {
                    previous: '<i class="ti ti-chevron-left"></i>',
                    next: '<i class="ti ti-chevron-right"></i>',
                }
            },
            columns: [
                { data: 'reference_id', name: 'reference_id' },
                { data: 'nurse', name: 'nurse', orderable: false, searchable: false },
                { data: 'status', name: 'status' },
                { data: 'payment_status', name: 'payment_status' },
                { data: 'sessions', name: 'sessions', orderable: false, searchable: false },
                { data: 'amount', name: 'total_amount' },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
            ],
            initComplete: function () {
                $('#patient-bookings-loader').remove();
                $('#patient-bookings-table-wrapper').removeClass('d-none');
            }
        });
    }
</script>
