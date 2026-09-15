                {{-- Ratings & Reviews Table (AJAX) --}}
                <div class="card shadow-sm mb-7 border border">
                    <div class="card-header border-0 pt-4 min-h-50px">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold mb-0 text-body">Ratings & Reviews</span>
                        </h3>
                    </div>
                    <div class="card-body pt-2 pb-5">
                        @include('admin.layouts.partials._table-loader', ['id' => 'ratings-loader'])
                        <div id="ratings-table-wrapper" class="table-responsive d-none">
                            <table id="ratings-table" class="table align-middle  w-100" data-server-side="true">
                                <thead>
                                    <tr class="fw-bold text-secondary bg-light text-uppercase ">
                                        <th class="ps-3 rounded-start min-w-200px">User</th>
                                        <th class="min-w-100px">Rating</th>
                                        <th class="min-w-200px">Review</th>
                                        <th class="rounded-end min-w-100px">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>



@push('datatables_js')
<script>
$(document).ready(function () {
    $('#ratings-table').DataTable(Object.assign({}, getDtOpts('ratings-loader', 'ratings-table-wrapper'), {
        ajax: '{{ route('admin.bookings.reviews-data', $booking->id) }}',
        columns: [
            { data: 'user', className: 'ps-3' },
            { data: 'rating' },
            { data: 'review', className: 'text-secondary small text-wrap' },
            { data: 'created_at' }
        ]
    }));
});
</script>
@endpush
