@extends('layouts.app')

@section('title', 'Customer Dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h2 mb-1"><i class="fas fa-user-tie me-2 text-info"></i>Customer Dashboard</h1>
                <p class="text-muted">Manage your account and discover vendors.</p>
            </div>
        </div>

        <!-- Customer Profile Card -->
        <div class="row g-4 mb-5">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-user me-2 text-info"></i>My Profile</h5>
                        <a href="#" class="btn btn-outline-info btn-sm">Edit Profile</a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-4">
                                <img src="https://ui-avatars.com/api/?name={{ $customer->name }}&background=4facfe&color=fff&size=120"
                                    class="rounded-circle img-fluid mx-auto mb-3" alt="{{ $customer->name }}">
                                <h5 class="fw-bold">{{ $customer->name }}</h5>
                                <span class="badge bg-info mb-2">Active Customer</span>
                                <p class="text-muted">{{ $customer->company }}</p>
                            </div>
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Company</label>
                                        <p class="h6">{{ $customer->company ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Email</label>
                                        <p class="h6">{{ $customer->email }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Phone</label>
                                        <p class="h6">{{ $customer->phone ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Status</label>
                                        <span class="badge bg-info fs-6">{{ ucfirst($customer->status) }}</span>
                                    </div>
                                    <div class="col-12">
                                        <label class="fw-semibold text-muted">Address</label>
                                        <p>{{ $customer->address ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2 text-info"></i>My Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Active Contracts</span>
                            <span class="fw-bold h5 text-primary">5</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total Spend</span>
                            <span class="fw-bold h5 text-success">$18,500</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Vendors Engaged</span>
                            <span class="fw-bold h5 text-warning">12</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Avg Rating</span>
                            <div class="star-rating">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <span class="ms-2 fw-bold">4.8</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('customer.products.index') }}"
                    class="card shadow-sm h-100 text-decoration-none hover-lift">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-boxes fa-3x text-success mb-3"></i>
                        <h6 class="fw-bold mb-1">Browse Products</h6>
                        <p class="text-muted small mb-0">Explore catalog and add to enquiry</p>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('customer.enquiries.create') }}"
                    class="card shadow-sm h-100 text-decoration-none hover-lift">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                        <h6 class="fw-bold mb-1">New Enquiry</h6>
                        <p class="text-muted small mb-0">Create multi-product enquiry</p>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('customer.enquiries.index') }}"
                    class="card shadow-sm h-100 text-decoration-none hover-lift">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-clipboard-list fa-3x text-info mb-3"></i>
                        <h6 class="fw-bold mb-1">My Enquiries</h6>
                        <p class="text-muted small mb-0">Track status & details</p>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="#" class="card shadow-sm h-100 text-decoration-none hover-lift">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-store fa-3x text-warning mb-3"></i>
                        <h6 class="fw-bold mb-1">Vendors</h6>
                        <p class="text-muted small mb-0">Manage contacts</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Vendor Directory -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-store me-2 text-success"></i>Featured Vendors</span>
                            <a href="#" class="btn btn-outline-success btn-sm">View All</a>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Vendor</th>
                                        <th>Specialization</th>
                                        <th>Company</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vendors as $vendor)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://ui-avatars.com/api/?name={{ $vendor->name }}&background=667eea&color=fff&size=36"
                                                        class="rounded-circle me-3" width="36">
                                                    <div>
                                                        <h6 class="mb-0">{{ $vendor->name }}</h6>
                                                        <small class="text-muted">{{ $vendor->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-light text-dark">{{ $vendor->specialization }}</span>
                                            </td>
                                            <td>{{ $vendor->company }}</td>
                                            <td><span class="badge bg-success">{{ ucfirst($vendor->status) }}</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">Contact</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
