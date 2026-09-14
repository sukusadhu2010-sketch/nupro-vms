@extends('layouts.app')

@section('title', 'Vendor Dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h2 mb-1"><i class="fas fa-truck me-2 text-success"></i>Vendor Dashboard</h1>
                <p class="text-muted">Manage your vendor profile and operations.</p>
            </div>
        </div>

        <!-- Vendor Profile Card -->
        <div class="row g-4 mb-5">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-user-tie me-2 text-success"></i>My Profile</h5>
                        <a href="#" class="btn btn-outline-success btn-sm">Edit Profile</a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-4">
                                <img src="https://ui-avatars.com/api/?name={{ $vendor->name }}&background=f093fb&color=fff&size=120"
                                    class="rounded-circle img-fluid mx-auto mb-3" alt="{{ $vendor->name }}">
                                <h5 class="fw-bold">{{ $vendor->name }}</h5>
                                <span class="badge bg-success mb-2">Active Vendor</span>
                                <p class="text-muted">{{ $vendor->specialization }}</p>
                            </div>
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Company</label>
                                        <p class="h6">{{ $vendor->company ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Email</label>
                                        <p class="h6">{{ $vendor->email }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Phone</label>
                                        <p class="h6">{{ $vendor->phone ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Status</label>
                                        <span class="badge bg-success fs-6">{{ ucfirst($vendor->status) }}</span>
                                    </div>
                                    <div class="col-12">
                                        <label class="fw-semibold text-muted">Address</label>
                                        <p>{{ $vendor->address ?? 'N/A' }}</p>
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
                        <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2 text-success"></i>Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total Orders</span>
                            <span class="fw-bold h5 text-success">24</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Pending</span>
                            <span class="fw-bold h5 text-warning">3</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Active Contracts</span>
                            <span class="fw-bold h5 text-primary">7</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Rating</span>
                            <div class="star-rating">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="far fa-star text-muted"></i>
                                <span class="ms-2 fw-bold">4.2</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recent Activity -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0"><i class="fas fa-bolt me-2 text-primary"></i>Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <a href="#" class="d-block p-3 border-bottom text-decoration-none">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-plus-circle text-success fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-1">Add New Service</h6>
                                    <p class="text-muted mb-0 small">Offer new vendor services</p>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="d-block p-3 border-bottom text-decoration-none">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-invoice text-info fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-1">View Invoices</h6>
                                    <p class="text-muted mb-0 small">Manage billing and payments</p>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="d-block p-3 text-decoration-none">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-users text-warning fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-1">Customer Directory</h6>
                                    <p class="text-muted mb-0 small">Browse active customers</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0"><i class="fas fa-history me-2 text-secondary"></i>Recent Activity</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action border-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">New order from ABC Corp</h6>
                                    <small>2h ago</small>
                                </div>
                                <small class="text-muted">Service delivery scheduled</small>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Profile updated</h6>
                                    <small>1 day ago</small>
                                </div>
                                <small class="text-muted">Contact details changed</small>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Payment received</h6>
                                    <small>3 days ago</small>
                                </div>
                                <small class="text-muted">₹2,500 invoice cleared</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
