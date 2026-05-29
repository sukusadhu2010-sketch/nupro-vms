@extends('layouts.app')

@section('title', 'Sales Orders')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-shopping-cart me-2 text-success"></i>
                            Sales Orders
                        </h4>
                        <p class="text-muted mb-0">Manage sales orders with multiple products</p>
                    </div>
                    <button class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>New Sales Order
                    </button>
                </div>

                <!-- Sales Orders Table -->
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Products</th>
                                        <th>Value</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-primary px-3 py-2 rounded-pill">SO-001</span></td>
                                        <td>
                                            <div class="fw-bold">Tech Solutions Ltd</div>
                                            <small class="text-muted">sales@techsol.com</small>
                                        </td>
                                        <td>
                                            <ul class="mb-0 ps-3">
                                                <li>Dell XPS Laptop ($1,200)</li>
                                                <li>LG 27" Monitor ($450)</li>
                                                <li>Wireless Mouse ($50)</li>
                                            </ul>
                                        </td>
                                        <td><strong class="text-success">$1,700</strong></td>
                                        <td><span class="badge bg-info px-3 py-2">Shipped</span></td>
                                        <td>2024-01-20</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="#" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-success">
                                                    <i class="fas fa-invoice"></i>
                                                </a>
                                                <button class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success px-3 py-2 rounded-pill">SO-002</span></td>
                                        <td>
                                            <div class="fw-bold">Global Trading Co</div>
                                            <small class="text-muted">orders@global.com</small>
                                        </td>
                                        <td>
                                            <ul class="mb-0 ps-3">
                                                <li>Office Chair ($350)</li>
                                                <li>Conference Table ($1,200)</li>
                                                <li>Projector ($800)</li>
                                            </ul>
                                        </td>
                                        <td><strong class="text-success">$2,350</strong></td>
                                        <td><span class="badge bg-success px-3 py-2">Delivered</span></td>
                                        <td>2024-01-19</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="#" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-success">
                                                    <i class="fas fa-invoice"></i>
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
