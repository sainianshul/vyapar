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
                    <a href="#" class="text-muted small">View all →</a>
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
                    <a href="#" class="text-muted small">View all →</a>
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
                    <a href="#" class="text-muted small">View all →</a>
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
                    <a href="#" class="text-muted small">View all →</a>
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
                        <a href="#" class="btn btn-sm">View All</a>
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
                        <a href="#" class="btn btn-sm">View All</a>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dashboard template is ready. Add your data fetching logic here.
});
</script>
@endpush