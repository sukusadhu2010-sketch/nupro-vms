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
                <!-- Enquiry Reference -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-link me-2"></i>Linked Enquiry</h6>
                    </div>
                    <div class="card-body text-white">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Enquiry #{{ $quotation->enquiry->id }}</strong><br>
                                <small
                                    class="">{{  $quotation->enquiry->customer->name }}</small>
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
                                                <br><small class="">SKU: {{ $item->product->sku }}</small>
                                                <br><small class="">Vendor:
                                                    {{ $item->product->vendor?->name }}</small>
                                            </td>
                                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                            <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-end fw-bold text-success">
                                                ₹{{ number_format($item->total_price, 2) }}</td>
                                            <td>
                                                @if ($item->notes)
                                                    <small class="">{{ $item->notes }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><td colspan="6">
                                            <div class="col-12 mt-2">
                                            <label class="form-label fw-bold small mb-1">LC / Credit / Advance / PIC / PDC / Proforma Invoice</label>
                                            <div class="d-flex flex-wrap gap-3">
                                                @foreach (['LC' => 'LC', 'Credit' => 'Credit', 'Advance' => 'Advance', 'PIC' => 'PIC', 'PDC' => 'PDC', 'Proforma Invoice' => 'Proforma Invoice'] as $key => $label)
                                                    <div class="form-check">
                                                        <input class="form-check-input pay-method" type="checkbox"
                                                            name="items[{{ $i }}][payment_methods][]" value="{{ $key }}"
                                                            id="pay_{{ $i }}_{{ $loop->index }}"
                                                            {{ in_array($key, $item->payment_methods ?? []) ? 'checked' : '' }}>
                                                        <label class="form-check-label small" for="pay_{{ $i }}_{{ $loop->index }}">{{ $label }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label class="form-label fw-bold small mb-1">Description</label>
                                            <div class="row g-2">
                                                <div class="col-md-3"><label class="form-label fw-bold small mb-1">MOC</label><input type="text" class="form-control form-control-sm bg-light" name="items[{{ $i }}][moc]" value="{{ $item->moc ?? '' }}" readonly></div>
                                                <div class="col-md-3"><label class="form-label fw-bold small mb-1">MFG Spec</label><input type="text" class="form-control form-control-sm bg-light" name="items[{{ $i }}][mfg_spec]" value="{{ $item->mfg_spec ?? '' }}" readonly></div>
                                                <div class="col-md-2"><label class="form-label fw-bold small mb-1">Trim</label><input type="text" class="form-control form-control-sm bg-light" name="items[{{ $i }}][trim]" value="{{ $item->trim ?? '' }}" readonly></div>
                                                <div class="col-md-2"><label class="form-label fw-bold small mb-1">Operation</label><input type="text" class="form-control form-control-sm bg-light" name="items[{{ $i }}][operation]" value="{{ $item->operation ?? '' }}" readonly></div>
                                                <div class="col-md-2"><label class="form-label fw-bold small mb-1">End Connection</label><input type="text" class="form-control form-control-sm bg-light" name="items[{{ $i }}][end_connection]" value="{{ $item->end_connection ?? '' }}" readonly></div>
                                                <div class="col-md-3"><label class="form-label fw-bold small mb-1">Rating</label><input type="text" class="form-control form-control-sm bg-light" name="items[{{ $i }}][rating]" value="{{ $item->rating ?? '' }}" readonly></div>
                                                <div class="col-md-3"><label class="form-label fw-bold small mb-1">Media</label><input type="text" class="form-control form-control-sm bg-light" name="items[{{ $i }}][media]" value="{{ $item->media ?? '' }}" readonly></div>
                                            </div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label class="form-label fw-bold small">Remarks</label>
                                            <textarea class="form-control" name="items[{{ $i }}][remarks]" rows="2" placeholder="Remarks">{{ $item->remarks ?? $item->notes }}</textarea>
                                        </div>
                                        </td></tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-group-divider">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Subtotal (Quantity Amount):</td>
                                        <td class="text-end fw-bold">
                                            ₹{{ number_format($quotation->total_amount, 2) }}</td>
                                        <td></td>
                                    </tr>
                                    @if (($quotation->tax_amount ?? 0) > 0)
                                        <tr>
                                            <td colspan="4" class="text-end">Tax
                                                @if ($quotation->tax_type === 'igst') (IGST @ {{ $quotation->igst }}%)
                                                @else (CGST @ {{ $quotation->cgst }}% + SGST @ {{ $quotation->sgst }}%)
                                                @endif</td>
                                            <td class="text-end">₹{{ number_format($quotation->tax_amount, 2) }}</td>
                                            <td></td>
                                        </tr>
                                    @endif
                                    <tr class="table-active">
                                        <td colspan="4" class="text-end h5 fw-bold">Grand Total:</td>
                                        <td class="text-end h4 fw-bold text-success">
                                            ₹{{ number_format($quotation->total_amount + ($quotation->tax_amount ?? 0), 2) }}</td>
                                        <td></td>
                                    </tr>
                                    @if ($quotation->amount_in_words)
                                        <tr>
                                            <td colspan="6" class="fst-italic">
                                                <small><strong>Amount in Words:</strong> {{ $quotation->amount_in_words }}</small>
                                            </td>
                                        </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                @if ($quotation->acknowledgement || $quotation->product_spec)
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Acknowledgement & Product Specification</h6>
                        </div>
                        <div class="card-body text-white">
                            @if ($quotation->acknowledgement)
                                <p class="mb-2">{{ $quotation->acknowledgement }}</p>
                            @endif
                            @if ($quotation->product_spec)
                                <div><small class=" d-block">Product Specification / Type</small><strong>{{ $quotation->product_spec }}</strong></div>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($quotation->delivery_terms || $quotation->warranty_terms || $quotation->payment_terms || $quotation->inspection_vendor_scope || $quotation->inspection_third_party_scope)
                    <div class="card shadow-sm mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-file-contract me-2"></i>Terms & Conditions</h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                @if ($quotation->delivery_terms)
                                    <dt class="col-sm-4">Delivery Terms</dt><dd class="col-sm-8">{{ $quotation->delivery_terms }}</dd>
                                @endif
                                @if ($quotation->warranty_terms)
                                    <dt class="col-sm-4">Warranty Terms</dt><dd class="col-sm-8">{{ $quotation->warranty_terms }}</dd>
                                @endif
                                @if ($quotation->payment_terms)
                                    <dt class="col-sm-4">Payment Terms</dt><dd class="col-sm-8">{!! nl2br(e($quotation->payment_terms)) !!}</dd>
                                @endif
                                @if ($quotation->inspection_vendor_scope)
                                    <dt class="col-sm-4">Inspection — Vendor Scope</dt><dd class="col-sm-8">{!! nl2br(e($quotation->inspection_vendor_scope)) !!}</dd>
                                @endif
                                @if ($quotation->inspection_third_party_scope)
                                    <dt class="col-sm-4">Inspection — Third Party</dt><dd class="col-sm-8">{!! nl2br(e($quotation->inspection_third_party_scope)) !!}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif

                @if ($quotation->notes || $quotation->closing_statement || $quotation->signatory_company || $quotation->signatory_designation)
                    <div class="card shadow-sm mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-pen-nib me-2"></i>Notes & Signatory</h6>
                        </div>
                        <div class="card-body ">
                            @if ($quotation->notes)
                                <div class="mb-3">{!! \App\Support\HtmlSanitizer::clean($quotation->notes) !!}</div>
                            @endif
                            @if ($quotation->closing_statement)
                                <p class="mb-2 fst-italic">{{ $quotation->closing_statement }}</p>
                            @endif
                            @if ($quotation->signatory_company || $quotation->signatory_designation)
                                <div class="border-top pt-2">
                                    <strong>{{ $quotation->signatory_company }}</strong>
                                    @if ($quotation->signatory_designation)<div class="small">{{ $quotation->signatory_designation }}</div>@endif
                                </div>
                            @endif
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
                    <div class="card-body ">
                        <div class="mb-3">
                            <small class=" d-block">Quote Number</small>
                            <strong>{{ $quotation->quote_number }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class=" d-block">Version</small>
                            <strong>V{{ $quotation->version }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class=" d-block">Status</small>
                            <span
                                class="badge {{ $quotation->status_badge }} px-3 py-2">{{ $quotation->status_label }}</span>
                        </div>
                        <div class="mb-3">
                            <small class=" d-block">Valid Until</small>
                            <strong>{{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'Not specified' }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class=" d-block">Created</small>
                            <strong>{{ $quotation->created_at->format('M d, Y H:i') }}</strong>
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
