                {{-- Bids Table --}}
                <div class="card shadow-sm mb-7 border border">
                    <div class="card-header border-0 pt-4 min-h-50px">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-body mb-0">Received Bids</span>
                        </h3>
                        <div class="card-toolbar">
                            <div class="d-flex align-items-center position-relative">
                                <i class="ti ti-search text-muted position-absolute ms-3"></i>
                                <input type="text" id="bids-search"
                                    class="form-control form-control-sm form-control border border text-body w-200px ps-9 fw-semibold"
                                    placeholder="Search bids...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-2 pb-5">
                        <div class="table-responsive">
                            <table id="bids-table" class="table align-middle ">
                                <thead>
                                    <tr class="fw-bold text-secondary bg-light text-uppercase ">
                                        <th class="ps-3 min-w-150px rounded-start">Nurse</th>
                                        <th class="min-w-100px">Nurse Amt</th>
                                        <th class="min-w-100px">Comm.</th>
                                        <th class="min-w-100px">Total</th>
                                        <th class="min-w-100px">Status</th>
                                        <th class="text-end pe-3 rounded-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

@push('datatables_js')
<script>
$(document).ready(function () {
    let table = $('#bids-table').DataTable({
        serverSide: true,
        processing: false,
        ajax: {
            url: '{{ route('admin.requests.bids-data', $careRequest->id) }}'
        },
        columns: [
            { data: 'nurse', name: 'nurse', orderable: false, searchable: true, className: 'ps-3' },
            { data: 'nurse_amount', name: 'nurse_amount' },
            { data: 'commission_amount', name: 'commission_amount' },
            { data: 'total_amount', name: 'total_amount' },
            { data: 'status', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end pe-3' },
        ],
        order: [[3, 'asc']], // Order by total amount ascending initially
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        dom:
            "<'row'<'col-12'tr>>" +
            "<'row align-items-center mt-3 pt-3 flex-nowrap'" +
            "<'col-sm-12 col-md-5 text-muted fw-semibold'i>" +
            "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-2'lp>>",
        language: {
            emptyTable: '<span class="text-muted small">No bids received yet.</span>',
            zeroRecords: '<span class="text-muted small">No matching bids found.</span>',
            info: 'Showing _START_ to _END_ of _TOTAL_',
            lengthMenu: '_MENU_',
            paginate: {
                previous: '<i class="ti ti-chevron-left"></i>',
                next: '<i class="ti ti-chevron-right"></i>',
            },
        }
    });

    // ── Search ───────────────────────────────────────────────────────
    let searchTimer;
    $('#bids-search').on('input', function () {
        clearTimeout(searchTimer);
        let query = $(this).val();
        searchTimer = setTimeout(function () {
            table.search(query).draw();
        }, 400);
    });
});
</script>
@endpush
