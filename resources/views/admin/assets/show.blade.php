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

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Asset Information</h5>
            <div class="btn-group">
                <a href="{{ route($routePrefix.'.assets.edit', $asset) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route($routePrefix.'.assets.destroy', $asset) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Are you sure you want to delete this asset?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Equipment ID</th>
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
                            <th>Equipment Type</th>
                            <td><span class="badge bg-secondary">{{ $asset->equipment_type }}</span></td>
                        </tr>
                        <tr>
                            <th>Location</th>
                            <td>{{ $asset->location }}</td>
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
                    </table>
                </div>

                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Created By</th>
                            <td>{{ $asset->creator ? $asset->creator->name : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $asset->created_at ? $asset->created_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Updated By</th>
                            <td>{{ $asset->updater ? $asset->updater->name : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Updated At</th>
                            <td>{{ $asset->updated_at ? $asset->updated_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Notes</th>
                            <td>
                                @if($asset->notes)
                                    {{ $asset->notes }}
                                @else
                                    <span class="text-muted">No notes</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route($routePrefix.'.assets.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
