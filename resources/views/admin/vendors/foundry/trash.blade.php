@extends('layouts.app')

@section('title', 'Deleted Foundries - Admin')

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
                                Deleted Foundries
                            </h4>
                            <p class="mb-0 text-muted small">Foundries removed from the list — restore them or delete permanently</p>
                        </div>
                        <a href="{{ route('foundry-vendors.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to Foundries
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Contact Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>GST No</th>
                                    <th>Deleted At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($vendors as $index => $vendor)
                                <tr>
                                    <td class="text-muted">{{ $vendors->firstItem() + $index }}</td>
                                    <td class="fw-semibold">{{ $vendor->name }}</td>
                                    <td>{{ $vendor->email }}</td>
                                    <td>{{ $vendor->phone ?? '—' }}</td>
                                    <td>{{ $vendor->gst_no }}</td>
                                    <td>
                                        <span class="badge bg-danger px-3 py-2 rounded-pill">
                                            <i class="fas fa-trash me-1"></i>{{ $vendor->deleted_at->format('d M Y, h:i A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('foundry-vendors.restore', $vendor->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                <i class="fas fa-undo me-1"></i>Restore
                                            </button>
                                        </form>
                                        <form action="{{ route('foundry-vendors.force-delete', $vendor->id) }}" method="POST" class="d-inline form-force-delete">
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
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                                        <p class="text-muted mb-0">Trash is empty — no deleted foundries.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($vendors->hasPages())
                <div class="card-footer">
                    {{ $vendors->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.form-force-delete').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Permanently delete this foundry? This cannot be undone!')) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection