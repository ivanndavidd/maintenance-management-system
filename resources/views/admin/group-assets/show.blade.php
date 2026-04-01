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
            <small class="text-muted"><i class="fas fa-pencil-alt me-1"></i>Click badge to edit BOM ID &nbsp;|&nbsp; <i class="fas fa-external-link-alt me-1"></i>Link icon to view BOM details</small>
        </div>
        <div class="card-body p-0">
            @if($assets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:130px">Equipment ID</th>
                                <th>Asset Name</th>
                                <th style="width:160px">BOM ID</th>
                                <th class="text-center" style="width:100px">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assets as $asset)
                            @php
                                $bomShowUrl = $asset->bomRecord
                                    ? route($routePrefix.'.bom-management.show', $asset->bomRecord)
                                    : null;
                            @endphp
                            <tr>
                                <td>
                                    @if($asset->equipment_id)
                                        <span class="badge bg-info">{{ $asset->equipment_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $asset->asset_name }}</td>
                                <td class="bom-cell"
                                    data-asset-id="{{ $asset->id }}"
                                    data-bom="{{ $asset->bom_id ?? '' }}"
                                    data-url="{{ route($routePrefix.'.assets.update-bom', $asset) }}"
                                    data-bom-index-url="{{ route($routePrefix.'.bom-management.index') }}"
                                    style="vertical-align:middle;">
                                    <span class="bom-display d-flex align-items-center gap-1">
                                        @if($asset->bom_id)
                                            <span class="badge bg-light text-dark border bom-badge" style="cursor:pointer;" title="Click to edit BOM ID">{{ $asset->bom_id }}</span>
                                            @if($bomShowUrl)
                                                <a href="{{ $bomShowUrl }}" class="bom-link text-primary" title="View BOM details" style="line-height:1;">
                                                    <i class="fas fa-external-link-alt" style="font-size:11px;"></i>
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted fst-italic bom-badge" style="font-size:12px;cursor:pointer;" title="Click to add BOM ID">click to add</span>
                                        @endif
                                    </span>
                                    <span class="bom-edit d-none">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm bom-input"
                                                   value="{{ $asset->bom_id ?? '' }}"
                                                   placeholder="e.g. R04"
                                                   maxlength="20"
                                                   style="width:80px">
                                            <button class="btn btn-success btn-sm bom-save" type="button"><i class="fas fa-check"></i></button>
                                            <button class="btn btn-outline-secondary btn-sm bom-cancel" type="button"><i class="fas fa-times"></i></button>
                                        </div>
                                    </span>
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

@push('scripts')
<script>
const bomIndexUrl = {{ Js::from(route($routePrefix.'.bom-management.index')) }};

document.querySelectorAll('.bom-cell').forEach(function(cell) {
    const display = cell.querySelector('.bom-display');
    const edit    = cell.querySelector('.bom-edit');
    const input   = cell.querySelector('.bom-input');
    const saveBtn = cell.querySelector('.bom-save');
    const cancelBtn = cell.querySelector('.bom-cancel');

    function openEdit() {
        display.classList.add('d-none');
        edit.classList.remove('d-none');
        input.focus();
        input.select();
    }

    function closeEdit() {
        edit.classList.add('d-none');
        display.classList.remove('d-none');
    }

    function saveBom() {
        const newVal = input.value.trim().toUpperCase();
        const url    = cell.dataset.url;

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ bom_id: newVal }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const bomId = data.bom_id;
                input.value = bomId ?? '';
                cell.dataset.bom = bomId ?? '';

                if (bomId) {
                    // Show badge + link icon (link goes to BOM index filtered, or just index)
                    display.innerHTML =
                        `<span class="badge bg-light text-dark border bom-badge" style="cursor:pointer;" title="Click to edit BOM ID">${bomId}</span>` +
                        `<a href="${bomIndexUrl}?search=${encodeURIComponent(bomId)}" class="bom-link text-primary ms-1" title="View BOM details" style="line-height:1;" target="_blank">` +
                        `<i class="fas fa-external-link-alt" style="font-size:11px;"></i></a>`;
                } else {
                    display.innerHTML = `<span class="text-muted fst-italic bom-badge" style="font-size:12px;cursor:pointer;" title="Click to add BOM ID">click to add</span>`;
                }

                // Re-bind click on new badge
                const newBadge = display.querySelector('.bom-badge');
                if (newBadge) newBadge.addEventListener('click', openEdit);
            }
            closeEdit();
        })
        .catch(() => closeEdit())
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-check"></i>';
        });
    }

    // Only open edit when clicking the badge, not the link icon
    const badge = display.querySelector('.bom-badge');
    if (badge) badge.addEventListener('click', openEdit);

    saveBtn.addEventListener('click', saveBom);
    cancelBtn.addEventListener('click', closeEdit);

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') saveBom();
        if (e.key === 'Escape') closeEdit();
    });
});
</script>
@endpush
</div>
@endsection
