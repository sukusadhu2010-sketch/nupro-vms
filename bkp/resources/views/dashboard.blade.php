@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h2 mb-1"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard</h1>
                <p class="text-muted">Welcome to VMS Pro. Select your role dashboard below.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 h-100 text-center shadow-sm hover-shadow-lg">
                    <div class="card-body py-5">
                        <i class="fas fa-crown fa-4x text-warning mb-4"></i>
                        <h3 class="h4 fw-bold mb-3">Admin Panel</h3>
                        <p class="text-muted mb-4">Full system management and analytics</p>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-warning btn-lg">Go to Admin</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 h-100 text-center shadow-sm hover-shadow-lg">
                    <div class="card-body py-5">
                        <i class="fas fa-truck fa-4x text-success mb-4"></i>
                        <h3 class="h4 fw-bold mb-3">Vendor Portal</h3>
                        <p class="text-muted mb-4">Manage services and contracts</p>
                        <a href="{{ route('vendor.dashboard') }}" class="btn btn-success btn-lg">Vendor Dashboard</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 h-100 text-center shadow-sm hover-shadow-lg">
                    <div class="card-body py-5">
                        <i class="fas fa-user-tie fa-4x text-info mb-4"></i>
                        <h3 class="h4 fw-bold mb-3">Customer Portal</h3>
                        <p class="text-muted mb-4">Browse vendors and manage orders</p>
                        <a href="{{ route('customer.dashboard') }}" class="btn btn-info btn-lg">Customer Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow-lg {
            transition: all 0.3s ease;
        }

        .hover-shadow-lg:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        }
    </style>
@endsection
