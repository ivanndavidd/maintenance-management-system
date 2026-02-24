@extends('layouts.admin')

@section('page-title', 'Group Detail')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Group Detail</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.group-assets.index') }}">Group Assets</a></li>
                <li class="breadcrumb-item active">{{ $groupAsset->group_id }}</li>
            </ol>
        </nav>
    </div>

    @php
        $badgeColor = match($groupAsset->severity) {
            'high'   => 'danger',
            'medium' => 'warning',
            'low'    => 'success',
            default  => 'secondary',
        };
    @endphp

    {{-- Group Info --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary fs-6">{{ $groupAsset->group_id }}</span>
                <h5 class="mb-0">{{ $groupAsset->group_name }}</h5>
                <span class="badge bg-{{ $badgeColor }}">{{ ucfirst($groupAsset->severity) }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route($routePrefix.'.group-assets.edit', $groupAsset) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route($routePrefix.'.group-assets.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <small class="text-muted d-block">Severity Description</small>
                    <span>{{ $groupAsset->severity_label }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Created By</small>
                    <span>{{ $groupAsset->creator?->name ?? '-' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Created At</small>
                    <span>{{ $groupAsset->created_at?->format('d M Y H:i') ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Assets in this group (placeholder for when asset-group link is implemented) --}}
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Assets in this Group</h6>
        </div>
        <div class="card-body">
            <div class="text-center text-muted py-4">
                <i class="fas fa-link fa-2x mb-2 d-block"></i>
                Asset linking to groups will be available after the assets table is updated.
            </div>
        </div>
    </div>
</div>
@endsection
