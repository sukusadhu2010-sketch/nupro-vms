@extends('layouts.app')

@section('title', 'Quotation Form - ' . $form->ref_no)

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-gradient-primary text-white rounded-top-4 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-file-invoice me-2"></i>{{ $form->ref_no }}
                    <span class="badge bg-light text-dark ms-2">{{ ucfirst($form->status) }}</span>
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('quotation-forms.edit', $form) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('quotation-forms.print', $form) }}" target="_blank" class="btn btn-light btn-sm">
                        <i class="fas fa-print me-1"></i>Print / Export
                    </a>
                    @if ($form->status !== 'final')
                        <form action="{{ route('quotation-forms.finalize', $form) }}" method="POST">
                            @csrf
                            <button class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i>Finalize</button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <h4 class="fw-bold mb-0">{{ $form->company_name }}</h4>
                    <small class="text-muted">{{ $form->certification }}</small>
                </div>
                <div class="d-flex justify-content-between fw-semibold mb-2">
                    <span>Ref. No.: {{ $form->ref_no }}</span>
                    <span>Date: {{ $form->form_date?->format('d-m-Y') }}</span>
                </div>
                <h6 class="text-center text-decoration-underline fw-bold my-3">{{ $form->title }}</h6>

                <table class="table table-bordered mb-3">
                    <tr><th style="width:22%">To</th>
                        <td><strong>{{ $form->to_company }}</strong><br>{!! nl2br(e($form->to_address ?? '')) !!}
                            @if ($form->project_details)<br><strong>Project:</strong> {{ $form->project_details }}@endif</td></tr>
                    <tr><th>Contact Person</th><td>{{ $form->contact_person }} {{ $form->contact_designation ? '(' . $form->contact_designation . ')' : '' }}</td></tr>
                </table>

                @if ($form->acknowledgement)
                    <p>{{ $form->acknowledgement }}</p>
                @endif
                <p class="fw-semibold">Product Specification / Type: {{ $form->product_spec }}</p>

                <h6 class="fw-bold mt-3">Description</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>#</th><th>* MOC</th><th>* MFG Spec</th><th>* Trim</th>
                                <th>* Operation</th><th>* End Connection</th><th>* Rating</th><th>* Media</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($form->description_entries ?? [] as $i => $d)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $d['moc'] ?? '' }}</td><td>{{ $d['mfg_spec'] ?? '' }}</td>
                                    <td>{{ $d['trim'] ?? '' }}</td><td>{{ $d['operation'] ?? '' }}</td>
                                    <td>{{ $d['end_connection'] ?? '' }}</td><td>{{ $d['rating'] ?? '' }}</td>
                                    <td>{{ $d['media'] ?? '' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-muted">No description entries.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold mt-3">Items</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr><th>#</th><th>Product Name</th><th>Size (MM)</th><th>Quantity (Nos)</th>
                                <th>Unit Price (Each No.)</th><th>Total Price</th><th>Payment Mode</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($form->items ?? [] as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item['product_name'] ?? '' }}</td>
                                    <td>{{ $item['size_mm'] ?? '' }}</td>
                                    <td>{{ $item['quantity'] ?? '' }}</td>
                                    <td>{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($item['total_price'] ?? 0, 2) }}</td>
                                    <td>{{ implode(', ', $item['payment_modes'] ?? []) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-muted">No items.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr><td colspan="4" class="text-end">Grand Total</td>
                                <td>{{ number_format($form->grand_total, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>

                <h6 class="fw-bold mt-3">Terms &amp; Conditions</h6>
                <table class="table table-bordered mb-3">
                    <tr><th style="width:28%">Delivery Terms</th><td>{{ $form->delivery_terms }}</td></tr>
                    <tr><th>Tax Details</th><td>IGST: {{ $form->igst_percent }}% | SGST: {{ $form->sgst_percent }}% | CGST: {{ $form->cgst_percent }}%</td></tr>
                    <tr><th>Payment Terms</th><td>{!! nl2br(e($form->payment_terms ?? '')) !!}</td></tr>
                    <tr><th>Inspection — Vendor Scope</th><td>{!! nl2br(e($form->inspection_vendor_scope ?? '')) !!}</td></tr>
                    <tr><th>Inspection — Third Party Scope</th><td>{!! nl2br(e($form->inspection_third_party_scope ?? '')) !!}</td></tr>
                    <tr><th>Warranty</th><td>{{ $form->warranty_terms }}</td></tr>
                </table>

                @if ($form->notes)
                    <h6 class="fw-bold">Notes</h6>
                    <p>{!! nl2br(e($form->notes)) !!}</p>
                @endif

                <p class="fw-semibold">{{ $form->closing_statement }}</p>
                <div class="text-end mt-4">
                    <div class="border-top d-inline-block px-4 pt-2">
                        <strong>For {{ $form->signatory_company }}</strong><br>
                        (Authorized Signatory)<br>
                        {{ $form->signatory_designation }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
