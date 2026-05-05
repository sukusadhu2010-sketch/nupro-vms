@extends('layouts.app')

@section('title', 'Edit Customer - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-user-edit me-2 text-warning"></i>
                                    Edit Customer: {{ $customer->company }}
                                </h4>
                                <p class="mb-0 text-muted">Update customer information and status</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye me-2"></i>View Profile
                                </a>
                                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Customers
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <form method="POST" action="{{ route('customers.update', $customer) }}" id="customerEditForm">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <!-- Basic Info -->
                                <div class="col-lg-6">
                                    <label class="form-label fw-bold mb-3">Basic Information</label>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="name" class="form-label fw-semibold">Full Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control form-control-lg @error('name') is-invalid @enderror"
                                                id="name" name="name"
                                                value="{{ old('name', $customer->user->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12">
                                            <label for="email" class="form-label fw-semibold">Email Address <span
                                                    class="text-danger">*</span></label>
                                            <input type="email"
                                                class="form-control form-control-lg @error('email') is-invalid @enderror"
                                                id="email" name="email"
                                                value="{{ old('email', $customer->user->email) }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12">
                                            <label for="phone" class="form-label fw-semibold">Phone</label>
                                            <input type="tel" class="form-control form-control-lg" id="phone"
                                                name="phone" value="{{ old('phone', $customer->phone) }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Company Info -->
                                <div class="col-lg-6">
                                    <label class="form-label fw-bold mb-3">Company Details</label>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="company" class="form-label fw-semibold">Company Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control form-control-lg @error('company') is-invalid @enderror"
                                                id="company" name="company"
                                                value="{{ old('company', $customer->company) }}" required>
                                            @error('company')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12">
                                            <label for="address" class="form-label fw-semibold">Business Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $customer->address) }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="status" class="form-label fw-semibold">Status <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select form-select-lg @error('status') is-invalid @enderror"
                                                id="status" name="status" required>
                                                <option value="">Select Status</option>
                                                <option value="pending"
                                                    {{ old('status', $customer->status) == 'pending' ? 'selected' : '' }}>
                                                    Pending</option>
                                                <option value="active"
                                                    {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>
                                                    Active</option>
                                                <option value="suspended"
                                                    {{ old('status', $customer->status) == 'suspended' ? 'selected' : '' }}>
                                                    Suspended</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary px-5">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-warning px-6 position-relative" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm d-none me-2" id="spinner"></span>
                                    <i class="fas fa-save me-2"></i>Update Customer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Form submission loading
                document.getElementById('customerEditForm').addEventListener('submit', function() {
                    const submitBtn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('spinner');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                });
            });
        </script>
    @endpush
@endsection
