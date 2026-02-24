@extends('layouts.admin')

@section('page-title', 'Group Assets')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Group Assets</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Group Assets</li>
            </ol>
        </nav>
    </div>

    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Import Warnings:</strong>
            <ul class="mb-0 mt-1">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Groups <span class="text-muted fw-normal fs-6">({{ $groups->count() }} total)</span></h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-file-csv me-1"></i> Import CSV
                </button>
                <a href="{{ route($routePrefix.'.group-assets.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Group
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($groups->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-layer-group fa-3x mb-3 d-block"></i>
                    No groups found.
                    <div class="mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                            Import CSV
                        </button>
                        or
                        <a href="{{ route($routePrefix.'.group-assets.create') }}" class="ms-2">create one manually</a>.
                    </div>
                </div>
            @else
                @php
                    $severityConfig = [
                        'high'   => ['label' => 'High',   'desc' => 'No Backup / Main Line',                          'color' => 'danger',  'bg' => '#fff5f5', 'border' => '#f5c6cb'],
                        'medium' => ['label' => 'Medium', 'desc' => 'Bypass still possible, need manpower',           'color' => 'warning', 'bg' => '#fffdf0', 'border' => '#ffeeba'],
                        'low'    => ['label' => 'Low',    'desc' => 'Bypass still possible, don\'t need manpower',    'color' => 'success', 'bg' => '#f0fff4', 'border' => '#c3e6cb'],
                    ];
                    $grouped = $groups->groupBy('severity');
                @endphp

                @foreach($severityConfig as $severityKey => $config)
                    @if($grouped->has($severityKey))
                        <div class="severity-section mb-4">
                            {{-- Section Header --}}
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom: 2px solid {{ $config['border'] }};">
                                <span class="badge bg-{{ $config['color'] }} fs-6 px-3 py-2">{{ $config['label'] }}</span>
                                <span class="text-muted" style="font-size:13px;">{{ $config['desc'] }}</span>
                                <span class="ms-auto text-muted" style="font-size:12px;">{{ $grouped[$severityKey]->count() }} groups</span>
                            </div>

                            {{-- Cards --}}
                            <div class="row g-3">
                                @foreach($grouped[$severityKey] as $group)
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                        <div class="card h-100 border shadow-sm group-card"
                                             style="cursor:pointer; border-color:{{ $config['border'] }} !important;"
                                             onclick="window.location='{{ route($routePrefix.'.group-assets.show', $group) }}'">
                                            <div class="card-body" style="background:{{ $config['bg'] }}; border-radius: calc(0.375rem - 1px) calc(0.375rem - 1px) 0 0;">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="badge bg-secondary">{{ $group->group_id }}</span>
                                                    <span class="badge bg-{{ $config['color'] }}">{{ $config['label'] }}</span>
                                                </div>
                                                <h6 class="card-title fw-semibold mb-1">{{ $group->group_name }}</h6>
                                                <small class="text-muted">
                                                    @if($group->creator)
                                                        Created by {{ $group->creator->name }}
                                                    @endif
                                                </small>
                                            </div>
                                            <div class="card-footer bg-transparent d-flex gap-2 justify-content-end" onclick="event.stopPropagation()">
                                                <a href="{{ route($routePrefix.'.group-assets.edit', $group) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form action="{{ route($routePrefix.'.group-assets.destroy', $group) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Delete group {{ $group->group_id }} - {{ $group->group_name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</div>

{{-- Import CSV Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route($routePrefix.'.group-assets.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel"><i class="fas fa-file-csv me-2"></i>Import Group Assets CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 mb-3" style="font-size:13px;">
                        <strong>Format CSV yang diperlukan:</strong><br>
                        Kolom: <code>GroupID, GroupName, Severity</code><br>
                        Severity: <code>high</code>, <code>medium</code>, atau <code>low</code><br>
                        Baris pertama dianggap sebagai header dan akan dilewati.
                    </div>
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Pilih File CSV <span class="text-danger">*</span></label>
                        <input type="file" id="csv_file" name="csv_file"
                               class="form-control @error('csv_file') is-invalid @enderror"
                               accept=".csv,.txt" required>
                        @error('csv_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Format: .csv — Maks. 5MB</div>
                    </div>
                    <div class="alert alert-warning py-2 px-3" style="font-size:13px;">
                        <i class="fas fa-info-circle me-1"></i>
                        Jika GroupID sudah ada, data akan di-update (bukan duplikat).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.group-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12) !important;
    transform: translateY(-2px);
    transition: all 0.2s ease;
}
.group-card {
    transition: all 0.2s ease;
}
</style>
@endsection
