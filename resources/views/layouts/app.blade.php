<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CRM') - EstatePulse Real Estate CRM</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #1d4ed8;
            --brand-primary-hover: #1e40af;
            --brand-dark: #0f172a;
            --brand-surface: #ffffff;
            --brand-bg: #f8fafc;
            --brand-border: #e2e8f0;
            --brand-text: #0f172a;
            --brand-text-secondary: #475569;
            --brand-text-muted: #64748b;
            --sidebar-width: 240px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden !important;
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--brand-bg);
            color: var(--brand-text);
            font-size: 0.875rem;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background-color: #0f172a;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            border-right: 1px solid #1e293b;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar-brand {
            padding: 16px 18px;
            border-bottom: 1px solid #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand-logo {
            width: 32px;
            height: 32px;
            background: #1d4ed8;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.05rem;
        }

        .sidebar-section-title {
            padding: 14px 18px 4px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 8px 16px;
            font-weight: 500;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 1px 8px;
            border-radius: 6px;
            transition: all 0.15s ease-in-out;
        }

        .sidebar .nav-link i {
            font-size: 0.95rem;
            color: #64748b;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .sidebar .nav-link:hover i {
            color: #ffffff;
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #1d4ed8;
            font-weight: 600;
        }

        .sidebar .nav-link.active i {
            color: #ffffff;
        }

        .sidebar-footer {
            padding: 12px 14px;
            border-top: 1px solid #1e293b;
            background-color: #0b1120;
        }

        /* Main Content Layout - Strictly constrained to prevent horizontal scrolling */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
            max-width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
            overflow-x: hidden !important;
        }

        /* Top Header */
        .top-header {
            position: sticky;
            top: 0;
            z-index: 1020;
            background-color: #ffffff;
            border-bottom: 1px solid var(--brand-border);
            height: 56px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            max-width: 100%;
        }

        .page-content {
            padding: 18px 20px 40px;
            flex-grow: 1;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden !important;
        }

        /* Corporate Cards */
        .card {
            background-color: #ffffff;
            border: 1px solid var(--brand-border);
            border-radius: 8px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            margin-bottom: 18px;
            max-width: 100%;
        }

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid var(--brand-border);
            padding: 12px 16px;
            font-weight: 600;
        }

        .card-body {
            padding: 16px;
        }

        /* KPI Card */
        .kpi-card {
            border: 1px solid var(--brand-border);
            border-radius: 8px;
            background: #ffffff;
            padding: 16px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
        }

        .kpi-title {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--brand-text-muted);
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--brand-text);
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        /* Buttons */
        .btn {
            font-size: 0.8125rem;
            font-weight: 500;
            border-radius: 6px;
            padding: 5px 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.15s ease-in-out;
        }

        .btn-primary {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: var(--brand-primary-hover);
            border-color: var(--brand-primary-hover);
        }

        .btn-outline-secondary {
            border-color: #cbd5e1;
            color: var(--brand-text-secondary);
        }

        .btn-outline-secondary:hover {
            background-color: #f1f5f9;
            border-color: #94a3b8;
            color: var(--brand-text);
        }

        /* Enterprise Table - Fluid and never causing horizontal scroll */
        .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            margin-bottom: 0;
        }

        .table {
            color: var(--brand-text);
            margin-bottom: 0;
            font-size: 0.8125rem;
            width: 100%;
        }

        .table thead th {
            background-color: #f8fafc;
            border-bottom: 1px solid var(--brand-border);
            color: #64748b;
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 9px 12px;
        }

        .table tbody td {
            padding: 10px 12px;
            vertical-align: middle;
            border-bottom: 1px solid var(--brand-border);
        }

        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Status Pills */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 0.72rem;
            font-weight: 500;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .badge-hot-pro {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fee2e2;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 0.68rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }

        .form-control, .form-select {
            font-size: 0.8125rem;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            color: var(--brand-text);
        }

        .form-control:focus, .form-select:focus {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
        }

        /* Header search bar */
        .header-search {
            position: relative;
            width: 280px;
            max-width: 100%;
        }

        .header-search input {
            padding-left: 30px;
            padding-right: 30px;
            height: 32px;
            font-size: 0.8rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }

        .header-search i.search-icon {
            position: absolute;
            left: 9px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.8rem;
        }

        .kbd-badge {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: #e2e8f0;
            color: #64748b;
            font-size: 0.65rem;
            padding: 1px 4px;
            border-radius: 3px;
            font-family: monospace;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
                transition: margin 0.2s ease-in-out;
            }
            .sidebar.show {
                margin-left: 0;
            }
            .main-wrapper {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
            }
            .page-content {
                padding: 14px;
            }
            .header-search {
                display: none;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div>
            <!-- Brand -->
            <div class="sidebar-brand">
                <div class="sidebar-brand-logo">
                    <i class="bi bi-buildings"></i>
                </div>
                <div>
                    <div class="text-white fw-bold" style="font-size: 0.92rem; letter-spacing: -0.01em;">EstatePulse CRM</div>
                    <div style="font-size: 0.68rem; color: #94a3b8;">Realty Sales Suite</div>
                </div>
            </div>

            <!-- Navigation Sections -->
            <div class="sidebar-section-title">Core Operations</div>
            <nav class="nav flex-column">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}" href="{{ route('leads.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Leads Management</span>
                </a>
                <a class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}">
                    <i class="bi bi-building"></i>
                    <span>Projects & Inventory</span>
                </a>
                <a class="nav-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Bookings & Milestones</span>
                </a>
                <a class="nav-link {{ request()->routeIs('commissions.*') ? 'active' : '' }}" href="{{ route('commissions.index') }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Agent Commissions</span>
                </a>
            </nav>

            @if(Auth::check() && Auth::user()->role === 'admin')
                <div class="sidebar-section-title">Administration</div>
                <nav class="nav flex-column">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <i class="bi bi-person-gear"></i>
                        <span>Sales Team & Users</span>
                    </a>
                </nav>
            @endif

            <div class="sidebar-section-title">Integrations & API</div>
            <nav class="nav flex-column">
                <a class="nav-link {{ request()->routeIs('api.docs') ? 'active' : '' }}" href="{{ route('api.docs') }}">
                    <i class="bi bi-code-square"></i>
                    <span>Webhooks & REST API</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar User Footer -->
        <div class="sidebar-footer">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-semibold flex-shrink-0" style="width: 30px; height: 30px; font-size: 0.75rem; background-color: #334155;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-white fw-semibold text-truncate" style="font-size: 0.78rem;">{{ Auth::user()->name }}</div>
                        <div class="text-muted text-truncate" style="font-size: 0.68rem; color: #94a3b8 !important;">
                            {{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}
                        </div>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm text-secondary p-1 border-0" title="Sign Out">
                        <i class="bi bi-box-arrow-right fs-6 text-danger"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Corporate Header -->
        <header class="top-header">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <span class="text-muted small">CRM / </span>
                    <span class="fw-semibold text-dark small">@yield('page-title', 'Overview')</span>
                </div>
            </div>

            <!-- Global Search Form -->
            <form action="{{ route('leads.index') }}" method="GET" class="header-search">
                <i class="bi bi-search search-icon"></i>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search leads, phone, project...">
                <span class="kbd-badge">/</span>
            </form>

            <div class="d-flex align-items-center gap-2">
                <!-- Overdue Follow-ups Alert Bell Dropdown -->
                @php
                    $overdueBellQuery = \App\Models\Lead::whereNotNull('next_follow_up_date')
                        ->where('next_follow_up_date', '<=', now());
                    if (Auth::check() && Auth::user()->role === 'sales_agent') {
                        $overdueBellQuery->where('assigned_to', Auth::id());
                    }
                    $overdueBellCount = $overdueBellQuery->count();
                    $overdueBellLeads = $overdueBellQuery->with('project')->orderBy('next_follow_up_date', 'asc')->take(5)->get();
                @endphp

                <div class="dropdown me-1">
                    <button class="btn btn-sm btn-outline-secondary position-relative px-2 py-1 rounded-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ $overdueBellCount }} Follow-ups Due / Overdue">
                        <i class="bi bi-bell-fill fs-6 {{ $overdueBellCount > 0 ? 'text-danger' : 'text-muted' }}"></i>
                        @if($overdueBellCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.62rem;">
                                {{ $overdueBellCount > 99 ? '99+' : $overdueBellCount }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border p-0 rounded-3" style="min-width: 320px; font-size: 0.8125rem;">
                        <li class="p-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center rounded-top-3">
                            <span class="fw-bold small text-dark"><i class="bi bi-alarm text-danger me-1"></i> Due & Overdue Follow-ups</span>
                            <span class="badge {{ $overdueBellCount > 0 ? 'bg-danger text-white' : 'bg-success text-white' }}" style="font-size: 0.68rem;">
                                {{ $overdueBellCount }} Pending
                            </span>
                        </li>
                        @forelse($overdueBellLeads as $obl)
                            <li class="border-bottom">
                                <a class="dropdown-item py-2 px-3 text-wrap" href="{{ route('leads.show', $obl->id) }}">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <strong class="text-dark">{{ $obl->name }}</strong>
                                        <span class="text-danger small fw-semibold flex-shrink-0" style="font-size: 0.7rem;">
                                            {{ $obl->next_follow_up_date->diffForHumans() }}
                                        </span>
                                    </div>
                                    <div class="text-muted small mt-1 d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                                        <span><i class="bi bi-telephone text-primary me-1"></i>{{ $obl->phone }}</span>
                                        <span class="text-truncate" style="max-width: 140px;">{{ $obl->project?->name ?? ($obl->property_type ?? 'General') }}</span>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="p-3 text-center text-muted small">
                                <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                                All follow-ups are up to date!
                            </li>
                        @endforelse
                        <li class="p-2 text-center bg-light rounded-bottom-3">
                            <a href="{{ route('leads.index', ['due_today' => 1]) }}" class="text-primary small fw-semibold text-decoration-none">
                                View All Overdue In Pipeline &rarr;
                            </a>
                        </li>
                    </ul>
                </div>

                <span class="d-none d-md-inline-block text-muted me-2" style="font-size: 0.75rem;">
                    <i class="bi bi-calendar3 me-1"></i> {{ now()->format('D, d M Y') }}
                </span>

                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createLeadModal">
                    <i class="bi bi-plus-lg"></i>
                    <span>New Lead</span>
                </button>
            </div>
        </header>

        <!-- Page Body Content -->
        <div class="page-content">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 mb-3 border-0 rounded-2" role="alert" style="background-color: #ecfdf5; border-left: 3px solid #10b981 !important;">
                    <i class="bi bi-check-circle-fill text-success fs-6"></i>
                    <div class="small fw-medium text-success-emphasis">{{ session('success') }}</div>
                    <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3 border-0 rounded-2" role="alert" style="background-color: #fef2f2; border-left: 3px solid #ef4444 !important;">
                    <div class="d-flex align-items-center gap-2 mb-1 small fw-semibold text-danger">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                        <span>Validation errors found:</span>
                    </div>
                    <ul class="mb-0 ps-3 small text-danger-emphasis">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Create Lead Modal (Global quick modal) -->
    @include('leads._create_modal')

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
