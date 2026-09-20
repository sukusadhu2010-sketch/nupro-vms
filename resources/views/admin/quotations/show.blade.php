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
                <p class=" mb-0">
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
                <!-- ============ QUOTATION DETAILS ============ -->
                <div class="card shadow-lg">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold">Quotation Details</h6>
                    </div>
                    <div class="card-body">

                        <!-- Header fields -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Quote Number</label>
                                <p class="form-control bg-light">{{ $quotation->quote_number }}</p>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Version</label>
                                <p class="form-control bg-light">{{ $quotation->version }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Valid Until</label>
                                <p class="form-control bg-light">{{ $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : 'Not specified' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Customer</label>
                                <p class="form-control bg-light">{{ $quotation->enquiry->customer->company ?? $quotation->enquiry->customer->name }}</p>
                            </div>
                        </div>

                        <!-- ============ ACKNOWLEDGEMENT & PRODUCT SPEC ============ -->
                        <div class="card shadow-sm border-0 rounded-4 mb-4 mt-4">
                            <div class="card-header bg-light rounded-top-4">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-align-left me-2 text-success"></i>Acknowledgement &amp; Product Specification</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Acknowledgement Text</label>
                                        <p class="form-control bg-light">{{ $quotation->acknowledgement ?? '—' }}</p>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Customer Information</label>
                                        <p class="form-control bg-light" style="white-space: pre-line;">{{ $quotation->customer_information ?? '—' }}</p>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Kind Atten.</label>
                                        <p class="form-control bg-light">{{ $quotation->kind_attention ?? '—' }}</p>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Product Specification / Type</label>
                                        <p class="form-control bg-light">{{ $quotation->product_spec ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============ QUOTE ITEMS ============ -->
                        <label class="form-label fw-bold mb-3">Quote Items <span class="text-danger">*</span></label>
                        @forelse ($quotation->items as $i => $item)
                            <div class="row mb-3 border p-3 rounded bg-light item-row">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Product Name</label>
                                    <p class="form-control bg-light">
                                        {{ $item->product->name }} - {{ $item->product->productCategory?->name ?? $item->product->category ?? 'General' }}
                                    </p>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small">Quantity</label>
                                    <p class="form-control bg-light">{{ $item->quantity }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Unit Price</label>
                                    <p class="form-control bg-light">₹{{ number_format((float) $item->unit_price, 2) }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Total Price</label>
                                    <p class="form-control bg-light">₹{{ number_format((float) $item->total_price, 2) }}</p>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="description-entry border rounded-3 p-3 bg-white">
                                        <div class="mb-2">
                                            <strong class="small"><i class="fas fa-list-ul me-1 text-warning"></i>Additional Informations</strong>
                                        </div>
                                        <div class="row g-2">
                                            @foreach (['moc' => '* MOC (Material of Construction)', 'mfg_spec' => '* MFG Spec (Manufacturing Specification)', 'trim' => '* Trim', 'operation' => '* Operation', 'end_connection' => '* End Connection', 'rating' => '* Rating', 'media' => '* Media'] as $field => $descLabel)
                                                <div class="col-md-6 col-lg-4">
                                                    <label class="form-label small fw-semibold mb-1">{{ $descLabel }}</label>
                                                    <p class="form-control form-control-sm bg-light">{{ $item->{$field} ?? '—' }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-2">
                                    <label class="form-label fw-bold small">Remarks</label>
                                    <p class="form-control bg-light" style="white-space: pre-line;">{{ $item->remarks ?? $item->notes ?? '—' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No items on this quotation.</p>
                        @endforelse

                        <!-- ============ TERMS & CONDITIONS ============ -->
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-light rounded-top-4">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-file-contract me-2 text-danger"></i>Terms &amp; Conditions</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Delivery Terms</label>
                                        <p class="form-control bg-light">{{ $quotation->delivery_terms ?? '—' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Warranty Terms</label>
                                        <p class="form-control bg-light">{{ $quotation->warranty_terms ?? '—' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">PRICES BASIS</label>
                                        <p class="form-control bg-light">
                                            {{ $quotation->prices_basis ?? '—' }}
                                            @if ($quotation->prices_basis_option)
                                                <small class="text-muted d-block">Option: {{ $quotation->prices_basis_option === 'ex-works' ? 'Ex-Workers' : 'Transported Godown' }}</small>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Payment Terms</label>
                                        <div class="border rounded-3 p-2 bg-white">
                                            @foreach (['LC / Credit' => 'lc_credit', 'Advance + PI' => 'advance_pi', 'PDC' => 'pdc', 'Proforma Invoice' => 'proforma_invoice'] as $label => $key)
                                                <div class="d-flex gap-2 align-items-center mb-2">
                                                    <div class="form-check" style="min-width: 160px;">
                                                        <input class="form-check-input" type="radio" disabled
                                                            {{ $quotation->payment_term_option === $key ? 'checked' : '' }}>
                                                        <label class="form-check-label small">{{ $label }}</label>
                                                    </div>
                                                    <span class="form-control form-control-sm bg-light">{{ ($quotation->payment_term_text[$key] ?? '') ?: '—' }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Inspection — Vendor Scope</label>
                                        <p class="form-control bg-light" style="white-space: pre-line;">{{ $quotation->inspection_vendor_scope ?? '—' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Inspection — Third Party Scope</label>
                                        <p class="form-control bg-light" style="white-space: pre-line;">{{ $quotation->inspection_third_party_scope ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============ NOTES & SIGNATORY ============ -->
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-light rounded-top-4">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-pen-nib me-2 text-secondary"></i>Notes &amp; Signatory</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <div class="border rounded-3 p-2 bg-light">
                                            @if ($quotation->notes)
                                                {!! \App\Support\HtmlSanitizer::clean($quotation->notes) !!}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="margin-top: 10px;">
                                        <label class="form-label fw-semibold">Closing Statement</label>
                                        <p class="form-control bg-light">{{ $quotation->closing_statement ?? '—' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Authorized Signatory (Company)</label>
                                        <p class="form-control bg-light">{{ $quotation->signatory_company ?? '—' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Designation</label>
                                        <p class="form-control bg-light">{{ $quotation->signatory_designation ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- ============ SUMMARY ============ -->
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold">Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Items:</span>
                            <span>{{ $quotation->items->sum('quantity') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Amount (Qty):</span>
                            <span>₹{{ number_format((float) $quotation->total_amount, 2) }}</span>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold small mb-1">Tax Details</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" disabled
                                    {{ ($quotation->tax_type ?? 'igst') === 'igst' ? 'checked' : '' }}>
                                <label class="form-check-label small">IGST @18%</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" disabled
                                    {{ ($quotation->tax_type ?? 'igst') === 'sgst_cgst' ? 'checked' : '' }}>
                                <label class="form-check-label small">SGST @9% + CGST @9%</label>
                            </div>
                        </div>
                        @php
                            $taxType = $quotation->tax_type ?? 'igst';
                            $showIgst = $taxType === 'igst' && (float) ($quotation->igst ?? 0) > 0;
                            $showSgst = $taxType === 'sgst_cgst' && (float) ($quotation->sgst ?? 0) > 0;
                            $showCgst = $taxType === 'sgst_cgst' && (float) ($quotation->cgst ?? 0) > 0;
                        @endphp
                        @if ($showIgst)
                            <div class="d-flex justify-content-between mb-2">
                                <span>IGST @18%:</span><span>₹{{ number_format((float) $quotation->igst, 2) }}</span>
                            </div>
                        @endif
                        @if ($showSgst)
                            <div class="d-flex justify-content-between mb-2">
                                <span>SGST @9%:</span><span>₹{{ number_format((float) $quotation->sgst, 2) }}</span>
                            </div>
                        @endif
                        @if ($showCgst)
                            <div class="d-flex justify-content-between mb-2">
                                <span>CGST @9%:</span><span>₹{{ number_format((float) $quotation->cgst, 2) }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between">
                            <span class="h5 fw-bold">Grand Total:</span>
                            <span class="h4 text-success">₹{{ number_format($quotation->grand_total, 2) }}</span>
                        </div>
                        <div class="mt-2 border-top pt-2">
                            <small class="text-muted d-block">Amount (in words)</small>
                            <small class="fw-semibold">{{ $quotation->amount_in_words ?? '—' }}</small>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                @if ($quotation->attachments && count($quotation->attachments))
                    <div class="card shadow-sm mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-paperclip me-2"></i>Attachments ({{ count($quotation->attachments) }})</h6>
                        </div>
                        <div class="card-body">
                            @foreach ($quotation->attachments as $attachment)
                                <a href="{{ Storage::url($attachment) }}"
                                    class="btn btn-outline-primary btn-sm w-100 mb-2" target="_blank">
                                    <i class="fas fa-download me-1"></i>{{ basename($attachment) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="card shadow-sm mt-3">
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
