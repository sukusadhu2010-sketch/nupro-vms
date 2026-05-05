@extends('layouts.app')

@section('title', 'Quotation {{ $quotation->quote_number }}')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('enquiries.index') }}">Enquiries</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('enquiries.show', $quotation->enquiry) }}">#{{ $quotation->enquiry->id }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ $quotation->quote_number }}</li>
                    </ol>
                </nav>
                <h1 class="h2 mb-1">
                    <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>{{ $quotation->quote_number }}
                </h1>
                <p class="text-muted mb-0">
                    Version {{ $quotation->version }} &bull;
                    Created {{ $quotation->created_at->format('M d, Y') }}
                </p>
            </div>
            <div class="col-auto">
                <span class="badge {{ $quotation->status_badge }} fs-4 px-4 py-2">
                    {{ $quotation->status_label }}
                </span>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Enquiry Reference -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-link me-2"></i>Linked Enquiry</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Enquiry #{{ $quotation->enquiry->id }}</strong><br>
                                <small
                                    class="text-muted">{{ $quotation->enquiry->customer->company ?? $quotation->enquiry->customer->name }}</small>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ route('enquiries.show', $quotation->enquiry) }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Enquiry
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quotation Items -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-list me-2"></i>Quote Items</h6>
                        <span class="badge bg-light text-dark">{{ $quotation->items->count() }} items</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($quotation->items as $i => $item)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->product->name }}</strong>
                                                <br><small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                                <br><small class="text-muted">Vendor:
                                                    {{ $item->product->vendor->company }}</small>
                                            </td>
                                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                            <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-end fw-bold text-success">
                                                ${{ number_format($item->total_price, 2) }}</td>
                                            <td>
                                                @if ($item->notes)
                                                    <small class="text-muted">{{ $item->notes }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-group-divider">
                                    <tr class="table-active">
                                        <td colspan="4" class="text-end h5 fw-bold">Grand Total:</td>
                                        <td class="text-end h4 fw-bold text-success">
                                            ${{ number_format($quotation->total_amount, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                @if ($quotation->notes)
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Terms & Notes</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $quotation->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <!-- Quote Info -->
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quote Info</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Quote Number</small>
                            <strong>{{ $quotation->quote_number }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Version</small>
                            <strong>V{{ $quotation->version }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Status</small>
                            <span
                                class="badge {{ $quotation->status_badge }} px-3 py-2">{{ $quotation->status_label }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Valid Until</small>
                            <strong>{{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'Not specified' }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Created</small>
                            <strong>{{ $quotation->created_at->format('M d, Y H:i') }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body text-center py-4">
                        <a href="{{ route('quotations.print', $quotation) }}" class="btn btn-secondary btn-lg w-100 mb-2"
                            target="_blank">
                            <i class="fas fa-print me-2"></i>Print / PDF
                        </a>

                        @if ($quotation->status == 'draft')
                            <form method="POST" action="{{ route('quotations.send', $quotation) }}" class="d-block mb-2">
                                @csrf
                                <button type="submit" class="btn btn-info btn-lg w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Mark as Sent
                                </button>
                            </form>
                        @endif

                        @if (in_array($quotation->status, ['draft', 'sent']))
                            <form method="POST" action="{{ route('quotations.accept', $quotation) }}" class="d-block mb-2"
                                onsubmit="return confirm('Accept this quotation and close the enquiry?');">
                                @csrf
                                <input type="hidden" name="close_enquiry" value="1">
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-check-circle me-2"></i>Accept & Close
                                </button>
                            </form>
                        @endif

                        @if ($quotation->status != 'accepted')
                            <a href="{{ route('quotations.revise', $quotation) }}"
                                class="btn btn-warning btn-lg w-100 mb-2">
                                <i class="fas fa-code-branch me-2"></i>Create Revision
                            </a>
                        @endif

                        <a href="{{ route('enquiries.show', $quotation->enquiry) }}"
                            class="btn btn-outline-secondary btn-lg w-100">
                            <i class="fas fa-arrow-left me-2"></i>Back to Enquiry
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
