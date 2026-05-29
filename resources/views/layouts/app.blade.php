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
        }
        .dark table tbody{
            color: #ffffff
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
            style="width: 300px;">
            <div class="p-3">
                <h6 class="text-uppercase text-muted mb-3"></h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i><span class="sidebar-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('enquiries.*') ? 'active' : '' }}"
                            href="{{ route('enquiries.index') }}">
                            <i class="fas fa-file-invoice me-2"></i><span class="sidebar-text">Enquiries</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('quotations.*') ? 'active' : '' }}"
                            href="{{ route('quotations.index') }}">
                            <i class="fas fa-file-invoice-dollar me-2"></i><span class="sidebar-text">Quotations</span>
                        </a>
                    </li>
<li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('sales-orders.*') ? 'active' : '' }}"
                            href="{{ route('sales-orders.index') }}">
                            <i class="fas fa-shopping-cart me-2"></i><span class="sidebar-text">Sales Orders</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}"
                            href="{{ route('invoices.index') }}">
                            <i class="fas fa-file-invoice-dollar me-2"></i><span class="sidebar-text">Invoices</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('procurements.*') ? 'active' : '' }}"
                            href="{{ route('procurements.index') }}">
                            <i class="fas fa-file-invoice me-2"></i><span class="sidebar-text">Procurements</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inventories.*') ? 'active' : '' }}"
                            href="{{ route('inventories.index') }}">
                            <i class="fas fa-boxes me-2"></i><span class="sidebar-text">Inventory</span>
                        </a>
                    </li>
                    <li class="nav-item my-2 border-top border-secondary"></li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}"
                            href="{{ route('vendors.index') }}">
                            <i class="fas fa-truck me-2"></i><span class="sidebar-text">Vendors</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                            href="{{ route('users.index') }}">
                            <i class="fas fa-users me-2"></i><span class="sidebar-text">Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
                            href="{{ route('customers.index') }}">
                            <i class="fas fa-user-tie me-2"></i><span class="sidebar-text">Customers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                            href="{{ route('products.index') }}">
                            <i class="fas fa-boxes me-2"></i><span class="sidebar-text">Products</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}"
                            href="{{ route('units.index') }}">
                            <i class="fas fa-ruler-combined me-2"></i><span class="sidebar-text">Units</span>
                        </a>
                    </li>
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

            // Force Dark Mode Default
            body.classList.add('dark');
            localStorage.setItem('theme', 'dark');
            darkToggle.querySelector('i').className = 'fas fa-sun fs-5 theme-icon';

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
