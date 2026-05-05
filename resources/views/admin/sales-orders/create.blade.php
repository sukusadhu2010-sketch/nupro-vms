@extends('layouts.app')

@section('title', 'Create Sales Order')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-plus me-2 text-success"></i>
                            Create Sales Order
                        </h4>
                        <p class="text-muted mb-0">Convert a quotation into a sales order</p>
                    </div>
                    <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body">
                        <form action="{{ route('sales-orders.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="quotation_id" class="form-label fw-bold">Select Quotation</label>
                                <select name="quotation_id" id="quotation_id" class="form-select" required>
                                    <option value="">— Choose a quotation —</option>
                                    @foreach ($quotations as $quotation)
                                        <option value="{{ $quotation->id }}"
                                            {{ old('quotation_id') == $quotation->id ? 'selected' : '' }}>
                                            {{ $quotation->quote_number }} —
                                            {{ $quotation->enquiry->customer->name ?? 'N/A' }} ({{ $quotation->status }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('quotation_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="po_number" class="form-label fw-bold">PO Number</label>
                                    <input type="text" name="po_number" id="po_number" class="form-control"
                                        value="{{ old('po_number') }}" placeholder="Enter PO number">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="job_number" class="form-label fw-bold">Job Number</label>
                                    <input type="text" name="job_number" id="job_number" class="form-control"
                                        value="{{ old('job_number') }}" placeholder="Enter job number">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-2"></i>Convert to Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
