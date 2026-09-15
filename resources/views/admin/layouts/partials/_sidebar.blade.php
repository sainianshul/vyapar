<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ url('/admin') }}">
                VVyaparMitra
            </a>
        </h1>
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                
                {{-- ===================== --}}
                {{-- CORE --}}
                {{-- ===================== --}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/admin') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-home"></i>
                        </span>
                        <span class="nav-link-title">Dashboard</span>
                    </a>
                </li>
                
                {{-- ===================== --}}
                {{-- USERS & PROFILES --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">Users & Profiles</div>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#navbar-users" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false" >
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-users"></i>
                        </span>
                        <span class="nav-link-title">Users (Buyers/Sellers)</span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                            All Users
                        </a>
                        <a class="dropdown-item" href="{{ route('admin.users.blocked') }}">
                            Blocked
                        </a>
                        <a class="dropdown-item" href="{{ route('admin.users.deleted') }}">
                            Deleted
                        </a>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-building-store"></i>
                        </span>
                        <span class="nav-link-title">Company Profiles</span>
                    </a>
                </li>

                {{-- ===================== --}}
                {{-- CATALOG --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">Catalog</div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.categories.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-category"></i>
                        </span>
                        <span class="nav-link-title">Categories</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-packages"></i>
                        </span>
                        <span class="nav-link-title">Products / Services</span>
                    </a>
                </li>

                {{-- ===================== --}}
                {{-- LEADS & BUSINESS --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">Leads & Business</div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-bulb"></i>
                        </span>
                        <span class="nav-link-title">Buy Leads / Enquiries</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-wallet"></i>
                        </span>
                        <span class="nav-link-title">Wallets & Credits</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-credit-card"></i>
                        </span>
                        <span class="nav-link-title">Subscriptions</span>
                    </a>
                </li>

                {{-- ===================== --}}
                {{-- SYSTEM --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">System</div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-server"></i>
                        </span>
                        <span class="nav-link-title">Error Logs</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/api/documentation') }}" target="_blank">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-api-app"></i>
                        </span>
                        <span class="nav-link-title">API Docs (Swagger)</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</aside>