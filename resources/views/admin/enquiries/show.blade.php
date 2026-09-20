@extends('layouts.app')

@section('title', 'Enquiry Details #{{ $enquiry->id }}')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('enquiries.index') }}">Enquiries</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </nav>
                <h1 class="h2 mb-1">
                    <i class="fas fa-eye me-2 text-info"></i>Enquiry #{{ $enquiry->id }}
                </h1>
                <p class=" mb-0">
                    Created {{ $enquiry->created_at->format('M d, Y H:i') }}
                </p>
            </div>
            <div class="col-auto">
                <span class="badge {{ $enquiry->status_badge }} fs-4 px-4 py-2">{{ ucfirst($enquiry->status) }}</span>
                <span class="badge {{ $enquiry->priority_badge }} fs-6 px-3 py-2 ms-2">{{ ucfirst($enquiry->priority) }}
                    Priority</span>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Customer Info -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Customer Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>{{ $enquiry->customer->company ?? $enquiry->customer->name ?? 'N/A' }}</strong><br>
                                <small class="">{{ $enquiry->customer?->user?->email ?? 'No email available' }}</small>
                            </div>
                            <div class="col-md-6 text-end">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mt-4" id="enquiryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#products"
                            type="button" role="tab">
                            <i class="fas fa-list me-1"></i>Products
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="quotations-tab" data-bs-toggle="tab" data-bs-target="#quotations"
                            type="button" role="tab">
                            <i class="fas fa-file-invoice-dollar me-1"></i>Quotations
                            <span class="badge bg-secondary ms-1">{{ $enquiry->quotations->count() }}</span>
                        </button>
                    </li>
                    @if ($enquiry->message)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="message-tab" data-bs-toggle="tab" data-bs-target="#message"
                                type="button" role="tab">
                                <i class="fas fa-comment me-1"></i>Message
                            </button>
                        </li>
                    @endif
                </ul>

                <div class="tab-content" id="enquiryTabsContent">
                    <!-- Products Tab -->
                    <div class="tab-pane fade show active" id="products" role="tabpanel">
                        <div class="card shadow-sm border-top-0 rounded-0 rounded-bottom">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Product</th>
                                                <th class="text-center">Qty</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($enquiry->items as $item)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item->product->name }}</strong>
                                                        <br><small class="">{{ $item->product->sku }}</small>
                                                        <br><small
                                                            class="">{{ $item->product->vendor?->company ?? 'No Vendor' }}</small>
                                                    </td>
                                                    <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                                    <td>
                                                        @if ($item->notes)
                                                            <small class="">{{ $item->notes }}</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-4 ">No products</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quotations Tab -->
                    <div class="tab-pane fade" id="quotations" role="tabpanel">
                        <div class="card shadow-sm border-top-0 rounded-0 rounded-bottom">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>All
                                    Quotations</h6>
                                    @if($enquiry->status !='closed')
                                <a href="{{ route('quotations.create', $enquiry) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus me-1"></i>Convert to Quote
                                </a>
                                @endif
                            </div>
                            <div class="card-body p-0">
                                @if ($enquiry->quotations->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Quote #</th>
                                                    <th>Version</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Total</th>
                                                    <th>Valid Until</th>
                                                    <th>Created</th>
                                                    <th class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($enquiry->quotations as $quote)
                                                    <tr
                                                        class="{{ $quote->is_accepted ? 'table-success' : ($quote->status == 'expired' ? 'table-secondary' : '') }}">
                                                        <td>
                                                            <a href="{{ route('quotations.show', $quote) }}"
                                                                class="fw-bold text-decoration-none">
                                                                {{ $quote->quote_number }}
                                                            </a>
                                                            @if ($quote->is_accepted)
                                                                <br><span class="badge bg-success">Accepted</span>
                                                            @elseif($quote->is_active)
                                                                <br><span class="badge bg-info">Active</span>
                                                            @endif
                                                        </td>
                                                        <td>V{{ $quote->version }}</td>
                                                        <td>
                                                            <span class="badge {{ $quote->status_badge }}">
                                                                {{ $quote->status_label }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end fw-bold">
                                                            ₹{{ number_format($quote->total_amount, 2) }}
                                                        </td>
                                                        <td>
                                                            {{ $quote->valid_until ? $quote->valid_until->format('M d, Y') : 'N/A' }}
                                                        </td>
                                                        <td>
                                                            <small
                                                                class="">{{ $quote->created_at->format('M d, Y') }}</small>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="{{ route('quotations.show', $quote) }}"
                                                                    class="btn btn-outline-primary" title="View">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="{{ route('quotations.print', $quote) }}"
                                                                    class="btn btn-outline-secondary" title="Print"
                                                                    target="_blank">
                                                                    <i class="fas fa-print"></i>
                                                                </a>
                                                                @if ($quote->status == 'draft')
                                                                    <form method="POST"
                                                                        action="{{ route('quotations.send', $quote) }}"
                                                                        class="d-inline">
                                                                        @csrf
                                                                        <button type="submit"
                                                                            class="btn btn-outline-info" title="Send">
                                                                            <i class="fas fa-paper-plane"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                                @if (in_array($quote->status, ['draft', 'sent']))
                                                                    <form method="POST"
                                                                        action="{{ route('quotations.accept', $quote) }}"
                                                                        class="d-inline"
                                                                        onsubmit="return confirmAccept(this);">
                                                                        @csrf
                                                                        <input type="hidden" name="close_enquiry"
                                                                            value="1">
                                                                        <button type="submit"
                                                                            class="btn btn-outline-success"
                                                                            title="Accept & Close">
                                                                            <i class="fas fa-check"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                                @if ($quote->status != 'accepted')
                                                                    <a href="{{ route('quotations.revise', $quote) }}"
                                                                        class="btn btn-outline-warning" title="Revise">
                                                                        <i class="fas fa-code-branch"></i>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-file-invoice-dollar fa-3x  mb-3"></i>
                                        <h5>No quotations yet</h5>
                                        <p class="">Click "Convert to Quote" to generate the first quotation.</p>
                                        <a href="{{ route('quotations.create', $enquiry) }}" class="btn btn-success">
                                            <i class="fas fa-plus me-1"></i>Convert to Quote
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Message Tab -->
                    @if ($enquiry->message)
                        <div class="tab-pane fade" id="message" role="tabpanel">
                            <div class="card shadow-sm border-top-0 rounded-0 rounded-bottom">
                                <div class="card-body">
                                    <p class="mb-0">{{ $enquiry->message }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Attachments -->
                @if ($enquiry->attachments && count($enquiry->attachments))
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-paperclip me-2"></i>Attachments
                                ({{ count($enquiry->attachments) }})</h6>
                        </div>
                        <div class="card-body">
                            @foreach ($enquiry->attachments as $attachment)
                                <a href="{{ Storage::url($attachment) }}"
                                    class="btn btn-outline-primary btn-sm w-100 mb-2" target="_blank">
                                    <i class="fas fa-download me-1"></i>{{ basename($attachment) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body text-center py-4">
                     @if($enquiry->status !='closed')
                        <a href="{{ route('quotations.create', $enquiry) }}" class="btn btn-success btn-lg w-100 mb-2">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Convert to Quote   <span class="badge bg-primary"> {{ $enquiry->quotations->count() }}</span>
                        </a>

                        @if($enquiry->status == 'pending')
                        <a href="{{ route('enquiries.edit', $enquiry) }}" class="btn btn-warning btn-lg w-100 mb-2">
                            <i class="fas fa-edit me-2"></i>Edit Enquiry
                        </a>
                        @endif
                        @endif
                        <a href="{{ route('enquiries.index') }}" class="btn btn-outline-secondary btn-lg w-100">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmAccept(form) {
                return confirm('Accept this quotation? This will mark the enquiry as closed and expire other quotes.');
            }
        </script>
    @endpush
@endsection
