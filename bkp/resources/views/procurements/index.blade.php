@extends('layouts.app')

@section('title', 'Procurement Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-truck me-2 text-danger"></i>
                            Procurement Management
                        </h4>
                        <p class="text-muted mb-0">Manage procurement orders with multiple products</p>
                    </div>
                    <button class="btn btn-danger">
                        <i class="fas fa-plus me-2"></i>New P.O.
                    </button>
                </div>

                <!-- Procurement Table -->
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>P.O. #</th>
                                        <th>Vendor</th>
                                        <th>Products</th>
                                        <th>Value</th>
                                        <th>Status</th>
                                        <th>ETA</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-danger px-3 py-2 rounded-pill">PO-023</span></td>
                                        <td>
                                            <div class="fw-bold">XYZ Supplies</div>
                                            <small class="text-muted">purchase@xyz.com</small>
                                        </td>
                                        <td>
                                            <ul class="mb-0 ps-3">
                                                <li>Office Chair (50 units) ($17,500)</li>
                                                <li>Desk (20 units) ($8,000)</li>
                                            </ul>
                                        </td>
                                        <td><strong class="text-success">$25,500</strong></td>
                                        <td><span class="badge bg-info px-3 py-2">In Transit</span></td>
                                        <td>2024-01-28</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="#" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-success">
                                                    <i class="fas fa-shipping-fast"></i>
                                                </a>
                                                <button class="btn btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning px-3 py-2 rounded-pill">PO-024</span></td>
                                        <td>
                                            <div class="fw-bold">ABC Manufacturing</div>
                                            <small class="text-muted">supply@abc.com</small>
                                        </td>
                                        <td>
                                            <ul class="mb-0 ps-3">
                                                <li>Printer Paper (1000 reams) ($5,000)</li>
                                                <li>Ink Cartridges (200 units) ($4,000)</li>
                                            </ul>
                                        </td>
                                        <td><strong class="text-success">$9,000</strong></td>
                                        <td><span class="badge bg-warning px-3 py-2">Pending</span></td>
                                        <td>2024-01-30</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="#" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-success">
                                                    <i class="fas fa-shipping-fast"></i>
                                                </a>
                                                <button class="btn btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
