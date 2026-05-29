@extends('layouts.app')

@section('title', 'Customer Profile - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Customer Profile Card -->
            <div class="col-xl-8">
                <div class="card shadow-lg border-0 rounded-4 h-100">
                    <div class="card-header bg-gradient-warning text-white rounded-top-4 border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0 fw-bold">
                                    <i class="fas fa-user-tie me-2"></i>
                                    Customer Profile
                                </h3>
                                <p class="mb-0 opacity-90">Detailed view of {{ $customer->company }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                <a href="{{ route('customers.index') }}" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-list me-1"></i>Customers List
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <div class="row g-4">
                            <!-- Main Info -->
                            <div class="col-12">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div
                                            class="avatar avatar-xl bg-gradient-warning text-white rounded-circle d-flex align-items-center justify-content-center">
                                            {{ strtoupper(substr($customer->company, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h2 class="mb-1 fw-bold">{{ $customer->user->name }}</h2>
                                        <p class="mb-2 text-muted fs-5">{{ $customer->user->email }}</p>
                                        @if ($customer->phone)
                                            <p class="mb-0">
                                                <i class="fas fa-phone me-2 text-muted"></i>
                                                {{ $customer->phone }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Company Details -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 fw-bold text-warning">
                                            <i class="fas fa-building me-2"></i>Company Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-warning-subtle rounded-circle p-3 me-3 d-flex align-items-center justify-content-center"
                                                        style="width: 60px; height: 60px;">
                                                        <i class="fas fa-building text-warning fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-bold">{{ $customer->company }}</h6>
                                                        <small class="text-muted">Company Name</small>
                                                    </div>
                                                </div>
                                                @if ($customer->address)
                                                    <div class="d-flex align-items-start">
                                                        <div class="bg-info-subtle rounded-circle p-2 me-3 d-flex align-items-center justify-content-center mt-1"
                                                            style="width: 40px; height: 40px;">
                                                            <i class="fas fa-map-marker-alt text-info"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-semibold">{{ $customer->address }}</h6>
                                                            <small class="text-muted">Business Address</small>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-center h-100">
                                                    <div class="text-center">
                                                        @php
                                                            $statusConfig = [
                                                                'active' => [
                                                                    'class' => 'success',
                                                                    'icon' => 'check-circle',
                                                                    'label' => 'Active',
                                                                ],
                                                                'pending' => [
                                                                    'class' => 'warning',
                                                                    'icon' => 'clock',
                                                                    'label' => 'Pending',
                                                                ],
                                                                'suspended' => [
                                                                    'class' => 'danger',
                                                                    'icon' => 'ban',
                                                                    'label' => 'Suspended',
                                                                ],
                                                            ];
                                                            $config =
                                                                $statusConfig[$customer->status] ??
                                                                $statusConfig['pending'];
                                                        @endphp
                                                        <div
                                                            class="bg-{{ $config['class'] }}-subtle border border-{{ $config['class'] }} rounded-4 p-4">
                                                            <i
                                                                class="fas fa-{{ $config['icon'] }} text-{{ $config['class'] }} fa-3x mb-3"></i>
                                                            <h3 class="fw-bold text-{{ $config['class'] }}">
                                                                {{ ucfirst($customer->status) }}</h3>
                                                            <small class="text-muted">Account Status</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Activity -->
                            <div class="col-12">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card border-0 h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-calendar-plus text-primary fa-2x mb-3"></i>
                                                <h5>{{ $customer->created_at->format('M d, Y') }}</h5>
                                                <small
                                                    class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                                                <p class="text-muted mt-2 mb-0">Registration Date</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-0 h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-clock text-info fa-2x mb-3"></i>
                                                <h5>{{ $customer->updated_at->format('M d, Y') }}</h5>
                                                <small
                                                    class="text-muted">{{ $customer->updated_at->diffForHumans() }}</small>
                                                <p class="text-muted mt-2 mb-0">Last Updated</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Sidebar -->
            <div class="col-xl-4">
                <div class="card shadow-lg border-0 rounded-4 h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold text-warning">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-warning">
                                <i class="fas fa-edit me-2"></i>Edit Details
                            </a>
                            @if ($customer->status !== 'pending')
                                <form action="{{ route('customers.toggle-status', $customer) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit"
                                        class="btn {{ $customer->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                        onclick="return confirm('{{ $customer->status === 'active' ? 'Suspend this customer?' : 'Activate this customer?' }}')">
                                        <i
                                            class="fas fa-toggle-{{ $customer->status === 'active' ? 'off' : 'on' }} me-2"></i>
                                        {{ $customer->status === 'active' ? 'Suspend' : 'Activate' }}
                                    </button>
                                </form>
                            @endif
                            <div class="dropdown-divider my-3"></div>
                            <button class="btn btn-danger" onclick="deleteCustomer({{ $customer->id }})">
                                <i class="fas fa-trash me-2"></i>Delete Customer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            async function deleteCustomer(customerId) {
                const result = await Swal.fire({
                    title: 'Delete Customer?',
                    text: 'This customer account will be permanently deleted!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete!'
                });

                if (!result.isConfirmed) return;

                try {
                    const response = await fetch(`/admin/customers/${customerId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        Swal.fire('Deleted!', 'Customer removed successfully.', 'success').then(() => {
                            window.location.href = "{{ route('customers.index') }}";
                        });
                    }
                } catch (error) {
                    Swal.fire('Error!', 'Failed to delete customer.', 'error');
                }
            }
        </script>
    @endpush
@endsection
