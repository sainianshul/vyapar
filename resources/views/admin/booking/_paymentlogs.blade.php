                {{-- Payment Logs Table (AJAX) --}}
                <div class="card shadow-sm mb-7 border border">
                    <div class="card-header border-0 pt-4 min-h-50px">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-body mb-0">Payment Logs</span>
                        </h3>
                    </div>
                    <div class="card-body pt-2 pb-5">
                        @include('admin.layouts.partials._table-loader', ['id' => 'payment-logs-loader'])
                        <div id="payment-logs-table-wrapper" class="table-responsive d-none">
                            <table id="payment-logs-table" class="table align-middle ">
                                <thead>
                                    <tr class="fw-bold text-secondary bg-light text-uppercase ">
                                        <th class="ps-3 rounded-start">Event</th>
                                        <th>Amount</th>
                                        <th>Gateway</th>
                                        <th>Order ID</th>
                                        <th>Payment ID</th>
                                        <th>Status</th>
                                        <th class="rounded-end">Time</th>
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
    $('#payment-logs-table').DataTable(Object.assign({}, getDtOpts('payment-logs-loader', 'payment-logs-table-wrapper'), {
        ajax: '{{ route('admin.bookings.payment-logs-data', $booking->id) }}',
        columns: [
            { data: 'event', className: 'ps-3' },
            { data: 'amount' },
            { data: 'gateway' },
            { data: 'gateway_order_id' },
            { data: 'gateway_payment_id' },
            { data: 'status' },
            { data: 'created_at', className: 'text-secondary' }
        ]
    }));
});
</script>
@endpush
