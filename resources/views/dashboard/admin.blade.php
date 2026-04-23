@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="glass p-3">
        <div class="row mb-3">
            <div class="col">
                <h1 class="h2 mb-1"><i class="fas fa-chart-line me-2 text-primary"></i>Admin Dashboard</h1>
                <p class="text-muted">Welcome back, Admin! Here's what's happening in VMS Pro.</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4 text-white thumb-tiles">
            <div class="col-lg-3 col-md-6">
                <div class="card glass h-100">
                    <div class="card-body text-center p-3 ">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h4 class="fw-bold">{{ $stats['total_users'] }}</h4>
                        <p class="text-muted mb-0 small">Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-truck fa-3x text-success mb-3"></i>
                        <h3 class="h4 fw-bold">{{ $stats['total_vendors'] }}</h3>
                        <p class="text-muted mb-0">Vendors</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie fa-3x text-info mb-3"></i>
                        <h3 class="h4 fw-bold">{{ $stats['total_customers'] }}</h3>
                        <p class="text-muted mb-0">Customers</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-crown fa-3x text-warning mb-3"></i>
                        <h3 class="h4 fw-bold">{{ $stats['total_roles'] }}</h3>
                        <p class="text-muted mb-0">Roles</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity / Tables -->
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-clock me-2 text-primary"></i>Recent Vendors</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>Status</th>
                                        <th>Phone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (\App\Models\Vendor::with('user')->latest()->take(5)->get() as $vendor)
                                        <tr>
                                            <td>
                                                <div>
                                                    <img src="https://ui-avatars.com/api/?name={{ $vendor->name }}&background=667eea&color=fff&size=32"
                                                        class="rounded-circle me-2" width="32">
                                                    {{ $vendor->name }}
                                                </div>
                                            </td>
                                            <td>{{ $vendor->company }}</td>
                                            <td><span
                                                    class="badge {{ $vendor->status == 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($vendor->status) }}</span>
                                            </td>
                                            <td>{{ $vendor->phone }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2 text-info"></i>Role Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="roleChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('roleChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Admin', 'Vendor', 'Customer'],
                    datasets: [{
                        data: [{{ $stats['total_users'] }},
                            {{ $stats['total_vendors'] }},
                            {{ $stats['total_customers'] }}
                        ],
                        backgroundColor: ['#667eea', '#f093fb', '#4facfe'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection
