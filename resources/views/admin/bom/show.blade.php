@extends('layouts.admin')

@section('page-title', 'BOM ' . $bom->bom_id)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-list-alt me-2"></i>BOM {{ $bom->bom_id }}</h4>
            <p class="text-muted mb-0">{{ $bom->description ?? 'No description' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route($routePrefix . '.bom-management.edit', $bom) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- BOM Items -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-th-list me-2"></i>BOM Items</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="18%">No. Material</th>
                                    <th>Material Description</th>
                                    <th width="8%" class="text-center">Qty</th>
                                    <th width="7%" class="text-center">Unit</th>
                                    <th width="15%" class="text-end">Price Unit</th>
                                    <th width="15%" class="text-end">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bom->items as $item)
                                <tr>
                                    <td>{{ $item->no }}</td>
                                    <td><code class="small">{{ $item->material_code ?? '-' }}</code></td>
                                    <td>{{ $item->material_description }}</td>
                                    <td class="text-center">{{ rtrim(rtrim(number_format($item->qty, 2), '0'), '.') }}</td>
                                    <td class="text-center">{{ $item->unit }}</td>
                                    <td class="text-end">{{ $item->formatted_price_unit }}</td>
                                    <td class="text-end">{{ $item->formatted_price }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No items</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($totalPrice > 0)
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="6" class="text-end">Total</td>
                                    <td class="text-end">Rp {{ number_format($totalPrice, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Info -->
            <div class="card shadow-sm border-info mb-3">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle me-1"></i> BOM Information
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>BOM ID:</strong>
                        <span class="float-end badge bg-primary fs-6">{{ $bom->bom_id }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Items:</strong>
                        <span class="float-end badge bg-info">{{ $bom->items->count() }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Linked Assets:</strong>
                        <span class="float-end badge {{ $bom->assets->count() > 0 ? 'bg-success' : 'bg-secondary' }}">
                            {{ $bom->assets->count() }}
                        </span>
                    </div>
                    @if($totalPrice > 0)
                    <div class="mb-2">
                        <strong>Total Price:</strong>
                        <span class="float-end text-success fw-bold">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <hr>
                    <div class="mb-1 small text-muted">
                        Created: {{ $bom->created_at->format('d M Y') }}
                        @if($bom->creator) by {{ $bom->creator->name }} @endif
                    </div>
                    <div class="small text-muted">
                        Updated: {{ $bom->updated_at->diffForHumans() }}
                    </div>
                </div>
            </div>

            <!-- Linked Assets -->
            @if($bom->assets->count() > 0)
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-microchip me-1"></i> Linked Assets ({{ $bom->assets->count() }})
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($bom->assets as $asset)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <span class="fw-semibold small">{{ $asset->equipment_id }}</span>
                                <div class="text-muted" style="font-size:12px;">{{ $asset->asset_name }}</div>
                            </div>
                            <span class="badge {{ $asset->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($asset->status) }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @else
            <div class="card shadow-sm border-secondary">
                <div class="card-body text-center text-muted py-3">
                    <i class="fas fa-microchip fa-2x mb-2 d-block"></i>
                    No assets linked to this BOM yet.
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
