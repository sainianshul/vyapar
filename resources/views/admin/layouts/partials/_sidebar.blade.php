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
                {{-- USERS --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">Users</div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.users.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-users"></i>
                        </span>
                        <span class="nav-link-title">All Users</span>
                    </a>
                </li>
                

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.users.blocked') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-user-off"></i>
                        </span>
                        <span class="nav-link-title">Blocked Users</span>
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
                        <span class="nav-link-title">Category</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.products.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-packages"></i>
                        </span>
                        <span class="nav-link-title">Product</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.requirements.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-clipboard-list"></i>
                        </span>
                        <span class="nav-link-title">Requirements</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.banners.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-photo"></i>
                        </span>
                        <span class="nav-link-title">Banners</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.feedbacks.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-message-report"></i>
                        </span>
                        <span class="nav-link-title">Feedbacks</span>
                    </a>
                </li>

                {{-- ===================== --}}
                {{-- TRASH --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">Trash</div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.users.deleted') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-trash"></i>
                        </span>
                        <span class="nav-link-title">Deleted User</span>
                    </a>
                </li>

                {{-- ===================== --}}
                {{-- SYSTEM --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">System</div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.communication-logs.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-message-dots"></i>
                        </span>
                        <span class="nav-link-title">Communication Logs</span>
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