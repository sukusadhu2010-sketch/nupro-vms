@extends('layouts.app')

@section('title', 'Create New Unit - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-ruler-combined me-2 text-primary"></i>
                                    Create New Unit
                                </h4>
                                <p class="mb-0 text-muted">Add a new measurement unit for products</p>
                            </div>
                            <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Units
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <form method="POST" action="{{ route('units.store') }}" id="unitCreateForm">
                            @csrf

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <label for="name" class="form-label fw-bold">Unit Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}" required
                                        placeholder="e.g., Kilogram, Piece, Meter">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label for="symbol" class="form-label fw-bold">Symbol <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('symbol') is-invalid @enderror"
                                        id="symbol" name="symbol" value="{{ old('symbol') }}" required maxlength="20"
                                        placeholder="e.g., kg, pc, m">
                                    @error('symbol')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label fw-bold">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="3" placeholder="Brief description of the unit...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label for="status" class="form-label fw-bold">Status <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select form-select-lg @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-3 pt-4 border-top mt-4">
                                <a href="{{ route('units.index') }}" class="btn btn-outline-secondary px-5">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-6 fw-semibold" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm d-none me-2" id="spinner"></span>
                                    <i class="fas fa-plus me-2"></i>Create Unit
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
                const form = document.getElementById('unitCreateForm');
                const submitBtn = document.getElementById('submitBtn');
                const spinner = document.getElementById('spinner');

                // Form submission
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                });

                // Auto-generate symbol suggestion
                document.getElementById('name').addEventListener('input', function() {
                    const symbol = document.getElementById('symbol');
                    if (!symbol.value) {
                        const words = this.value.trim().split(/\s+/);
                        if (words.length === 1) {
                            symbol.value = words[0].substring(0, 3).toLowerCase();
                        } else if (words.length > 1) {
                            symbol.value = words.map(w => w[0]).join('').toLowerCase();
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection
