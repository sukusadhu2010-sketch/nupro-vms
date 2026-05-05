@extends('layouts.app')

@section('title', 'Edit Vendor - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-gradient-warning text-dark p-4 rounded-top-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0 fw-bold">
                                    <i class="fas fa-user-edit me-3"></i>
                                    Edit Vendor: {{ $vendor->company }}
                                </h3>
                                <p class="mb-0 opacity-75">Update vendor account and company details</p>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('vendors.index') }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Vendors
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <form action="{{ route('vendors.update', $vendor) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <!-- Personal Info -->
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-uppercase text-primary mb-3">
                                        <i class="fas fa-user me-2"></i>Personal Information
                                    </h6>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Full Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name"
                                            class="form-control form-control-lg @error('name') is-invalid @enderror"
                                            value="{{ old('name', $vendor->user->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Email Address <span
                                                class="text-danger">*</span></label>
                                        <input type="email" name="email"
                                            class="form-control form-control-lg @error('email') is-invalid @enderror"
                                            value="{{ old('email', $vendor->user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">Phone</label>
                                        <input type="tel" name="phone"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            value="{{ old('phone', $vendor->phone) }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Company Info -->
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-uppercase text-success mb-3">
                                        <i class="fas fa-building me-2"></i>Company Information
                                    </h6>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Company Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="company"
                                            class="form-control form-control-lg @error('company') is-invalid @enderror"
                                            value="{{ old('company', $vendor->company) }}" required>
                                        @error('company')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Specialization <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="specialization"
                                            class="form-control form-control-lg @error('specialization') is-invalid @enderror"
                                            value="{{ old('specialization', $vendor->specialization) }}" required>
                                        @error('specialization')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Address</label>
                                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $vendor->address) }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="col-12">
                                    <h6 class="fw-bold text-uppercase text-info mb-4">
                                        <i class="fas fa-cogs me-2"></i>Account Status
                                    </h6>

                                    <div class="row g-3 mb-5">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="status"
                                                class="form-select form-select-lg @error('status') is-invalid @enderror"
                                                required>
                                                <option value="">Select Status</option>
                                                <option value="pending"
                                                    {{ old('status', $vendor->status) == 'pending' ? 'selected' : '' }}>
                                                    Pending</option>
                                                <option value="active"
                                                    {{ old('status', $vendor->status) == 'active' ? 'selected' : '' }}>
                                                    Active</option>
                                                <option value="suspended"
                                                    {{ old('status', $vendor->status) == 'suspended' ? 'selected' : '' }}>
                                                    Suspended</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Section -->
                            <div class="d-flex gap-2 pt-4">
                                <button type="submit" class="btn btn-success  px-4 py-2 shadow me-2">
                                    <i class="fas fa-save me-2"></i>
                                    Update Vendor
                                </button>
                                <a href="{{ route('vendors.index') }}"
                                    class="btn btn-outline-secondary  px-4 py-2 shadow me-2">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <a href="{{ route('vendors.show', $vendor) }}"
                                    class="btn btn-outline-info  px-4 py-2 shadow me-2">
                                    <i class="fas fa-eye me-2"></i>View Profile
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                // Smooth eye-candy animations for buttons
                document.querySelectorAll('.btn').forEach(btn => {
                    btn.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-2px)';
                    });
                    btn.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateY(0)';
                    });
                });

                // Form focus effects
                document.querySelectorAll('.form-control').forEach(input => {
                    input.addEventListener('focus', function() {
                        this.parentNode.classList.add('focused');
                    });
                    input.addEventListener('blur', function() {
                        if (this.value === '') {
                            this.parentNode.classList.remove('focused');
                        }
                    });
                });
            </script>
        @endpush
    @endsection
