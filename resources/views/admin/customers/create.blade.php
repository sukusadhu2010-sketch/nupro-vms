@extends('layouts.app')

@section('title', 'Create New Customer - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-user-plus me-2 text-warning"></i>
                                    Create New Customer
                                </h4>
                                <p class="mb-0 text-muted">Fill details to add new customer to system</p>
                            </div>
                            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Customers
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <form method="POST" action="{{ route('customers.store') }}" id="customerCreateForm">
                            @csrf

                            <div class="row g-4">
                                <!-- Basic Info -->
                                <div class="col-lg-6">
                                    <label class="form-label fw-bold mb-3">Basic Information</label>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="name" class="form-label fw-semibold">Contact Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control form-control-lg @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name') }}" required>
                                            <small class="text-muted">Primary identifier for the customer.</small>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12">
                                            <label for="email" class="form-label fw-semibold">Email Address <span
                                                    class="text-danger">*</span></label>
                                            <input type="email"
                                                class="form-control form-control-lg @error('email') is-invalid @enderror"
                                                id="email" name="email" value="{{ old('email') }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12">
                                            <label for="phone" class="form-label fw-semibold">Mobile Number</label>
                                            <input type="tel" inputmode="numeric"
                                                class="form-control form-control-lg @error('phone') is-invalid @enderror"
                                                id="phone" name="phone" value="{{ old('phone') }}"
                                                placeholder="Digits only, e.g. 9876543210">
                                            <small class="text-muted">Only numeric digits allowed (no spaces, signs or
                                                decimals).</small>
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Info -->
                                <div class="col-lg-6">
                                    <label class="form-label fw-bold mb-3">Additional Details</label>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="gst_no" class="form-label fw-semibold">GST No <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control form-control-lg @error('gst_no') is-invalid @enderror"
                                                id="gst_no" name="gst_no" value="{{ old('gst_no') }}" required
                                                maxlength="20" placeholder="e.g. 27ABCDE1234F1Z5">
                                            @error('gst_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12">
                                            <label for="address" class="form-label fw-semibold">Address</label>
                                            <textarea class="form-control form-control-lg" id="address" name="address" rows="3">{{ old('address') }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="status" class="form-label fw-semibold">Initial Status <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select form-select-lg @error('status') is-invalid @enderror"
                                                id="status" name="status" required>
                                                <option value="">Select Status</option>
                                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>
                                                    Pending Approval</option>
                                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                                    Active</option>
                                                <option value="suspended"
                                                    {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
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
                                    <i class="fas fa-user-plus me-2"></i>Create Customer
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
                document.getElementById('customerCreateForm').addEventListener('submit', function() {
                    const submitBtn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('spinner');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                });

                // Mobile Number: strip everything except digits (blocks
                // negatives, decimals, spaces and non-numeric characters)
                document.getElementById('phone').addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                });
            });
        </script>
    @endpush
@endsection
