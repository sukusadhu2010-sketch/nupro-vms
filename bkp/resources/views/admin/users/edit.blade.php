@extends('layouts.app')

@section('title', 'Edit User - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-user-edit me-2 text-warning"></i>
                                    Edit User: {{ $user->name }}
                                </h4>
                                <p class="mb-0 text-muted">Update user details and permissions</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye me-2"></i>View Profile
                                </a>
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <form method="POST" action="{{ route('users.update', $user) }}" id="userEditForm">
                            @csrf
                            @method('PUT')

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
                                                    id="name" name="name" value="{{ old('name', $user->name) }}"
                                                    required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <label for="email" class="form-label fw-semibold">Email Address <span
                                                        class="text-danger">*</span></label>
                                                <input type="email"
                                                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                                                    id="email" name="email" value="{{ old('email', $user->email) }}"
                                                    required>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                                <input type="tel" class="form-control form-control-lg" id="phone"
                                                    name="phone"
                                                    value="{{ old('phone', $user->vendor?->phone ?? $user->customer?->phone) }}">
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
                                                            {{ old('role_id', $user->roles->first()?->id) == $role->id ? 'selected' : '' }}>
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

                                    <!-- Password -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold mb-3">Password (Leave blank to keep
                                            current)</label>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="password" class="form-label fw-semibold">New Password</label>
                                                <div class="input-group">
                                                    <input type="password"
                                                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                                                        id="password" name="password" minlength="8"
                                                        placeholder="Leave blank to keep current">
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
                                                    Password</label>
                                                <div class="input-group">
                                                    <input type="password"
                                                        class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror"
                                                        id="password_confirmation" name="password_confirmation">
                                                </div>
                                                @error('password_confirmation')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>Password must be at least 8 characters
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-warning px-5 position-relative" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm d-none me-2" id="spinner"></span>
                                    <i class="fas fa-save me-2"></i>Update User
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

                // Form submission loading
                document.getElementById('userEditForm').addEventListener('submit', function() {
                    const submitBtn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('spinner');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                });
            });
        </script>
    @endpush
@endsection
