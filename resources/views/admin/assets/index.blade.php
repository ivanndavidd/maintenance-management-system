@extends('layouts.admin')

@section('page-title', 'Assets Management')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Assets Management</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Assets</li>
            </ol>
        </nav>
    </div>

    @if(session('import_errors'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Import Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Master Data Assets</h5>
            <div class="btn-group">
                <a href="{{ route($routePrefix.'.assets.import') }}" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Import Excel
                </a>
                <a href="{{ route($routePrefix.'.assets.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Asset
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route($routePrefix.'.assets.index') }}">
                <div class="row mb-3 g-2">
                    <div class="col-12 col-md-6">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search Equipment ID, Name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="disposed" {{ request('status') == 'disposed' ? 'selected' : '' }}>Disposed</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            @if(request('search') || request('status'))
                <div class="mb-3">
                    <small class="text-muted">Active filters:</small>
                    <div class="d-inline-flex gap-2 ms-2 flex-wrap">
                        @if(request('search'))
                            <span class="badge bg-info">Search: {{ request('search') }} <a href="{{ route($routePrefix.'.assets.index', array_filter(request()->except('search'))) }}" class="text-white text-decoration-none ms-1">x</a></span>
                        @endif
                        @if(request('status'))
                            <span class="badge bg-info">Status: {{ ucfirst(request('status')) }} <a href="{{ route($routePrefix.'.assets.index', array_filter(request()->except('status'))) }}" class="text-white text-decoration-none ms-1">x</a></span>
                        @endif
                        <a href="{{ route($routePrefix.'.assets.index') }}" class="badge bg-secondary text-decoration-none">Clear All</a>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Equipment ID</th>
                            <th>Asset Name</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                        <tr>
                            <td><span class="badge bg-info">{{ $asset->equipment_id ?? '-' }}</span></td>
                            <td>{{ $asset->asset_name }}</td>
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
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route($routePrefix.'.assets.show', $asset) }}" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route($routePrefix.'.assets.edit', $asset) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route($routePrefix.'.assets.destroy', $asset) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this asset?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center">No assets found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $assets->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>
@endsection
