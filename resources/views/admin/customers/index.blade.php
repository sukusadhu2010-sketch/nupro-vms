@extends('layouts.app')

@section('title', 'Customer Management - Admin')

@section('content')
    <div class="glass py-3">
        <div class="row">
            <div class="col-12">
                <div class="card glass">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="fas fa-user-tie me-2 text-accent"></i>
                                    Customer Management
                                </h4>
                                <p class="mb-0 text-muted small">Manage all registered customers and their status</p>
                            </div>
                            <a href="{{ route('customers.create') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-plus me-2"></i>New Customer
                            </a>
                        </div>
                    </div>

                    <!-- Search & Filter -->
                    <div class="card-body border-bottom">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Search customers by name or company..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>
                                        Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                            </div>
                            @if (request()->hasAny(['search', 'status']))
                                <div class="col-md-2">
                                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                                        Clear
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>

                    <!-- Customers Table -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="customersTable" class="table table-hover mb-0" style="width:100%">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Company</th>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>


                    <!-- Pagination -->
                    @if ($customers->hasPages())
                        <div class="card-footer bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of
                                    {{ $customers->total() }} customers
                                </div>
                                {{ $customers->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            $(document).ready(function() {
                const table = $('#customersTable').DataTable({
                    processing: true,
                    serverSide: false,
                    responsive: true,
                    pageLength: 25,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    order: [
                        [0, 'asc']
                    ],
                    columnDefs: [{
                            orderable: false,
                            targets: -1
                        }, // Actions column
                    ],
                    language: {
                        search: "Search customers:",
                        paginate: {
                            first: '<i class="fas fa-angle-double-left"></i>',
                            last: '<i class="fas fa-angle-double-right"></i>',
                            next: '<i class="fas fa-angle-right"></i>',
                            previous: '<i class="fas fa-angle-left"></i>'
                        }
                    },
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    data: @json($customers->items()),
                    columns: [{
                            data: null,
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            },
                            width: "5%"
                        },
                        {
                            data: 'company',
                            name: 'company'
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-gradient-warning text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            ${row.user ? row.user.name.charAt(0).toUpperCase() : ''}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">${row.user ? row.user.name : 'N/A'}</div>
                                        </div>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'user.email',
                            name: 'email',
                            render: function(data) {
                                return data || 'N/A';
                            }
                        },
                        {
                            data: 'phone',
                            render: function(data) {
                                return data || 'N/A';
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                const statusConfig = {
                                    'active': {
                                        'class': 'success',
                                        'icon': 'check-circle'
                                    },
                                    'pending': {
                                        'class': 'warning',
                                        'icon': 'clock'
                                    },
                                    'suspended': {
                                        'class': 'danger',
                                        'icon': 'ban'
                                    }
                                };
                                const config = statusConfig[row.status] || statusConfig['pending'];
                                return `<span class="badge bg-${config.class} px-3 py-2 rounded-pill">
                                    <i class="fas fa-${config.icon} me-1"></i>${row.status.charAt(0).toUpperCase() + row.status.slice(1)}
                                </span>`;
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="dropdown dropstart">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle p-2 rounded-circle border-0 shadow-sm" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu shadow-lg border-0 py-2" style="min-width: 160px;">
                                            <li><a class="dropdown-item py-2" href="/admin/customers/${row.id}">
                                                <i class="fas fa-eye me-2 text-info"></i>View Profile
                                            </a></li>
                                            <li><a class="dropdown-item py-2" href="/admin/customers/${row.id}/edit">
                                                <i class="fas fa-edit me-2 text-warning"></i>Edit
                                            </a></li>
                                            <li><hr class="dropdown-divider my-1 mx-2"></li>
                                            <li><button class="dropdown-item py-2 text-danger w-100 text-start" onclick="deleteCustomer(${row.id})">
                                                <i class="fas fa-trash me-2"></i>Delete Customer
                                            </button></li>
                                        </ul>
                                    </div>
                                `;
                            }
                        }
                    ]
                });
            });

            const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Delete Customer AJAX
            window.deleteCustomer = async function(customerId) {
                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: 'This customer will be permanently deleted!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
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
                        $('#customersTable').DataTable().row(`[data-customer-id="${customerId}"]`).remove().draw();
                        Swal.fire('Deleted!', 'Customer has been deleted.', 'success');
                    } else {
                        throw new Error('Delete failed');
                    }
                } catch (error) {
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                }
            }
        </script>
    @endpush
+@endsection
