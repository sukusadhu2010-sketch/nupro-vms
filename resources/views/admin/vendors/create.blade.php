@extends('layouts.app')

@section('title', 'Create New Vendor - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-gradient-primary text-white p-4 rounded-top-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0 fw-bold">
                                    <i class="fas fa-user-plus me-3"></i>
                                    Add New Vendor
                                </h3>
                                <p class="mb-0 opacity-75">Create a new vendor account and profile</p>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('vendors.index') }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Vendors
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <form action="{{ route('vendors.store') }}" method="POST">
                            @csrf

                            <div class="row g-4">
                                <!-- Personal Info -->
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-uppercase text-primary mb-3">
                                        <i class="fas fa-user me-2"></i>Personal Information
                                    </h6>
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Contact Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name"
                                            class="form-control form-control-lg @error('name') is-invalid @enderror"
                                            value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Email Address <span
                                                class="text-danger">*</span></label>
                                        <input type="email" name="email"
                                            class="form-control form-control-lg @error('email') is-invalid @enderror"
                                            value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">Mobile Number</label>
                                        <input type="text" name="phone" inputmode="numeric" maxlength="15"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            value="{{ old('phone') }}"
                                            placeholder="Digits only, e.g. 9876543210"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Company Info -->
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-uppercase text-success mb-3">
                                        <i class="fas fa-building me-2"></i>Vendor Information
                                    </h6>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Particulars</label>
                                        <input type="text" name="particulars"
                                            class="form-control form-control-lg @error('particulars') is-invalid @enderror"
                                            value="{{ old('particulars') }}"
                                            placeholder="Vendor particulars / nature of business">
                                        @error('particulars')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Vendor Type <span
                                                class="text-danger">*</span></label>
                                        <div class="d-flex gap-4 pt-1">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="vendor_type"
                                                    id="vendor_type_foundry" value="FOUNDRY"
                                                    {{ old('vendor_type') === 'FOUNDRY' ? 'checked' : '' }} required>
                                                <label class="form-check-label fw-semibold"
                                                    for="vendor_type_foundry">FOUNDRY</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="vendor_type"
                                                    id="vendor_type_sub_vendor" value="SUB VENDOR"
                                                    {{ old('vendor_type') === 'SUB VENDOR' ? 'checked' : '' }} required>
                                                <label class="form-check-label fw-semibold"
                                                    for="vendor_type_sub_vendor">SUB VENDOR</label>
                                            </div>
                                        </div>
                                        @error('vendor_type')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">GST No <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="gst_no" maxlength="20"
                                            class="form-control form-control-lg @error('gst_no') is-invalid @enderror"
                                            value="{{ old('gst_no') }}"
                                            placeholder="e.g. 22AAAAA0000A1Z5" required>
                                        @error('gst_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Address</label>
                                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Status & Actions -->
                                <div class="col-12">
                                    <h6 class="fw-bold text-uppercase text-info mb-4">
                                        <i class="fas fa-cogs me-2"></i>Account Status
                                    </h6>

                                    <div class="row g-3 mb-5">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Initial Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="status"
                                                class="form-select form-select-lg @error('status') is-invalid @enderror"
                                                required>
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

                            <!-- Submit Section -->
                            <div class="d-flex gap-2 pt-4">
                                <button type="submit" class="btn btn-primary px-4 py-2 shadow me-2">
                                    <i class="fas fa-save me-1"></i>
                                    Create Vendor
                                </button>
                                <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary px-4 py-2 shadow">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page JS -->
    @push('js')
        <script>
            // Form enhancement
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
