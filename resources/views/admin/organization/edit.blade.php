@extends('layouts.app')

@section('title', 'Organization Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2"><i class="fas fa-building me-2 text-primary"></i>Organization Settings</h1>
            <p class="text-muted mb-0">Logo &amp; organization info used on quotations, invoices and printouts.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('organization.update') }}" enctype="multipart/form-data"
        class="card shadow-sm" style="max-width: 900px;">
        @csrf
        @method('PUT')
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Organization Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $organization->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Abbreviation <span class="text-danger">*</span></label>
                    <input type="text" name="abbreviation" maxlength="6" style="text-transform: uppercase"
                        class="form-control text-uppercase @error('abbreviation') is-invalid @enderror"
                        value="{{ old('abbreviation', $organization->abbreviation) }}" required>
                    <small class="text-muted">Used in document numbers (max 6 chars, A-Z/0-9)</small>
                    @error('abbreviation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">GSTIN / Tax ID</label>
                    <input type="text" name="gstin" class="form-control" value="{{ old('gstin', $organization->gstin) }}">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Logo</label>
                    <div class="d-flex align-items-start gap-4">
                        @if ($organization->logo_path)
                            <img src="{{ asset($organization->logo_url) }}" alt="Logo"
                                style="max-height: 100px; max-width: 300px;" class="border rounded p-2 bg-white">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="removeLogo">
                                <label class="form-check-label" for="removeLogo">Remove current logo</label>
                            </div>
                        @else
                            <div class="text-muted border rounded p-4 bg-light">No logo uploaded</div>
                        @endif
                    </div>
                    <input type="file" name="logo" accept=".png,.jpg,.jpeg,.svg"
                        class="form-control mt-2 @error('logo') is-invalid @enderror">
                    <small class="text-muted">PNG / JPG / SVG, max 2MB. Recommended ~300x100px.</small>
                    @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Print Assets</label>
                    <small class="text-muted d-block mb-2">Used when printing quotations, invoices and other documents.</small>
                    <div class="row g-3">
                        @foreach ([
                            'seal' => ['Seal', 'seal_path', 'seal_url', 'Round stamp/seal, ~300x300px transparent PNG'],
                            'signature' => ['Digital Signature', 'signature_path', 'signature_url', 'Authorized signatory signature, ~300x120px transparent PNG'],
                            'letterhead' => ['Letter Head', 'letterhead_path', 'letterhead_url', 'Full-width letter head, ~1700x300px'],
                        ] as $input => [$label, $column, $urlColumn, $hint])
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ $label }}</label>
                                <div class="border rounded p-2 bg-light text-center" style="min-height: 90px;">
                                    @if ($organization->{$column})
                                        <img src="{{ asset($organization->{$urlColumn}) }}" alt="{{ $label }}"
                                            style="max-height: 80px; max-width: 100%;" class="bg-white">
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="remove_{{ $input }}" value="1" id="remove_{{ $input }}">
                                            <label class="form-check-label small" for="remove_{{ $input }}">Remove</label>
                                        </div>
                                    @else
                                        <div class="text-muted small py-3">Not uploaded</div>
                                    @endif
                                </div>
                                <input type="file" name="{{ $input }}" accept=".png,.jpg,.jpeg,.svg"
                                    class="form-control mt-2 @error($input) is-invalid @enderror">
                                <small class="text-muted">{{ $hint }}</small>
                                @error($input)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Address Line 1</label>
                    <input type="text" name="address_line1" class="form-control" value="{{ old('address_line1', $organization->address_line1) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Address Line 2</label>
                    <input type="text" name="address_line2" class="form-control" value="{{ old('address_line2', $organization->address_line2) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $organization->city) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">State</label>
                    <input type="text" name="state" class="form-control" value="{{ old('state', $organization->state) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Pincode</label>
                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $organization->pincode) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Country</label>
                    <input type="text" name="country" class="form-control" value="{{ old('country', $organization->country ?? 'India') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $organization->contact_number) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $organization->email) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Website</label>
                    <input type="text" name="website" class="form-control" value="{{ old('website', $organization->website) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Bank Details (invoice footer)</label>
                    <textarea name="bank_details" rows="3" class="form-control">{{ old('bank_details', $organization->bank_details) }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
        </div>
    </form>
</div>
@endsection
