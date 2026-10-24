<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'VMS Pro') }} - @yield('title', 'Dashboard')</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Main Theme CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">


<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- Perfect Scrollbar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.5/css/perfect-scrollbar.min.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        .flex-grow {
            flex-grow: 1;
            min-width: 0;
            width: 100%;
        }
        .dark table tbody{
            color: var(--text-primary, #e5e7eb);
        }
    </style>
    @stack('styles')

</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg text-white"
        style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); box-shadow: var(--shadow-md);">
        <div class="container-fluid">
            <!-- Sidebar Toggle (Desktop/Mobile) -->
            <button class="btn btn-link text-white p-2 sidebar-toggle d-lg-none me-2" id="sidebarToggle"
                title="Toggle Sidebar">
                <i class="fas fa-bars fs-5"></i>
            </button>
            <button class="btn btn-link text-white p-2 sidebar-toggle d-none d-lg-block me-2" id="sidebarCollapse"
                title="Collapse Sidebar">
                <i class="fas fa-outdent fs-5 sidebar-icon"></i>
            </button>
            <a class="navbar-brand fw-bold fs-4 text-white" href="{{ route('dashboard') }}">
                <i class="fas fa-users-cog me-2"></i>VMS Pro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <!-- Dark Mode Toggle -->
                    <li class="nav-item">
                        <button class="btn btn-link text-white-50 nav-link p-2 dark-toggle" id="darkToggle"
                            title="Toggle Dark Mode">
                            <i class="fas fa-sun fs-5 theme-icon"></i>
                        </button>
                    </li>
                    <li class="nav-item dropdown">

                        <a class="nav-link dropdown-toggle d-flex align-items-center text-white" href="#"
                            role="button" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=667eea&color=fff&size=32"
                                class="rounded-circle me-2" alt="{{ Auth::user()->name }}">
                            {{ Auth::user()->name }}
                            @if (Auth::user()->hasRole('admin'))
                                <span class="badge bg-danger ms-1">Admin</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end text-white">
                            <li><a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                        </ul>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar bg-light border-end vh-100 ps ps-show-scrollbar d-none d-lg-block" id="sidebar"
            style="width: 300px; overflow-y: auto;">
            <div class="p-3">
                @php($org = \App\Models\OrganizationSetting::current())
                @php($u = auth()->user())
                <div class="text-center mb-3">
                    @if ($org->logo_path)
                        <img src="{{ asset('storage/' . $org->logo_path) }}" alt="{{ $org->name }}" style="max-height: 55px; max-width: 180px;">
                    @else
                        <span class="fw-bold text-primary">{{ $org->name }}</span>
                    @endif
                </div>
                <ul class="nav flex-column pb-5">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2 text-primary"></i><span class="sidebar-text">Dashboard</span>
                        </a>
                    </li>

                    {{-- SALES & CRM --}}
                    @if ($u?->canAccess('enquiries') || $u?->canAccess('quotations') || $u?->canAccess('sales-orders') || $u?->canAccess('customers') || $u?->canAccess('invoices'))
                    <li class="nav-header text-uppercase text-muted fw-bold px-3 pt-3 pb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Sales & CRM</li>
                    @if ($u?->canAccess('customers'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                            href="{{ route('customers.index') }}">
                            <i class="fas fa-user-tie me-2"></i><span class="sidebar-text">Customers</span>
                        </a>
                    </li>
                    @endif
                    @if ($u?->canAccess('enquiries'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('enquiries.*') ? 'active' : '' }}"
                            href="{{ route('enquiries.index') }}">
                            <i class="fas fa-file-invoice me-2"></i><span class="sidebar-text">Enquiries</span>
                        </a>
                    </li>
                    @endif
                    @if ($u?->canAccess('quotations'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('quotations.*') ? 'active' : '' }}"
                            href="{{ route('quotations.index') }}">
                            <i class="fas fa-file-invoice-dollar me-2"></i><span class="sidebar-text">Quotations</span>
                        </a>
                    </li>
                    @endif
                    @if ($u?->canAccess('sales-orders'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('sales-orders.*') ? 'active' : '' }}"
                            href="{{ route('sales-orders.index') }}">
                            <i class="fas fa-shopping-cart me-2"></i><span class="sidebar-text">Sales Orders</span>
                        </a>
                    </li>
                    @endif
                    @if ($u?->canAccess('invoices'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}"
                            href="{{ route('invoices.index') }}">
                            <i class="fas fa-file-invoice-dollar me-2"></i><span class="sidebar-text">Sales Invoices</span>
                        </a>
                    </li>
                    @endif
                    @endif

                    {{-- PURCHASE & VENDORS --}}
                    @if ($u?->canAccess('procurements') || $u?->canAccess('vendors'))
                    <li class="nav-header text-uppercase text-muted fw-bold px-3 pt-3 pb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Purchase & Vendors</li>
                    @if ($u?->canAccess('procurements'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('procurements.*') ? 'active' : '' }}"
                            href="{{ route('procurements.index') }}">
                            <i class="fas fa-file-invoice me-2"></i><span class="sidebar-text">Purchase Orders</span>
                        </a>
                    </li>
                    @endif
                    @if ($u?->canAccess('vendors'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('foundry-vendors.*') ? 'active' : '' }}"
                            href="{{ route('foundry-vendors.index') }}">
                            <i class="fas fa-industry me-2"></i><span class="sidebar-text">Foundry Vendors</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('sub-vendors.*') ? 'active' : '' }}"
                            href="{{ route('sub-vendors.index') }}">
                            <i class="fas fa-sitemap me-2"></i><span class="sidebar-text">Sub Vendors</span>
                        </a>
                    </li>
                    @endif
                    @endif

                    {{-- ACCOUNTS & FINANCE --}}
                    @if ($u?->canAccess('invoices') || $u?->canAccess('receipts') || $u?->canAccess('vendor-payments'))
                    <li class="nav-header text-uppercase text-muted fw-bold px-3 pt-3 pb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Accounts & Finance</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('accounts.dashboard') ? 'active' : '' }}"
                            href="{{ route('accounts.dashboard') }}">
                            <i class="fas fa-chart-line me-2 text-primary"></i><span class="sidebar-text">Accounts Dashboard</span>
                        </a>
                    </li>
                    @if ($u?->canAccess('receipts'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('receipts.*') ? 'active' : '' }}"
                            href="{{ route('receipts.index') }}">
                            <i class="fas fa-hand-holding-usd me-2 text-success"></i><span class="sidebar-text">Customer Receipts</span>
                        </a>
                    </li>
                    @endif
                    @if ($u?->canAccess('vendor-payments'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vendor-payments.*') ? 'active' : '' }}"
                            href="{{ route('vendor-payments.index') }}">
                            <i class="fas fa-money-check-alt me-2 text-danger"></i><span class="sidebar-text">Vendor Payments</span>
                        </a>
                    </li>
                    @endif
                    @endif

                    {{-- INVENTORY & STORES --}}
                    @if ($u?->canAccess('inventory-ledger') || $u?->canAccess('products'))
                    <li class="nav-header text-uppercase text-muted fw-bold px-3 pt-3 pb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Inventory & Stores</li>
                    @if ($u?->canAccess('inventory-ledger'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inventory-ledger.*') ? 'active' : '' }}"
                            href="{{ route('inventory-ledger.index') }}">
                            <i class="fas fa-boxes me-2"></i><span class="sidebar-text">Inventory Ledger</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inventory.po-tracking') ? 'active' : '' }}"
                            href="{{ route('inventory.po-tracking') }}">
                            <i class="fas fa-chart-pie me-2"></i><span class="sidebar-text">PO Consumption Tracking</span>
                        </a>
                    </li>
                    @endif
                    @if ($u?->canAccess('products'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                            href="{{ route('products.index') }}">
                            <i class="fas fa-box-open me-2"></i><span class="sidebar-text">Products</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('product-categories.*') ? 'active' : '' }}"
                            href="{{ route('product-categories.index') }}">
                            <i class="fas fa-tags me-2"></i><span class="sidebar-text">Product Categories</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}"
                            href="{{ route('units.index') }}">
                            <i class="fas fa-ruler-combined me-2"></i><span class="sidebar-text">Units</span>
                        </a>
                    </li>
                    @endif
                    @endif

                    {{-- REPORTS CENTER --}}
                    <li class="nav-header text-uppercase text-muted fw-bold px-3 pt-3 pb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Reports Center</li>
                    @if ($u?->canAccess('roles') || $u?->canAccess('users'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.rbac.*') ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#rbacReportsCollapse" role="button">
                            <i class="fas fa-user-lock me-2 text-warning"></i><span class="sidebar-text">RBAC Reports</span>
                            <i class="fas fa-chevron-down ms-auto small"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('reports.rbac.*') ? 'show' : '' }}" id="rbacReportsCollapse">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.rbac.role-users') }}">&bull; Role Wise Users</a></li>
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.rbac.permission-matrix') }}">&bull; Permission Matrix</a></li>
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.rbac.user-access') }}">&bull; User Access Report</a></li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if ($u?->canAccess('invoices'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.invoices.*') ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#invoiceReportsCollapse" role="button">
                            <i class="fas fa-file-invoice-dollar me-2 text-primary"></i><span class="sidebar-text">Invoice Reports</span>
                            <i class="fas fa-chevron-down ms-auto small"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('reports.invoices.*') ? 'show' : '' }}" id="invoiceReportsCollapse">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.invoices.register') }}">&bull; Invoice Register</a></li>
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.invoices.outstanding') }}">&bull; Outstanding Report</a></li>
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.invoices.tax-summary') }}">&bull; Tax Summary</a></li>
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.invoices.customer-ledger') }}">&bull; Customer Ledger</a></li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if ($u?->canAccess('receipts') || $u?->canAccess('vendor-payments'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.payments.*') ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#paymentReportsCollapse" role="button">
                            <i class="fas fa-receipt me-2 text-success"></i><span class="sidebar-text">Payment Reports</span>
                            <i class="fas fa-chevron-down ms-auto small"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('reports.payments.*') ? 'show' : '' }}" id="paymentReportsCollapse">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.payments.receipts-register') }}">&bull; Receipts Register</a></li>
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.payments.vendor-payment-register') }}">&bull; Vendor Payments</a></li>
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.payments.aging') }}">&bull; Aging Analysis</a></li>
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.payments.summary') }}">&bull; Receivables & Payables</a></li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if ($u?->canAccess('inventory-ledger'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.inventory.*') ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#inventoryReportsCollapse" role="button">
                            <i class="fas fa-warehouse me-2 text-info"></i><span class="sidebar-text">Inventory Reports</span>
                            <i class="fas fa-chevron-down ms-auto small"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('reports.inventory.*') ? 'show' : '' }}" id="inventoryReportsCollapse">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.inventory.ledger') }}">&bull; Inventory Ledger</a></li>
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.inventory.stock-movement') }}">&bull; Stock Movement</a></li>
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.inventory.po-report') }}">&bull; PO Inventory Report</a></li>
                                <li class="nav-item"><a class="nav-link py-1 small" href="{{ route('reports.inventory.item-ledger') }}">&bull; Item Ledger</a></li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if ($u?->canAccess('sd-pbg'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('sd-pbg.*') ? 'active' : '' }}"
                            href="{{ route('sd-pbg.index') }}">
                            <i class="fas fa-file-contract me-2"></i><span class="sidebar-text">SD/PBG Register</span>
                        </a>
                    </li>
                    @endif

                    {{-- ADMINISTRATION --}}
                    @if ($u?->canAccess('users') || $u?->canAccess('roles') || $u?->canAccess('audit-logs'))
                    <li class="nav-header text-uppercase text-muted fw-bold px-3 pt-3 pb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Administration</li>
                    @if ($u?->canAccess('users'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                            href="{{ route('users.index') }}">
                            <i class="fas fa-users me-2"></i><span class="sidebar-text">Users</span>
                        </a>
                    </li>
                    @endif
                    @if ($u?->canAccess('roles'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                            href="{{ route('roles.index') }}">
                            <i class="fas fa-user-shield me-2"></i><span class="sidebar-text">User Role Master</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}"
                            href="{{ route('permissions.index') }}">
                            <i class="fas fa-key me-2"></i><span class="sidebar-text">Permissions Master</span>
                        </a>
                    </li>
                    @endif
                    @if ($u?->canAccess('audit-logs') || $u?->canAccess('roles'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}"
                            href="{{ route('audit-logs.index') }}">
                            <i class="fas fa-history me-2 text-primary"></i><span class="sidebar-text">Audit Log</span>
                        </a>
                    </li>
                    @endif
                    @if ($u?->canAccess('roles'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('financial-years.*') ? 'active' : '' }}"
                            href="{{ route('financial-years.index') }}">
                            <i class="fas fa-calendar-alt me-2"></i><span class="sidebar-text">Financial Year Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('organization.*') ? 'active' : '' }}"
                            href="{{ route('organization.edit') }}">
                            <i class="fas fa-building me-2"></i><span class="sidebar-text">Organization Settings</span>
                        </a>
                    </li>
                    @endif
                    @endif

                    <li class="nav-item my-3 border-top"></li>
                    <li class="nav-item mt-auto">
                        <a class="nav-link text-danger" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i><span class="sidebar-text">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Page Content -->
        <main class="flex-grow p-3">
            @yield('content')
        </main>
    </div>

    <!-- Perfect Scrollbar JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.5/dist/perfect-scrollbar.min.js"></script>
<!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Pro UI Features
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.body;
            const darkToggle = document.getElementById('darkToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarCollapse = document.getElementById('sidebarCollapse');
            const sidebarIcon = document.querySelector('.sidebar-icon');

            // Dark Mode Toggle
            if (darkToggle) {
                const currentTheme = localStorage.getItem('theme') || (window.matchMedia(
                    '(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                body.classList.toggle('dark', currentTheme === 'dark');
                darkToggle.querySelector('i').className = currentTheme === 'dark' ? 'fas fa-sun fs-5 theme-icon' :
                    'fas fa-moon fs-5 theme-icon';

                darkToggle.addEventListener('click', function() {
                    body.classList.toggle('dark');
                    const theme = body.classList.contains('dark') ? 'dark' : 'light';
                    localStorage.setItem('theme', theme);
                    this.querySelector('i').className = theme === 'dark' ? 'fas fa-sun fs-5 theme-icon' :
                        'fas fa-moon fs-5 theme-icon';
                });
            }

            // Perfect Scrollbar
            if (sidebar) {
                new PerfectScrollbar(sidebar);
            }

            // Respect the user's saved theme (falls back to system preference)
            // The toggle button above handles switching.

            // Sidebar Toggle Mobile
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('d-lg-block');
                    sidebar.classList.toggle('d-none');
                });
            }

            // Sidebar Collapse Desktop
            if (sidebarCollapse) {
                sidebarCollapse.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-collapsed');
                    sidebarIcon.classList.toggle('fa-outdent');
                    sidebarIcon.classList.toggle('fa-indent');
                });
            }
        });
    </script>
    @stack('scripts')

</body>

</html>
