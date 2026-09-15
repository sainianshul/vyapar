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

    {{-- Stats Row --}}
    <div class="row row-deck row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Users</div>
                    </div>
                    <div class="h1 mb-3">1,204</div>
                    <a href="{{ route('admin.users.index') }}" class="text-muted small">View all →</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Companies</div>
                    </div>
                    <div class="h1 mb-3">850</div>
                    <a href="#" class="text-muted small">View all →</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Active Products</div>
                    </div>
                    <div class="h1 mb-3">5,432</div>
                    <a href="#" class="text-muted small">View all →</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">New Leads (Today)</div>
                    </div>
                    <div class="h1 mb-3">124</div>
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
                    <h3 class="card-title">User Growth & Engagement</h3>
                </div>
                <div class="card-body p-0">
                    <div id="chart-users" style="height: 300px">
                        <!-- Dummy chart visualization -->
                        <div class="d-flex justify-content-center align-items-center h-100 bg-light rounded m-3">
                            <span class="text-muted"><i class="ti ti-chart-line fs-1 d-block text-center mb-2"></i>[ Chart Placeholder: Monthly Active Users ]</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Activity Summary</h3>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="status-dot status-dot-animated bg-success d-block"></span></div>
                            <div class="col text-truncate">
                                <div class="text-reset d-block">Active Users</div>
                                <div class="d-block text-muted text-truncate mt-n1">Currently online</div>
                            </div>
                            <div class="col-auto">
                                <strong>340</strong>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="status-dot status-dot-animated bg-primary d-block"></span></div>
                            <div class="col text-truncate">
                                <div class="text-reset d-block">New Registrations</div>
                                <div class="d-block text-muted text-truncate mt-n1">{{ now()->format('F Y') }}</div>
                            </div>
                            <div class="col-auto">
                                <strong>+85</strong>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="status-dot status-dot-animated bg-warning d-block"></span></div>
                            <div class="col text-truncate">
                                <div class="text-reset d-block">Pending KYC</div>
                            </div>
                            <div class="col-auto">
                                <strong>12</strong>
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
                    <h3 class="card-title">Recent Companies Added</h3>
                    <div class="card-actions">
                        <a href="#" class="btn btn-sm">View All</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>City</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex py-1 align-items-center">
                                        <span class="avatar me-2">SM</span>
                                        <div class="flex-fill">
                                            <div class="font-weight-medium">Super Mart India</div>
                                            <div class="text-secondary">Retail & FMCG</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-secondary">Delhi</td>
                                <td><span class="badge bg-success-lt">Active</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex py-1 align-items-center">
                                        <span class="avatar me-2">TE</span>
                                        <div class="flex-fill">
                                            <div class="font-weight-medium">TechNova Electronics</div>
                                            <div class="text-secondary">Manufacturing</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-secondary">Mumbai</td>
                                <td><span class="badge bg-warning-lt">Pending</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex py-1 align-items-center">
                                        <span class="avatar me-2">GT</span>
                                        <div class="flex-fill">
                                            <div class="font-weight-medium">Global Traders</div>
                                            <div class="text-secondary">Wholesale</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-secondary">Jaipur</td>
                                <td><span class="badge bg-success-lt">Active</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Lead Requirements</h3>
                    <div class="card-actions">
                        <a href="#" class="btn btn-sm">View All</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Requirement</th>
                                <th>Buyer</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div>Need 500 pcs Cotton T-Shirts</div>
                                    <div class="text-secondary small">Apparel & Clothing</div>
                                </td>
                                <td class="text-secondary">Amit Kumar</td>
                                <td>Today</td>
                            </tr>
                            <tr>
                                <td>
                                    <div>Looking for PVC Pipes Supplier</div>
                                    <div class="text-secondary small">Construction Material</div>
                                </td>
                                <td class="text-secondary">Rajesh Singh</td>
                                <td>Yesterday</td>
                            </tr>
                            <tr>
                                <td>
                                    <div>Bulk order of A4 Printer Paper</div>
                                    <div class="text-secondary small">Office Supplies</div>
                                </td>
                                <td class="text-secondary">Neha Sharma</td>
                                <td>2 days ago</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection