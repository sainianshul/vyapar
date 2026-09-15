                {{-- Sessions Table (AJAX) --}}
                <div class="card shadow-sm mb-7 border border">
                    <div class="card-header border-0 pt-4 min-h-50px">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-body mb-0">Sessions</span>
                            <span class="text-muted mt-1 fw-semibold small">{{ $booking->completed_sessions }} of {{ $booking->total_sessions }} completed</span>
                        </h3>
                    </div>
                    <div class="card-body pt-2 pb-5">
                            @include('admin.layouts.partials._table-loader', ['id' => 'sessions-loader'])
                            <div id="sessions-table-wrapper" class="d-none">
                                <table id="sessions-table" class="table align-middle  w-100">
                                    <thead>
                                        <tr class="fw-bold text-secondary bg-light text-uppercase ">
                                            <th class="ps-3 rounded-start">#</th>
                                            <th class="min-w-100px">Date</th>
                                            <th>Start</th>
                                            <th>End</th>
                                            <th>Started At</th>
                                            <th>Ended At</th>
                                            <th>Status</th>
                                            <th>OTP</th>
                                            <th class="rounded-end">Notes</th>
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
    $('#sessions-table').DataTable(Object.assign({}, getDtOpts('sessions-loader', 'sessions-table-wrapper'), {
        ajax: '{{ route('admin.bookings.sessions-data', $booking->id) }}',
        columns: [
            { data: 'session_number', className: 'ps-3 fw-bold text-body' },
            { data: 'session_date', className: 'fw-semibold text-body' },
            { data: 'start_time', className: 'text-secondary' },
            { data: 'end_time', className: 'text-secondary' },
            { data: 'started_at', className: 'text-secondary' },
            { data: 'ended_at', className: 'text-secondary' },
            { data: 'status' },
            { data: 'otp_verified' },
            { data: 'nurse_notes', className: 'text-muted', render: function(data) {
                return '<div style="max-width:150px; white-space:normal;">' + data + '</div>';
            }}
        ]
    }));
});
</script>
@endpush
