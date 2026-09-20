@extends('layouts.app')

@section('title', 'Quotation Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>
                            Quotation Management
                        </h4>
                        <p class="text-muted mb-0">
                            Manage all quotations.
                            @if (isset($quotations))
                                {{ $quotations->total() }} total
                            @endif
                        </p>
                    </div>
                </div>

                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Quote #</th>
                                        <th>Enquiry</th>
                                        <th>Customer</th>
                                        <th>Version</th>
                                        <th>Status</th>
                                        <th class="text-end">Total</th>
                                        <th>Valid Until</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($quotations ?? [] as $quotation)
                                        <tr class="{{ $quotation->is_accepted ? 'table-success' : '' }}">
                                            <td>
                                                <a href="{{ route('quotations.show', $quotation) }}"
                                                    class="fw-bold text-decoration-none">
                                                    {{ $quotation->quote_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('enquiries.show', $quotation->enquiry) }}">
                                                    #{{ $quotation->enquiry->id }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="fw-bold">
                                                    {{ $quotation->enquiry->customer->company ?? $quotation->enquiry->customer->name }}
                                                </div>
                                            </td>
                                            <td>V{{ $quotation->version }}</td>
                                            <td>
                                                <span class="badge {{ $quotation->status_badge }} px-3 py-2">
                                                    {{ $quotation->status_label }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold text-success">
                                                ₹{{ number_format($quotation->total_amount, 2) }}
                                            </td>
                                            <td>
                                                {{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'N/A' }}
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('quotations.show', $quotation) }}"
                                                        class="btn btn-outline-primary" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('quotations.print', $quotation) }}"
                                                        class="btn btn-outline-secondary" title="Print" target="_blank">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                   {{-- @if (!in_array($quotation->status, ['converted', 'expired']))
                                                        <form action="{{ route('sales-orders.store') }}" method="POST" class="d-inline" onsubmit="return confirm('Convert this quotation to a sales order?');">
                                                            @csrf
                                                            <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
                                                            <button type="submit" class="btn btn-outline-success" title="Convert to Sales Order">
                                                                <i class="fas fa-shopping-cart"></i>
                                                            </button>
                                                        </form>
                                                    @endif --}}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <h5>No quotations yet</h5>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if (isset($quotations) && $quotations->hasPages())
                        <div class="card-footer">
                            {{ $quotations->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
