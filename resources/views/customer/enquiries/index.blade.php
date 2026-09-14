@extends('layouts.app')

@section('title', 'My Enquiries')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h2 mb-1">
                    <i class="fas fa-clipboard-list me-2 text-info"></i>
                    My Enquiries
                </h1>
                <p class="text-muted">Track your submitted enquiries and their status.</p>
            </div>
        </div>

        <!-- Enquiries Table -->
        <div class="card shadow-lg border-0">
            <div class="card-header bg-white border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="mb-0 fw-bold text-info">All Enquiries</h6>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('customer.enquiries.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>New Enquiry
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Products</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Attachments</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($enquiries as $enquiry)
                                <tr>
                                    <td>
                                        <strong>{{ $enquiry->created_at->format('M d, Y') }}</strong>
                                        <br><small class="text-muted">{{ $enquiry->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <ul class="mb-0 ps-3 small">
                                            @foreach ($enquiry->products as $product)
                                                <li>{{ $product['name'] }} (x{{ $product['quantity'] }})</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        <strong
                                            class="text-success">₹{{ number_format($enquiry->total_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge {{ $enquiry->status_badge }} px-3 py-2">
                                            {{ ucfirst($enquiry->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($enquiry->attachments && count($enquiry->attachments) > 0)
                                            <span class="badge bg-light text-dark">
                                                {{ count($enquiry->attachments) }} files
                                            </span>
                                        @else
                                            <span class="text-muted small">None</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('customer.enquiries.show', $enquiry) }}"
                                                class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if ($enquiry->attachments)
                                                <button class="btn btn-outline-secondary"
                                                    onclick="downloadAttachments({{ $enquiry->id }})">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No enquiries yet</h5>
                                        <p class="text-muted">Create your first enquiry to get started.</p>
                                        <a href="{{ route('customer.enquiries.create') }}" class="btn btn-primary">
                                            Create Enquiry
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-3">
                {{ $enquiries->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function downloadAttachments(enquiryId) {
                // Future: ZIP download endpoint
                alert('Download feature coming soon!');
            }
        </script>
    @endpush
@endsection
