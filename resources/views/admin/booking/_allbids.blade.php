                {{-- All Bids List --}}
                @if($booking->careRequest && $booking->careRequest->bids->count() > 0)
                    <div class="card shadow-sm mb-7 border border">
                        <div class="card-header border-0 pt-4 min-h-50px">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold mb-0 text-body">All Bids for this Request</span>
                                <span class="text-muted mt-1 fw-semibold small">{{ $booking->careRequest->bids->count() }} bids total</span>
                            </h3>
                        </div>
                        <div class="card-body pt-2 pb-5">
                            @include('admin.layouts.partials._table-loader', ['id' => 'bids-loader'])
                            <div id="bids-table-wrapper" class="table-responsive d-none">
                                <table id="bids-table" class="table align-middle  w-100" data-server-side="true">
                                    <thead>
                                        <tr class="fw-bold text-secondary bg-light text-uppercase ">
                                            <th class="ps-3 rounded-start min-w-150px">Nurse</th>
                                            <th>Nurse Amount</th>
                                            <th>Commission</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th class="rounded-end min-w-150px">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif



@push('datatables_js')
<script>
$(document).ready(function () {
    @if($booking->care_request_id)
        $('#bids-table').DataTable(Object.assign({}, getDtOpts('bids-loader', 'bids-table-wrapper'), {
            ajax: '{{ route('admin.bookings.bids-data', $booking->id) }}',
            columns: [
                { data: 'nurse', className: 'ps-3' },
                { data: 'nurse_amount' },
                { data: 'commission' },
                { data: 'total' },
                { data: 'status' },
                { data: 'notes' }
            ]
        }));
    @endif
});
</script>
@endpush
