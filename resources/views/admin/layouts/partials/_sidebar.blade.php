<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('admin.dashboard') }}">
                VCancare
            </a>
        </h1>
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-home"></i>
                        </span>
                        <span class="nav-link-title">Dashboard</span>
                    </a>
                </li>
                
                {{-- ===================== --}}
                {{-- PEOPLE --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">People</div>
                </li>
                
                <li class="nav-item dropdown {{ request()->routeIs('admin.nurses.*') ? 'active' : '' }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-nurses" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="{{ request()->routeIs('admin.nurses.*') ? 'true' : 'false' }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-nurse"></i>
                        </span>
                        <span class="nav-link-title">Nurses</span>
                    </a>
                    <div class="dropdown-menu {{ request()->routeIs('admin.nurses.*') ? 'show' : '' }}">
                        <a class="dropdown-item {{ request()->routeIs('admin.nurses.index') ? 'active' : '' }}" href="{{ route('admin.nurses.index') }}">All Nurses</a>
                        <a class="dropdown-item {{ request()->routeIs('admin.nurses.pending_approval') ? 'active' : '' }}" href="{{ route('admin.nurses.pending_approval') }}">Pending Approval</a>
                        <a class="dropdown-item {{ request()->routeIs('admin.nurses.approved') ? 'active' : '' }}" href="{{ route('admin.nurses.approved') }}">Approved</a>
                        <a class="dropdown-item {{ request()->routeIs('admin.nurses.rejected') ? 'active' : '' }}" href="{{ route('admin.nurses.rejected') }}">Rejected</a>
                    </div>
                </li>

                <li class="nav-item dropdown {{ request()->routeIs('admin.patients.*') ? 'active' : '' }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-users" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="{{ request()->routeIs('admin.patients.*') ? 'true' : 'false' }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-user-heart"></i>
                        </span>
                        <span class="nav-link-title">Users</span>
                    </a>
                    <div class="dropdown-menu {{ request()->routeIs('admin.patients.*') ? 'show' : '' }}">
                        <a class="dropdown-item {{ request()->routeIs('admin.patients.index') ? 'active' : '' }}" href="{{ route('admin.patients.index') }}">All Users</a>
                        <a class="dropdown-item {{ request()->routeIs('admin.patients.blocked') ? 'active' : '' }}" href="{{ route('admin.patients.blocked') }}">Blocked Users</a>
                    </div>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.login-history.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.login-history.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-history"></i>
                        </span>
                        <span class="nav-link-title">Login History</span>
                    </a>
                </li>

                {{-- ===================== --}}
                {{-- CARE --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">Care</div>
                </li>

                <li class="nav-item dropdown {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-bookings" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="{{ request()->routeIs('admin.bookings.*') ? 'true' : 'false' }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-calendar-event"></i>
                        </span>
                        <span class="nav-link-title">Bookings</span>
                    </a>
                    <div class="dropdown-menu {{ request()->routeIs('admin.bookings.*') ? 'show' : '' }}">
                        <a class="dropdown-item {{ request()->routeIs('admin.bookings.index') ? 'active' : '' }}" href="{{ route('admin.bookings.index') }}">All Bookings</a>
                        <a class="dropdown-item {{ request()->routeIs('admin.bookings.active') ? 'active' : '' }}" href="{{ route('admin.bookings.active') }}">Active Care</a>
                        <a class="dropdown-item {{ request()->routeIs('admin.bookings.cancelled') ? 'active' : '' }}" href="{{ route('admin.bookings.cancelled') }}">Cancelled</a>
                    </div>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.requests.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-clipboard-list"></i>
                        </span>
                        <span class="nav-link-title">Care Requests</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.bids.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.bids.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-gavel"></i>
                        </span>
                        <span class="nav-link-title">Bids & Escrow</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.services.care-types.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.services.care-types.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-stethoscope"></i>
                        </span>
                        <span class="nav-link-title">Care Types</span>
                    </a>
                </li>

                {{-- ===================== --}}
                {{-- FINANCE --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">Finance</div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#navbar-finance" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-coin-rupee"></i>
                        </span>
                        <span class="nav-link-title">Payments & Payouts</span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">Transactions</a>
                        <a class="dropdown-item" href="#">Payouts</a>
                        <a class="dropdown-item" href="#">Refunds</a>
                    </div>
                </li>
                {{-- ===================== --}}
                {{-- SUPPORT --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">Support</div>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.support.index') || request()->routeIs('admin.support.show') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.support.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-headset"></i>
                        </span>
                        <span class="nav-link-title">Tickets</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.support.faqs.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.support.faqs.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-help"></i>
                        </span>
                        <span class="nav-link-title">FAQ</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.support.categories.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.support.categories.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-category"></i>
                        </span>
                        <span class="nav-link-title">Support Categories</span>
                    </a>
                </li>

                {{-- ===================== --}}
                {{-- SYSTEM --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">System</div>
                </li>

                <li class="nav-item dropdown {{ request()->routeIs('admin.system.*') ? 'active' : '' }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-system" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="{{ request()->routeIs('admin.system.*') ? 'true' : 'false' }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-server"></i>
                        </span>
                        <span class="nav-link-title">System</span>
                    </a>
                    <div class="dropdown-menu {{ request()->routeIs('admin.system.*') ? 'show' : '' }}">
                        <a class="dropdown-item {{ request()->routeIs('admin.system.error-logs') ? 'active' : '' }}" href="{{ route('admin.system.error-logs') }}">Error Logs</a>
                        <a class="dropdown-item {{ request()->routeIs('admin.system.communication-logs.*') ? 'active' : '' }}" href="{{ route('admin.system.communication-logs.index') }}">Communication Logs</a>
                        <a class="dropdown-item" href="{{ url('/api/documentation') }}" target="_blank">API</a>
                    </div>
                </li>

                <li class="nav-item dropdown {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-settings" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="{{ request()->routeIs('admin.settings.*') ? 'true' : 'false' }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-settings"></i>
                        </span>
                        <span class="nav-link-title">Settings</span>
                    </a>
                    <div class="dropdown-menu {{ request()->routeIs('admin.settings.*') ? 'show' : '' }}">
                        <a class="dropdown-item {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}" href="{{ route('admin.settings.general') }}">General</a>
                    </div>
                </li>

            </ul>
        </div>
    </div>
</aside>