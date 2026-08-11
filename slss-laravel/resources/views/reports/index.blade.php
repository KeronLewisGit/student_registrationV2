@extends('layouts.app')

@section('title', 'Reports - SLSS')

@section('page-title', 'Reports')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection

@push('styles')
<style>
    .report-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .report-card:hover {
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .report-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: var(--primary-light);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }
    .report-card h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .report-card p {
        color: var(--text-muted, #64748b);
        font-size: 0.9rem;
        flex-grow: 1;
    }

    @media (max-width: 576px) {
        .report-card {
            padding: 1rem;
        }
        .report-card .btn {
            width: 100%;
            min-height: 44px;
        }
    }
</style>
@endpush

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Reports</h2>
    <p class="text-muted mb-0">Generate and download reports from the student database.</p>
</div>

<div class="row g-3">
    @foreach($reports as $report)
        <div class="col-md-6 col-lg-4">
            <div class="report-card">
                <div class="report-icon">
                    <i class="fas {{ $report['icon'] }}"></i>
                </div>
                <h3>{{ $report['name'] }}</h3>
                <p>{{ $report['description'] }}</p>
                <a href="{{ route('reports.show', $report['key']) }}" class="btn btn-primary mt-3">
                    <i class="fas fa-file-excel me-1"></i> Generate Report
                </a>
            </div>
        </div>
    @endforeach
</div>
@endsection
