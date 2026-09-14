@extends('layouts.app')

@section('title', 'Quotation Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-file-quote me-2 text-primary"></i>
                            Quotation Management
                        </h4>
                        <p class="text-muted mb-0">Manage quotations with multiple products</p>
                    </div>
                    <button class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Quotation
                    </button>
                </div>

                <!-- Quotations Table -->
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Quote #</th>
                                        <th>Customer</th>
                                        <th>Products</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Expires</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-primary px-3 py-2 rounded-pill">QT-001</span></td>
                                        <td>
                                            <div class="fw-bold">ABC Corp</div>
                                            <small class="text-muted">sales@abccorp.com</small>
                                        </td>
                                        <td>
                                            <ul class="mb-0 ps-3">
                                                <li>Laptop Dell XPS (₹1,200)</li>
                                                <li>Monitor LG 27" (₹450)</li>
                                                <li>Mouse Wireless (₹50)</li>
                                            </ul>
                                        </td>
                                        <td><strong class="text-success">₹1,700</strong></td>
                                        <td><span class="badge bg-success px-3 py-2">Approved</span></td>
                                        <td>2024-02-10</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="#" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-success">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                <button class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-info px-3 py-2 rounded-pill">QT-002</span></td>
                                        <td>
                                            <div class="fw-bold">Tech Ltd</div>
                                            <small class="text-muted">tech@company.com</small>
                                        </td>
                                        <td>
                                            <ul class="mb-0 ps-3">
                                                <li>Office Chair (₹350)</li>
                                                <li>Desk (₹650)</li>
                                                <li>Lamp (₹100)</li>
                                            </ul>
                                        </td>
                                        <td><strong class="text-success">₹1,100</strong></td>
                                        <td><span class="badge bg-warning px-3 py-2">Pending</span></td>
                                        <td>2024-02-05</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="#" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-success">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                <button class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
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
