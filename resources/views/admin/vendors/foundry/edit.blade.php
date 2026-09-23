@extends('layouts.app')

@section('title', 'Edit Foundry - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-gradient-warning text-dark p-4 rounded-top-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0 fw-bold">
                                <i class="fas fa-industry me-3"></i>
                                Edit Foundry: {{ $vendor->name }}
                            </h3>
                            <p class="mb-0 opacity-75">Update foundry account details</p>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('foundry-vendors.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-2"></i>Back to Foundries
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-5">
                    <form action="{{ route('foundry-vendors.update', $vendor) }}" method="POST">
                        @csrf
                        @method('PUT')

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
                                        value="{{ old('name', $vendor->name) }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Email Address <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="email"
                                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                                        value="{{ old('email', $vendor->email) }}" required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Mobile Number</label>
                                    <input type="text" name="phone" inputmode="numeric" maxlength="15"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone', $vendor->phone) }}"
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
                                        value="{{ old('particulars', $vendor->particulars) }}"
                                        placeholder="Vendor particulars / nature of business">
                                    @error('particulars')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Vendor Type</label>
                                    <input type="text" class="form-control form-control-lg bg-light" value="FOUNDRY" disabled>
                                    <small class="text-muted">Vendor type cannot be changed here.</small>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">GST No <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="gst_no" maxlength="20"
                                        class="form-control form-control-lg @error('gst_no') is-invalid @enderror"
                                        value="{{ old('gst_no', $vendor->gst_no) }}"
                                        placeholder="e.g. 22AAAAA0000A1Z5" required>
                                    @error('gst_no')
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
                                Update Foundry
                            </button>
                            <a href="{{ route('foundry-vendors.index') }}"
                                class="btn btn-outline-secondary  px-4 py-2 shadow me-2">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <a href="{{ route('foundry-vendors.show', $vendor) }}"
                                class="btn btn-outline-info  px-4 py-2 shadow me-2">
                                <i class="fas fa-eye me-2"></i>View Profile
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endsection