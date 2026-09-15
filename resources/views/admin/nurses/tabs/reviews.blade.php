<div id="nurse-reviews-table-wrapper" class="table-responsive d-none">
    <table id="nurse-reviews-table" class="table table-vcenter w-100" data-server-side="true">
        <thead>
            <tr>
                <th>User</th>
                <th>Booking ID</th>
                <th>Rating</th>
                <th>Review</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<div id="nurse-reviews-loader" class="d-flex justify-content-center align-items-center py-4">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading…</span>
    </div>
</div>

<script>
    if (typeof jQuery !== 'undefined') {
        $('#nurse-reviews-table').DataTable({
            processing: false,
            serverSide: true,
            ajax: '{{ route('admin.nurses.reviews.data', $user->id) }}',
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
                emptyTable: "No reviews found for this nurse.",
                info: "Showing _START_ to _END_ of _TOTAL_ reviews",
                infoEmpty: "Showing 0 to 0 of 0 reviews",
                paginate: {
                    previous: '<i class="ti ti-chevron-left"></i>',
                    next: '<i class="ti ti-chevron-right"></i>',
                }
            },
            columns: [
                { data: 'user' },
                { data: 'booking_id' },
                { data: 'rating' },
                { data: 'review' },
                { data: 'created_at' }
            ],
            initComplete: function () {
                $('#nurse-reviews-loader').remove();
                $('#nurse-reviews-table-wrapper').removeClass('d-none');
            }
        });
    }
</script>
