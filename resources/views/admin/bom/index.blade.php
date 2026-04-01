@extends('layouts.admin')

@section('page-title', 'BOM Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-list-alt me-2"></i>BOM Management</h4>
            <p class="text-muted mb-0">Bill of Materials — linked to assets via BOM ID</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-csv me-1"></i> Import CSV
            </button>
            <a href="{{ route($routePrefix . '.bom-management.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create BOM
            </a>
        </div>
    </div>


    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" placeholder="Search by BOM ID or description..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i> Search</button>
                    <a href="{{ route($routePrefix . '.bom-management.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>BOM ID</th>
                            <th>Description</th>
                            <th class="text-center">Items</th>
                            <th class="text-center">Linked Assets</th>
                            <th>Last Updated</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boms as $bom)
                        <tr>
                            <td>
                                <a href="{{ route($routePrefix . '.bom-management.show', $bom) }}" class="fw-bold text-decoration-none">
                                    {{ $bom->bom_id }}
                                </a>
                            </td>
                            <td class="text-muted">{{ $bom->description ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $bom->items_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $bom->assets_count > 0 ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $bom->assets_count }}
                                </span>
                            </td>
                            <td><small class="text-muted">{{ $bom->updated_at->format('d M Y') }}</small></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route($routePrefix . '.bom-management.show', $bom) }}" class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route($routePrefix . '.bom-management.edit', $bom) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route($routePrefix . '.bom-management.destroy', $bom) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete BOM {{ $bom->bom_id }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No BOMs found.
                                <a href="{{ route($routePrefix . '.bom-management.create') }}">Create one</a> or import from CSV.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($boms->hasPages())
        <div class="card-footer">
            {{ $boms->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route($routePrefix . '.bom-management.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-csv me-2"></i>Import BOM from CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        CSV columns (with header row):<br>
                        <code>bom_id, no, material_code, material_description, qty, unit, price_unit, price</code>
                    </p>
                    <div class="mb-3">
                        <label class="form-label">CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                    </div>
                    <div class="alert alert-warning py-2 small">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Existing items for imported BOM IDs will be <strong>replaced</strong>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
