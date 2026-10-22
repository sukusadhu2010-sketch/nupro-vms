@extends('layouts.app')

@section('title', 'SD/PBG Register')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold">
                        <i class="fas fa-file-contract me-2 text-warning"></i>
                        SD/PBG Register
                    </h4>
                    <p class="text-muted mb-0">Security Deposit / Performance Bank Guarantee records (SD/PBG = Yes)</p>
                </div>
                <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Sales Orders
                </a>
            </div>

            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Filters -->
            <div class="card shadow-sm border-0 rounded-3 mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center"
                    data-bs-toggle="collapse" data-bs-target="#filtersCard" role="button" aria-expanded="true">
                    <span class="fw-bold"><i class="fas fa-filter me-2 text-primary"></i>Filters</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="collapse show" id="filtersCard">
                    <div class="card-body">
                        <form id="filtersForm" method="GET" action="{{ route('sd-pbg.index') }}">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Client</label>
                                    <input type="text" name="client" class="form-control form-control-sm"
                                        placeholder="Client name..." value="{{ $filters['client'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">BG No.</label>
                                    <input type="text" name="bg_number" class="form-control form-control-sm"
                                        placeholder="BG number..." value="{{ $filters['bg_number'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">PO No.</label>
                                    <input type="text" name="po_no" class="form-control form-control-sm"
                                        placeholder="PO number..." value="{{ $filters['po_no'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Invoice No.</label>
                                    <input type="text" name="invoice_no" class="form-control form-control-sm"
                                        placeholder="Invoice number..." value="{{ $filters['invoice_no'] ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Status</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="all">All</option>
                                        @foreach ($statuses as $status)
                                        <option value="{{ $status }}"
                                            {{ ($filters['status'] ?? 'all') === $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">BG Date From</label>
                                    <input type="date" name="bg_date_from" class="form-control form-control-sm"
                                        value="{{ $filters['bg_date_from'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">BG Date To</label>
                                    <input type="date" name="bg_date_to" class="form-control form-control-sm"
                                        value="{{ $filters['bg_date_to'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Expiry From</label>
                                    <input type="date" name="expiry_from" class="form-control form-control-sm"
                                        value="{{ $filters['expiry_from'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Expiry To</label>
                                    <input type="date" name="expiry_to" class="form-control form-control-sm"
                                        value="{{ $filters['expiry_to'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Claim From</label>
                                    <input type="date" name="claim_from" class="form-control form-control-sm"
                                        value="{{ $filters['claim_from'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Claim To</label>
                                    <input type="date" name="claim_to" class="form-control form-control-sm"
                                        value="{{ $filters['claim_to'] ?? '' }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-search me-1"></i>Apply
                                    </button>
                                    <a href="{{ route('sd-pbg.index') }}" class="btn btn-secondary btn-sm w-100">
                                        <i class="fas fa-rotate-left me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="badge bg-light text-dark border px-3 py-2">
                        <i class="fas fa-list-ol me-1 text-primary"></i>
                        Total Records: <strong>{{ $totalRecords }}</strong>
                    </span>
                </div>
                <button type="button" id="exportSdPbgBtn" class="btn btn-outline-success btn-sm"
                    data-export-url="{{ route('sd-pbg.export.excel', request()->only(['client', 'bg_number', 'po_no', 'invoice_no', 'status', 'bg_date_from', 'bg_date_to', 'expiry_from', 'expiry_to', 'claim_from', 'claim_to'])) }}">
                    <i class="fas fa-file-excel me-2"></i>Export
                </button>
            </div>

            <!-- Loading overlay -->
            <div id="loadingOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100"
                style="background: rgba(0,0,0,0.35); z-index: 1050;">
                <div class="position-absolute top-50 start-50 translate-middle text-white text-center">
                    <div class="spinner-border text-light mb-2" role="status" style="width: 3rem; height: 3rem;"></div>
                    <div class="fw-semibold">Generating Excel file...</div>
                </div>
            </div>

            <!-- Toast container -->
            <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1060;">
                <div id="exportToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body" id="exportToastBody"></div>
                        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            </div>

            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>SL No</th>
                                    <th>Client</th>
                                    <th>PO No.</th>
                                    <th>PO Date</th>
                                    <th>Invoice No.</th>
                                    <th>Invoice Date</th>
                                    <th>Claim Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($guarantees as $bg)
                                <tr>
                                    <td>{{ ($guarantees->currentPage() - 1) * $guarantees->perPage() + $loop->iteration }}</td>
                                    <td>{{ $bg->client_name }}</td>
                                    <td>{{ $bg->salesOrder?->customer_po_number ?? '—' }}</td>
                                    <td>{{ $bg->salesOrder?->customer_po_date?->format('d-m-Y') ?? '—' }}</td>
                                    <td>{{ $bg->invoice?->invoice_number ?? '—' }}</td>
                                    <td>{{ $bg->invoice?->invoice_date?->format('d-m-Y') ?? '—' }}</td>
                                    <td class="fw-bold text-success">{{ number_format($bg->claim_amount, 2) }}</td>
                                    <td>
                                        <span class="badge px-3 py-2
                                                    {{ ['active' => 'bg-success', 'expired' => 'bg-secondary', 'closed' => 'bg-dark', 'released' => 'bg-info', 'invoked' => 'bg-danger'][$bg->status] ?? 'bg-secondary' }}">
                                            {{ ucfirst($bg->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('sales-orders.show', $bg->salesOrder) }}"
                                            class="btn btn-outline-primary btn-sm" title="View Sales Order">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        No SD/PBG records available for export.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($guarantees->hasPages())
                <div class="card-footer">
                    {{ $guarantees->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('loadingOverlay');
        const showLoading = () => overlay && overlay.classList.remove('d-none');
        const hideLoading = () => overlay && overlay.classList.add('d-none');

        const filtersForm = document.getElementById('filtersForm');
        if (filtersForm) {
            filtersForm.addEventListener('submit', showLoading);
        }

        const exportBtn = document.getElementById('exportSdPbgBtn');
        const toastEl = document.getElementById('exportToast');
        const toastBody = document.getElementById('exportToastBody');

        function showToast(message, type) {
            if (!toastEl) return;
            toastEl.classList.remove('bg-success', 'bg-danger', 'text-white');
            toastEl.classList.add(type === 'success' ? 'bg-success' : 'bg-danger', 'text-white');
            toastBody.textContent = message;
            bootstrap.Toast.getOrCreateInstance(toastEl, {
                delay: 4000
            }).show();
        }

        let exportInProgress = false;

        if (exportBtn) {
            exportBtn.addEventListener('click', async function() {
                if (exportInProgress) return; // prevent duplicate export requests
                exportInProgress = true;
                exportBtn.disabled = true;
                showLoading();

                try {
                    const response = await fetch(exportBtn.dataset.exportUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        let message = 'Export failed (HTTP ' + response.status + ').';
                        try {
                            const data = await response.json();
                            if (data && data.message) message = data.message;
                        } catch (e) {
                            /* not JSON — keep default message */ }
                        throw new Error(message);
                    }

                    const blob = await response.blob();

                    const disposition = response.headers.get('Content-Disposition') || '';
                    const match = disposition.match(/filename\*?=(?:"([^"]+)"|([^;]+))/);
                    const filename = match ?
                        (match[1] || match[2]).trim().replace(/"/g, '') :
                        'SD_PBG_Register.xlsx';

                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    URL.revokeObjectURL(link.href);

                    showToast('SD/PBG Register exported successfully.', 'success');
                } catch (error) {
                    if (!(error instanceof DOMException && error.name === 'AbortError')) {
                        console.error('SD/PBG export failed:', error);
                        showToast(error.message || 'Export failed. Please try again.', 'error');
                    }
                } finally {
                    exportInProgress = false;
                    exportBtn.disabled = false;
                    hideLoading();
                }
            });
        }
    });
</script>
@endpush