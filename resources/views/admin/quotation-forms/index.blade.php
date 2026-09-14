@extends('layouts.app')

@section('title', 'Quotation Forms')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-gradient-primary text-white rounded-top-4 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-file-signature me-2"></i>Quotation Forms</h5>
                <a href="{{ route('quotation-forms.create') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i>New Quotation Form
                </a>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ref. No.</th>
                                <th>Date</th>
                                <th>To (Company)</th>
                                <th>Product Spec</th>
                                <th class="text-end">Grand Total</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($forms as $form)
                                <tr>
                                    <td class="fw-semibold">{{ $form->ref_no }}</td>
                                    <td>{{ $form->form_date?->format('d-m-Y') }}</td>
                                    <td>{{ $form->to_company }}</td>
                                    <td>{{ $form->product_spec }}</td>
                                    <td class="text-end">{{ number_format($form->grand_total, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $form->status === 'final' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($form->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('quotation-forms.show', $form) }}"
                                            class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('quotation-forms.edit', $form) }}"
                                            class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="{{ route('quotation-forms.print', $form) }}" target="_blank"
                                            class="btn btn-sm btn-outline-secondary" title="Print"><i class="fas fa-print"></i></a>
                                        <form action="{{ route('quotation-forms.destroy', $form) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Delete {{ $form->ref_no }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No quotation forms yet. Create the first one!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $forms->links() }}</div>
            </div>
        </div>
    </div>
@endsection
