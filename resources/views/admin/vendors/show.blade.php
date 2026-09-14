@extends('layouts.app')

@section('title', '{{ $vendor->company }} - Vendor Profile')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Vendor Header -->
            <div class="col-12 mb-5">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center me-4"
                            style="width: 100px; height: 100px;">
                            <i class="fas fa-users-cog fa-2x"></i>
                        </div>
                        <div>
                            <h1 class="h3 mb-1 fw-bold">{{ $vendor->user->name }}</h1>
                            <p class="mb-1 fw-semibold text-primary">Contact Name / Primary Identifier</p>
                            <div class="d-flex align-items-center gap-3 text-muted small">
                                <span><i class="fas fa-envelope me-1"></i>{{ $vendor->user->email }}</span>
                                @if ($vendor->phone)
                                    <span><i class="fas fa-mobile-alt me-1"></i>{{ $vendor->phone }}</span>
                                @endif
                                <span><i
                                        class="fas fa-calendar-day me-1"></i>{{ $vendor->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-warning px-4 py-2">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary px-4 py-2">
                            <i class="fas fa-list me-1"></i>Vendors List
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Main Profile Info -->
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0 h-100">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-building me-2 text-success"></i>Company Profile</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-light rounded-circle p-3 me-3 d-flex align-items-center justify-content-center"
                                            style="width: 60px; height: 60px;">
                                            <i class="fas fa-id-badge text-primary fa-lg"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">Particulars</h6>
                                            <p class="mb-0 text-muted fs-5">{{ $vendor->particulars ?: 'Not provided' }}</p>
                                            <span class="badge {{ $vendor->vendor_type === 'FOUNDRY' ? 'bg-primary' : 'bg-secondary' }} mt-2">Vendor Type: {{ $vendor->vendor_type ?: 'Not set' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-light rounded-circle p-3 me-3 d-flex align-items-center justify-content-center"
                                            style="width: 60px; height: 60px;">
                                            <i class="fas fa-map-marker-alt text-success fa-lg"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">GST No</h6>
                                            <p class="mb-0 text-muted">{{ $vendor->gst_no ?: 'Not provided' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-light rounded-circle p-3 me-3 d-flex align-items-center justify-content-center"
                                            style="width: 60px; height: 60px;">
                                            <i class="fas fa-toggle-on text-info fa-lg"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">Status</h6>
                                            @if ($vendor->status === 'active')
                                                <span class="badge bg-success px-4 py-2 fs-6">
                                                    <i class="fas fa-check-circle me-2"></i>Active
                                                </span>
                                            @elseif($vendor->status === 'pending')
                                                <span class="badge bg-warning px-4 py-2 fs-6">
                                                    <i class="fas fa-clock me-2"></i>Pending Review
                                                </span>
                                            @else
                                                <span class="badge bg-danger px-4 py-2 fs-6">
                                                    <i class="fas fa-pause-circle me-2"></i>Suspended
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Stats -->
                <div class="col-lg-4">
                    <div class="card shadow-lg border-0 mb-4 d-none">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-cogs me-2 text-info"></i>Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-outline-warning">
                                    <i class="fas fa-edit me-2"></i>Edit Profile
                                </a>
                                <form action="{{ route('vendors.toggle-status', $vendor) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('POST')
                                    <button type="submit"
                                        class="btn {{ $vendor->status == 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        <i class="fas fa-toggle-{{ $vendor->status == 'active' ? 'off' : 'on' }} me-2"></i>
                                        {{ $vendor->status == 'active' ? 'Suspend' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('vendors.destroy', $vendor) }}" method="POST"
                                    style="display: inline;" onsubmit="return confirm('Delete this vendor permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-trash me-2"></i>Delete Vendor
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Timeline -->
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Recent Activity</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-icon bg-success">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <small class="text-muted">{{ $vendor->created_at->format('M d, Y H:i') }}</small>
                                        <p class="mb-0">Vendor account created</p>
                                    </div>
                                </div>
                                @if ($vendor->updated_at->gt($vendor->created_at))
                                    <div class="timeline-item">
                                        <div class="timeline-icon bg-info">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <small
                                                class="text-muted">{{ $vendor->updated_at->format('M d, Y H:i') }}</small>
                                            <p class="mb-0">Profile updated</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Data (Future) -->
            <div class="row mt-5">
                <div class="col-12">
                    <h4 class="mb-4 fw-bold">
                        <i class="fas fa-clipboard-list me-2 text-info"></i>Associated Data
                    </h4>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Orders, Projects, and Customer associations will appear here.
                    </div>
                </div>
            </div>
        </div>

        <style>
            .timeline-item {
                display: flex;
                margin-bottom: 1.5rem;
                position: relative;
            }

            .timeline-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 0.875rem;
                flex-shrink: 0;
                margin-right: 1rem;
            }

            .timeline-content {
                flex: 1;
            }

            .bg-success {
                background-color: #28a745 !important;
            }

            .bg-info {
                background-color: #17a2b8 !important;
            }
        </style>
    @endsection
