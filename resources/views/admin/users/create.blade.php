@extends('layouts.app')

@section('title', 'Create New User - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-user-plus me-2 text-success"></i>
                                    Create New User
                                </h4>
                                <p class="mb-0 text-muted">Fill details to create new system user</p>
                            </div>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Users
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <form method="POST" action="{{ route('users.store') }}" id="userCreateForm">
                            @csrf

                            <div class="row g-4">
                                <!-- Basic Info -->
                                <div class="col-lg-6">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold mb-3">Basic Information</label>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="name" class="form-label fw-semibold">Full Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control form-control-lg @error('name') is-invalid @enderror"
                                                    id="name" name="name" value="{{ old('name') }}" required
                                                    placeholder="Enter full name">
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <label for="email" class="form-label fw-semibold">Email Address <span
                                                        class="text-danger">*</span></label>
                                                <input type="email"
                                                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                                                    id="email" name="email" value="{{ old('email') }}" required
                                                    placeholder="user@company.com">
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                                <input type="tel" class="form-control form-control-lg" id="phone"
                                                    name="phone" value="{{ old('phone') }}"
                                                    placeholder="+1 (555) 123-4567">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Role & Permissions -->
                                <div class="col-lg-6">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold mb-3">Role & Permissions</label>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="role_id" class="form-label fw-semibold">User Role <span
                                                        class="text-danger">*</span></label>
                                                <select
                                                    class="form-select form-select-lg @error('role_id') is-invalid @enderror"
                                                    id="role_id" name="role_id" required>
                                                    <option value="">Select Role</option>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->id }}"
                                                            {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                            {{ ucfirst($role->name) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('role_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Password (only for create) -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold mb-3">Password</label>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="password" class="form-label fw-semibold">Password <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password"
                                                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                                                        id="password" name="password" required minlength="8"
                                                        placeholder="Minimum 8 characters">
                                                    <button class="btn btn-outline-secondary toggle-password"
                                                        type="button">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="password_confirmation" class="form-label fw-semibold">Confirm
                                                    Password <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password"
                                                        class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror"
                                                        id="password_confirmation" name="password_confirmation" required
                                                        placeholder="Re-type password">
                                                </div>
                                                @error('password_confirmation')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-success px-5 position-relative overflow-hidden"
                                    id="submitBtn">
                                    <span class="spinner-border spinner-border-sm d-none me-2" id="spinner"></span>
                                    <i class="fas fa-user-plus me-2"></i>Create User
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
                // Password toggle
                document.querySelector('.toggle-password').addEventListener('click', function() {
                    const password = document.getElementById('password');
                    const confirmPassword = document.getElementById('password_confirmation');
                    const icon = this.querySelector('i');

                    const type = password.type === 'password' ? 'text' : 'password';
                    password.type = type;
                    confirmPassword.type = type;

                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });

                // Real-time password match validation
                document.getElementById('password').addEventListener('input', function() {
                    const password = this.value;
                    const confirm = document.getElementById('password_confirmation').value;

                    if (confirm && password !== confirm) {
                        document.getElementById('password_confirmation').classList.add('is-invalid');
                    } else {
                        document.getElementById('password_confirmation').classList.remove('is-invalid');
                    }
                });

                // Form submission with loading
                document.getElementById('userCreateForm').addEventListener('submit', function() {
                    const submitBtn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('spinner');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                    submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2" id="spinner"></span><i class="fas fa-spinner fa-spin me-2"></i>Creating...';
                });
            });
        </script>
    @endpush
@endsection
