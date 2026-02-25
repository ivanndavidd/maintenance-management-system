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

    {{-- Assets in this group --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-sitemap me-1"></i>
                Assets in this Group
                <span class="badge bg-secondary ms-1">{{ $assets->total() }}</span>
            </h6>
        </div>
        <div class="card-body p-0">
            @if($assets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Equipment ID</th>
                                <th>Asset Name</th>
                                <th>BOM ID</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assets as $asset)
                            <tr>
                                <td>
                                    @if($asset->equipment_id)
                                        <span class="badge bg-info">{{ $asset->equipment_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $asset->asset_name }}</td>
                                <td>
                                    @if($asset->bom_id)
                                        <span class="badge bg-light text-dark border">{{ $asset->bom_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($asset->status == 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($asset->status == 'inactive')
                                        <span class="badge bg-secondary">Inactive</span>
                                    @elseif($asset->status == 'maintenance')
                                        <span class="badge bg-warning text-dark">Maintenance</span>
                                    @else
                                        <span class="badge bg-danger">Disposed</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($assets->hasPages())
                    <div class="p-3">{{ $assets->links() }}</div>
                @endif
            @else
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No assets linked to this group yet.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
