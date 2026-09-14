@extends('layouts.app')

@section('title', 'Financial Year Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h2"><i class="fas fa-calendar-alt me-2 text-primary"></i>Financial Year Settings</h1>
            <p class="text-muted mb-0">Document numbering is scoped to the active financial year.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('financial-years.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Add Financial Year
            </a>
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if (session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Label</th>
                        <th>Short Code</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($years as $fy)
                        <tr class="{{ $fy->is_active ? 'table-success' : '' }}">
                            <td class="fw-bold">{{ $fy->fy_label }}</td>
                            <td><code>{{ $fy->fy_short_code }}</code></td>
                            <td>{{ $fy->start_date->format('d M Y') }}</td>
                            <td>{{ $fy->end_date->format('d M Y') }}</td>
                            <td>
                                @if ($fy->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @unless ($fy->is_active)
                                    <form action="{{ route('financial-years.activate', $fy) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Activate FY {{ $fy->fy_label }}? All other years will be deactivated and serial counters will restart for this year.');">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i> Set Active</button>
                                    </form>
                                @endunless
                                <a href="{{ route('financial-years.edit', $fy) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('financial-years.destroy', $fy) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Delete FY {{ $fy->fy_label }}?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No financial years yet. Add one to start numbering documents.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
