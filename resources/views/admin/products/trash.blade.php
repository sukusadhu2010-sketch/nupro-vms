@extends('layouts.app')

@section('title', 'Deleted Products - Admin')

@section('content')
<div class="glass py-3">
    <div class="row">
        <div class="col-lg-12">
            <div class="card glass">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <i class="fas fa-trash me-2 text-danger"></i>
                                Deleted Products
                            </h4>
                            <p class="mb-0 text-muted small">Products removed from the list — restore them or delete permanently</p>
                        </div>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to Products
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="mx-3 mt-3">
                        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th>Deleted At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $index => $product)
                                <tr>
                                    <td class="text-muted">{{ $products->firstItem() + $index }}</td>
                                    <td class="fw-semibold">{{ $product->name }}</td>
                                    <td><code>{{ $product->sku }}</code></td>
                                    <td>
                                        @if ($product->productCategory)
                                        <span class="badge bg-secondary px-2 py-1 rounded-pill">
                                            {{ $product->productCategory->name }}
                                        </span>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-danger px-3 py-2 rounded-pill">
                                            <i class="fas fa-trash me-1"></i>{{ $product->deleted_at->format('d M Y, h:i A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('products.restore', $product->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                <i class="fas fa-undo me-1"></i>Restore
                                            </button>
                                        </form>
                                        <form action="{{ route('products.force-delete', $product->id) }}" method="POST" class="d-inline form-force-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Permanently">
                                                <i class="fas fa-times me-1"></i>Delete Forever
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                                        <p class="text-muted mb-0">Trash is empty — no deleted products.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($products->hasPages())
                <div class="card-footer">
                    {{ $products->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.form-force-delete').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Permanently delete this product? This cannot be undone!')) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection