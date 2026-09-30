<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - BrightSmile Dental Clinic</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="app-sidebar no-print">
            <div class="sidebar-header">
                <div class="brand-icon">
                    <i class="fa-solid fa-tooth"></i>
                </div>
                <div class="brand-info">
                    <h1>BrightSmile</h1>
                    <span>Dental Clinic System</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section-title">Clinical & Operations</div>
                
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie icon"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('patients.index') }}" class="nav-item {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-hospital-user icon"></i>
                    <span>Patients & Charts</span>
                </a>

                <a href="{{ route('appointments.index') }}" class="nav-item {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check icon"></i>
                    <span>Appointments</span>
                </a>

                <a href="{{ route('queue.index') }}" class="nav-item {{ request()->routeIs('queue.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-line icon"></i>
                    <span>Waiting Queue</span>
                </a>

                <a href="{{ route('recalls.index') }}" class="nav-item {{ request()->routeIs('recalls.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left icon"></i>
                    <span>Follow-Ups & Recalls</span>
                </a>

                <a href="{{ route('lab-cases.index') }}" class="nav-item {{ request()->routeIs('lab-cases.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-flask-vial icon"></i>
                    <span>Dental Lab Cases</span>
                </a>

                <a href="{{ route('consent-forms.index') }}" class="nav-item {{ request()->routeIs('consent-forms.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-signature icon"></i>
                    <span>Digital Consents</span>
                </a>

                <div class="nav-section-title">Inventory & Supplies</div>

                <a href="{{ route('inventory.index') }}" class="nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-stacked icon"></i>
                    <span>Supply Inventory</span>
                </a>

                <a href="{{ route('purchase-orders.index') }}" class="nav-item {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-cart-flatbed icon"></i>
                    <span>Purchase Orders</span>
                </a>

                <a href="{{ route('suppliers.index') }}" class="nav-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-truck-field icon"></i>
                    <span>Suppliers</span>
                </a>

                <div class="nav-section-title">Finance & Billing</div>

                <a href="{{ route('invoices.index') }}" class="nav-item {{ request()->routeIs('invoices.*') || request()->routeIs('payments.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar icon"></i>
                    <span>Invoices & Payments</span>
                </a>

                @if(auth()->user()->hasRole(['administrator', 'dentist', 'cashier']))
                <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line icon"></i>
                    <span>Reports & Analytics</span>
                </a>
                @endif

                @if(auth()->user()->isAdmin())
                <div class="nav-section-title">System Administration</div>

                <a href="{{ route('services.index') }}" class="nav-item {{ request()->routeIs('services.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-notes-medical icon"></i>
                    <span>Services & Pricing</span>
                </a>

                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-doctor icon"></i>
                    <span>Staff & Schedules</span>
                </a>

                <a href="{{ route('audit-logs.index') }}" class="nav-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved icon"></i>
                    <span>Audit Trail (RA 10173)</span>
                </a>

                <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gears icon"></i>
                    <span>Settings & Backups</span>
                </a>
                @endif
            </nav>

            <div class="sidebar-footer">
                <div class="user-snippet">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="user-details">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role-badge">{{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Logout" style="padding: 6px 8px; border: none; background: transparent; color: #94a3b8;">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="app-main">
            <!-- Header -->
            <header class="app-header no-print">
                <div class="header-search">
                    <form action="{{ route('patients.index') }}" method="GET">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" name="search" placeholder="Search patient name, ID, or phone..." value="{{ request('search') }}">
                    </form>
                </div>

                <div class="header-actions">
                    <div class="dpa-badge" title="Compliant with Republic Act No. 10173 - Philippine Data Privacy Act">
                        <i class="fa-solid fa-shield-check"></i>
                        <span>RA 10173 Compliant</span>
                    </div>

                    <a href="{{ route('patients.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>New Patient</span>
                    </a>

                    <a href="{{ route('appointments.create') }}" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-calendar-plus"></i>
                        <span>Book Appointment</span>
                    </a>
                </div>
            </header>

            <!-- Body Wrapper -->
            <main class="content-wrapper">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <div>
                            <ul style="margin-left: 20px;">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
