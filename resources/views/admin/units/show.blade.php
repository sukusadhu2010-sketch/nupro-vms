@extends('layouts.app')

@section('title', 'Unit Details - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-ruler-combined me-2 text-info"></i>
                                    Unit Details
                                </h4>
                                <p class="mb-0 text-muted">View unit information and linked products</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('units.edit', $unit) }}" class="btn btn-outline-warning">
                                    <i class="fas fa-edit me-2"></i>Edit Unit
                                </a>
                                <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Units
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <!-- Unit Info -->
                        <div class="row g-4 mb-5">
                            <div class="col-lg-6">
                                <div class="p-4 bg-light rounded-3">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="fas fa-info-circle me-2"></i>Unit Information
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 120px;">Name:</td>
                                            <td class="fw-semibold">{{ $unit->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Symbol:</td>
                                            <td><code class="bg-white px-2 py-1 rounded">{{ $unit->symbol }}</code></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Status:</td>
                                            <td>
                                                @php
                                                    $statusConfig = [
                                                        'active' => ['class' => 'success', 'icon' => 'check-circle'],
                                                        'inactive' => ['class' => 'danger', 'icon' => 'ban'],
                                                    ];
                                                    $config = $statusConfig[$unit->status] ?? $statusConfig['inactive'];
                                                @endphp
                                                <span class="badge bg-{{ $config['class'] }} px-3 py-2 rounded-pill">
                                                    <i
                                                        class="fas fa-{{ $config['icon'] }} me-1"></i>{{ ucfirst($unit->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Created:</td>
                                            <td>{{ $unit->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Updated:</td>
                                            <td>{{ $unit->updated_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-4 bg-light rounded-3 h-100">
                                    <h6 class="fw-bold mb-3 text-success">
                                        <i class="fas fa-align-left me-2"></i>Description
                                    </h6>
                                    <p class="mb-0 text-muted">
                                        {{ $unit->description ?? 'No description provided.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Linked Products -->
                        <div class="card border-0">
                            <div
                                class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">
                                    <i class="fas fa-boxes me-2 text-info"></i>
                                    Products Using This Unit
                                </h5>
                                <span class="badge bg-info px-3 py-2 rounded-pill">
                                    {{ $unit->products->count() }} Products
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th>Price</th>
                                                <th>Stock</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($unit->products as $index => $product)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="fw-semibold">{{ $product->name }}</div>
                                                    </td>
                                                    <td>
                                                        <code class="bg-light px-2 py-1 rounded">{{ $product->sku }}</code>
                                                    </td>
                                                    <td class="fw-bold text-success">
                                                        ₹{{ number_format($product->price, 2) }}
                                                    </td>
                                                    <td>
                                                        @if ($product->stock_quantity > 0)
                                                            <span class="badge bg-success px-2 py-1">
                                                                {{ $product->stock_quantity }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger px-2 py-1">Out of Stock</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $pStatusConfig = [
                                                                'active' => [
                                                                    'class' => 'success',
                                                                    'icon' => 'check-circle',
                                                                ],
                                                                'draft' => ['class' => 'secondary', 'icon' => 'file'],
                                                                'out_of_stock' => [
                                                                    'class' => 'warning',
                                                                    'icon' => 'exclamation-triangle',
                                                                ],
                                                                'inactive' => ['class' => 'danger', 'icon' => 'ban'],
                                                            ];
                                                            $pConfig =
                                                                $pStatusConfig[$product->status] ??
                                                                $pStatusConfig['draft'];
                                                        @endphp
                                                        <span
                                                            class="badge bg-{{ $pConfig['class'] }} px-2 py-1 rounded-pill">
                                                            <i
                                                                class="fas fa-{{ $pConfig['icon'] }} me-1"></i>{{ ucfirst($product->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">
                                                        <i class="fas fa-box-open fa-2x text-muted mb-3 opacity-50"></i>
                                                        <p class="text-muted mb-0">No products are using this unit yet.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
