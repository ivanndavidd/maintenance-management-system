@extends('layouts.admin')

@section('page-title', 'Asset Details')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Asset Details</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.assets.index') }}">Assets</a></li>
                <li class="breadcrumb-item active">{{ $asset->asset_name }}</li>
            </ol>
        </nav>
    </div>

    <div class="card" style="max-width:700px;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Asset Information</h5>
            <div class="d-flex gap-2">
                <a href="{{ route($routePrefix.'.assets.edit', $asset) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                <form action="{{ route($routePrefix.'.assets.destroy', $asset) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Are you sure you want to delete this asset?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="35%">Equipment ID</th>
                    <td>
                        @if($asset->equipment_id)
                            <span class="badge bg-info">{{ $asset->equipment_id }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Asset Name</th>
                    <td>{{ $asset->asset_name }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
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
                <tr>
                    <th>Notes</th>
                    <td>{{ $asset->notes ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Created By</th>
                    <td>{{ $asset->creator?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>{{ $asset->created_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Updated By</th>
                    <td>{{ $asset->updater?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Updated At</th>
                    <td>{{ $asset->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
            </table>

            <a href="{{ route($routePrefix.'.assets.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>
@endsection
