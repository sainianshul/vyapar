@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="page-pretitle">Overview</div>
                <h2 class="page-title">Dashboard</h2>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    <div id="pending-errors-container"></div>
    <div id="pending-nurses-container"></div>
    <div id="pending-tickets-container"></div>

    {{-- Stats Row --}}
    <div class="row row-deck row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Patients</div>
                    </div>
                    <div class="h1 mb-3" id="stat-total-patients">-</div>
                    <a href="{{ route('admin.patients.index') }}" class="text-muted small">View all →</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Approved Nurses</div>
                    </div>
                    <div class="h1 mb-3" id="stat-total-nurses">-</div>
                    <a href="{{ route('admin.nurses.index') }}" class="text-muted small">View all →</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Enquiries</div>
                    </div>
                    <div class="h1 mb-3" id="stat-total-requests">-</div>
                    <a href="{{ route('admin.requests.index') }}" class="text-muted small">View all →</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Active Bookings</div>
                    </div>
                    <div class="h1 mb-3" id="stat-total-bookings">-</div>
                    <a href="{{ route('admin.bookings.index') }}" class="text-muted small">View all →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue + Chart Row --}}
    <div class="row row-deck row-cards mb-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Revenue &amp; Bookings</h3>
                </div>
                <div class="card-body">
                    <div id="chart-bookings" style="height: 300px">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Revenue Summary</h3>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="status-dot status-dot-animated bg-success d-block"></span></div>
                            <div class="col text-truncate">
                                <div class="text-reset d-block">Lifetime Revenue</div>
                            </div>
                            <div class="col-auto">
                                <strong id="stat-total-revenue">-</strong>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="status-dot status-dot-animated bg-primary d-block"></span></div>
                            <div class="col text-truncate">
                                <div class="text-reset d-block">This Month</div>
                                <div class="d-block text-muted text-truncate mt-n1">{{ now()->format('F Y') }}</div>
                            </div>
                            <div class="col-auto">
                                <strong id="stat-month-revenue">-</strong>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="status-dot status-dot-animated bg-warning d-block"></span></div>
                            <div class="col text-truncate">
                                <div class="text-reset d-block">Today</div>
                            </div>
                            <div class="col-auto">
                                <strong id="stat-today-revenue">-</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Tables Row --}}
    <div class="row row-deck row-cards">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Bookings</h3>
                    <div class="card-actions">
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm">View All</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Patient</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="recent-bookings-tbody">
                            <tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Care Requests</h3>
                    <div class="card-actions">
                        <a href="{{ route('admin.requests.index') }}" class="btn btn-sm">View All</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Patient</th>
                                <th>City</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="recent-requests-tbody">
                            <tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── Load Alerts ───
    $.get('{{ route("admin.system.errors.pending-count") }}').done(function(res) {
        if (res.count > 0) {
            $('#pending-errors-container').html(`
                <div class="alert alert-danger alert-dismissible mb-3" role="alert">
                    <div class="d-flex">
                        <div><i class="ti ti-alert-circle icon alert-icon"></i></div>
                        <div>
                            <h4 class="alert-title">System Errors</h4>
                            <div class="text-secondary">${res.count} error(s) logged. <a href="{{ route('admin.system.error-logs') }}">Review →</a></div>
                        </div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="Close"></a>
                </div>
            `);
        }
    });

    $.get('{{ route("admin.nurses.pending-count") }}').done(function(res) {
        if (res.count > 0) {
            $('#pending-nurses-container').html(`
                <div class="alert alert-info alert-dismissible mb-3" role="alert">
                    <div class="d-flex">
                        <div><i class="ti ti-users icon alert-icon"></i></div>
                        <div>
                            <h4 class="alert-title">Nurse Approvals</h4>
                            <div class="text-secondary">${res.count} nurse(s) pending. <a href="{{ route('admin.nurses.pending_approval') }}">Review →</a></div>
                        </div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="Close"></a>
                </div>
            `);
        }
    });

    $.get('{{ route("admin.support.pending-count") }}').done(function(res) {
        if (res.count > 0) {
            $('#pending-tickets-container').html(`
                <div class="alert alert-warning alert-dismissible mb-3" role="alert">
                    <div class="d-flex">
                        <div><i class="ti ti-message icon alert-icon"></i></div>
                        <div>
                            <h4 class="alert-title">Support Tickets</h4>
                            <div class="text-secondary">${res.count} ticket(s) open. <a href="{{ route('admin.support.index') }}?status=0">View →</a></div>
                        </div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="Close"></a>
                </div>
            `);
        }
    });

    // ── Fetch Stats ───
    fetch('{{ route("admin.dashboard.stats") }}')
        .then(r => r.json())
        .then(d => {
            $('#stat-total-patients').text(d.total_patients);
            $('#stat-total-nurses').text(d.total_nurses);
            $('#stat-total-requests').text(d.total_requests);
            $('#stat-total-bookings').text(d.total_bookings);

            $('#stat-total-revenue').html('₹' + d.total_revenue);
            $('#stat-month-revenue').html('₹' + d.month_revenue);
            $('#stat-today-revenue').html('₹' + d.today_revenue);

            // Recent Bookings
            var bh = '';
            d.recent_bookings.forEach(function(b) {
                bh += `<tr>
                    <td><a href="/admin/bookings/${b.id}">#${b.reference_id}</a></td>
                    <td>${b.user_name}</td>
                    <td>₹${b.total_amount}</td>
                    <td><span class="badge bg-${({0:'warning',1:'primary',2:'info',3:'success',4:'danger'})[b.status] || 'secondary'}-lt">${b.status_text}</span></td>
                </tr>`;
            });
            if (!d.recent_bookings.length) bh = '<tr><td colspan="4" class="text-center text-muted py-4">No bookings yet</td></tr>';
            $('#recent-bookings-tbody').html(bh);

            // Recent Requests
            var rh = '';
            d.recent_requests.forEach(function(r) {
                rh += `<tr>
                    <td><a href="/admin/requests/${r.id}">#${r.reference_id}</a></td>
                    <td>${r.user_name}</td>
                    <td class="text-muted">${r.city}</td>
                    <td><span class="badge bg-${({0:'warning',1:'success',2:'danger',3:'secondary',4:'primary',5:'info'})[r.status] || 'secondary'}-lt">${r.status_text}</span></td>
                </tr>`;
            });
            if (!d.recent_requests.length) rh = '<tr><td colspan="4" class="text-center text-muted py-4">No requests yet</td></tr>';
            $('#recent-requests-tbody').html(rh);

            // Chart
            var el = document.getElementById('chart-bookings');
            el.innerHTML = '';
            new ApexCharts(el, {
                series: [{
                    name: 'Bookings', type: 'column',
                    data: d.monthly_bookings.map(m => m.count)
                }, {
                    name: 'Revenue (₹)', type: 'line',
                    data: d.monthly_bookings.map(m => m.revenue)
                }],
                chart: { height: 300, type: 'line', toolbar: { show: false }, fontFamily: 'inherit' },
                stroke: { width: [0, 3], curve: 'smooth' },
                colors: ['#206bc4', '#2fb344'],
                plotOptions: { bar: { columnWidth: '40%', borderRadius: 4 } },
                dataLabels: { enabled: false },
                xaxis: { categories: d.monthly_bookings.map(m => m.month) },
                yaxis: [
                    { title: { text: 'Bookings' } },
                    { opposite: true, title: { text: 'Revenue (₹)' } }
                ],
                grid: { strokeDashArray: 4 },
                tooltip: {
                    shared: true, intersect: false,
                    y: { formatter: function(v, o) { return o.seriesIndex === 1 ? '₹' + v.toLocaleString() : v; } }
                }
            }).render();

        }).catch(err => console.error("Dashboard stats error", err));
});
</script>
@endpush