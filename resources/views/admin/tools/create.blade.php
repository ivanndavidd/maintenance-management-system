@extends('layouts.admin')

@section('page-title', 'Add New Tool')

@section('content')
<div class="container-fluid">
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
            @if(session('import_errors') && is_array(session('import_errors')))
                <ul class="mb-0 mt-2">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="mb-4">
        <h2>Add New Tool</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.tools.index') }}">Tools</a></li>
                <li class="breadcrumb-item active">Add New</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-plus"></i> Tool Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route($routePrefix.'.tools.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="equipment_type" class="form-label">Equipment Type</label>
                                <input type="text" name="equipment_type" id="equipment_type"
                                    class="form-control @error('equipment_type') is-invalid @enderror"
                                    value="{{ old('equipment_type', 'Tools') }}"
                                    placeholder="e.g., Tools, Hand Tools, Power Tools">
                                <small class="text-muted">Default: Tools</small>
                                @error('equipment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="sparepart_name" class="form-label">Tool Name <span class="text-danger">*</span></label>
                                <input type="text" name="sparepart_name" id="sparepart_name"
                                    class="form-control @error('sparepart_name') is-invalid @enderror"
                                    value="{{ old('sparepart_name') }}" required>
                                @error('sparepart_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" name="brand" id="brand"
                                    class="form-control @error('brand') is-invalid @enderror"
                                    value="{{ old('brand') }}">
                                @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" name="model" id="model"
                                    class="form-control @error('model') is-invalid @enderror"
                                    value="{{ old('model') }}">
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity"
                                    class="form-control @error('quantity') is-invalid @enderror"
                                    value="{{ old('quantity', 0) }}" min="0" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                <select name="unit" id="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                    <option value="">Select Unit</option>
                                    <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>Pcs</option>
                                    <option value="unit" {{ old('unit') == 'unit' ? 'selected' : '' }}>Unit</option>
                                    <option value="set" {{ old('unit') == 'set' ? 'selected' : '' }}>Set</option>
                                    <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box</option>
                                    <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>Pack</option>
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="minimum_stock" class="form-label">Minimum Stock <span class="text-danger">*</span></label>
                                <input type="number" name="minimum_stock" id="minimum_stock"
                                    class="form-control @error('minimum_stock') is-invalid @enderror"
                                    value="{{ old('minimum_stock', 1) }}" min="0" required>
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="parts_price" class="form-label">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="parts_price" id="parts_price"
                                        class="form-control @error('parts_price') is-invalid @enderror"
                                        value="{{ old('parts_price', 0) }}" min="0" step="0.01" required>
                                </div>
                                @error('parts_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="vulnerability" class="form-label">Vulnerability</label>
                                <select name="vulnerability" id="vulnerability" class="form-select @error('vulnerability') is-invalid @enderror">
                                    <option value="">Select Vulnerability</option>
                                    <option value="low" {{ old('vulnerability') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('vulnerability') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('vulnerability') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="critical" {{ old('vulnerability') == 'critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                                @error('vulnerability')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" name="location" id="location"
                                class="form-control @error('location') is-invalid @enderror"
                                value="{{ old('location') }}" placeholder="e.g., Warehouse A, Shelf 1">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="material_code" class="form-label">Material Code (Optional)</label>
                            <input type="text" name="material_code" id="material_code"
                                class="form-control @error('material_code') is-invalid @enderror"
                                value="{{ old('material_code') }}" placeholder="External material code from import">
                            <small class="text-muted">Leave empty if not applicable</small>
                            @error('material_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route($routePrefix.'.tools.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Tool
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tool ID:</strong> Auto-generated (TLS20251218001)</p>
                    <p><strong>Item Type:</strong> Automatically set to "tool"</p>
                    <p><strong>Added By:</strong> {{ auth()->user()->name }}</p>
                    <hr>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        Tool ID will be automatically generated when you save this tool.
                    </p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Tips</h5>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-info-circle text-info"></i> Quick Tips:</h6>
                    <ul class="small">
                        <li>Tool ID format: TLS + YYYYMMDD + 001</li>
                        <li>Set minimum stock to trigger low stock alerts</li>
                        <li>Use clear storage locations for easy finding</li>
                        <li>Material code is optional for tools</li>
                    </ul>

                    <hr>

                    <h6><i class="fas fa-file-csv text-success"></i> Bulk Import:</h6>
                    <p class="small">Need to import many tools at once?</p>
                    <button type="button" class="btn btn-success btn-sm w-100" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-upload"></i> Import from CSV
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import CSV Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="fas fa-file-csv"></i> Import Tools from CSV
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route($routePrefix.'.tools.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Important:</strong>
                        <ul class="mb-0 mt-2">
                            <li>File must be in CSV format (.csv)</li>
                            <li>Maximum file size: 2MB</li>
                            <li>Tool ID will be auto-generated with format: <strong>TLS + YYYYMMDD + XXX</strong></li>
                            <li>First row must contain column headers</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="csv_file" name="file" accept=".csv" required>
                        <small class="text-muted">Only .csv files are accepted</small>
                    </div>

                    <hr>

                    <h6><i class="fas fa-table"></i> CSV Format Requirements:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Column Name</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                    <th>Example</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>equipment_type</code></td>
                                    <td><span class="badge bg-warning">Optional</span></td>
                                    <td>Type of equipment</td>
                                    <td>Tools, Hand Tools</td>
                                </tr>
                                <tr>
                                    <td><code>tool_name</code></td>
                                    <td><span class="badge bg-danger">Required</span></td>
                                    <td>Name of the tool</td>
                                    <td>Tang Snap Ring</td>
                                </tr>
                                <tr>
                                    <td><code>brand</code></td>
                                    <td><span class="badge bg-warning">Optional</span></td>
                                    <td>Brand name</td>
                                    <td>Stanley, Bosch</td>
                                </tr>
                                <tr>
                                    <td><code>model</code></td>
                                    <td><span class="badge bg-warning">Optional</span></td>
                                    <td>Model number</td>
                                    <td>XYZ-123</td>
                                </tr>
                                <tr>
                                    <td><code>quantity</code></td>
                                    <td><span class="badge bg-danger">Required</span></td>
                                    <td>Current stock quantity</td>
                                    <td>10</td>
                                </tr>
                                <tr>
                                    <td><code>unit</code></td>
                                    <td><span class="badge bg-danger">Required</span></td>
                                    <td>Unit of measure</td>
                                    <td>pcs, unit, set, box, pack</td>
                                </tr>
                                <tr>
                                    <td><code>minimum_stock</code></td>
                                    <td><span class="badge bg-danger">Required</span></td>
                                    <td>Minimum stock level</td>
                                    <td>5</td>
                                </tr>
                                <tr>
                                    <td><code>location</code></td>
                                    <td><span class="badge bg-warning">Optional</span></td>
                                    <td>Storage location</td>
                                    <td>Warehouse A - Rack 1</td>
                                </tr>
                                <tr>
                                    <td><code>parts_price</code></td>
                                    <td><span class="badge bg-danger">Required</span></td>
                                    <td>Price per unit (Rp)</td>
                                    <td>50000</td>
                                </tr>
                                <tr>
                                    <td><code>material_code</code></td>
                                    <td><span class="badge bg-warning">Optional</span></td>
                                    <td>External material code</td>
                                    <td>MAT-001</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-download"></i> <strong>Example CSV Format:</strong>
                        <pre class="mb-0 mt-2" style="font-size: 10px;">Equipment Type,Material Code,Sparepart Name,Brand,Model,Quantity,Unit,Vulnerability,Location,Parts Price,Minimum,Item Type,Path
Tools,T-00001,Kunci L Pas,Tekiro,Tekiro Hex,1,Pcs,Low,WH,,1,Item,sites/SCM
Tools,T-00002,Meteran,Tekiro,Tekiro Meter,1,Pcs,Low,WH,,1,Item,sites/SCM
Tools,T-00003,Tang Snap,Tekiro,Tekiro Snap,1,Pcs,Low,WH,,1,Item,sites/SCM</pre>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="downloadTemplate()">
                            <i class="fas fa-download"></i> Download CSV Template
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="importBtn">
                        <i class="fas fa-upload"></i> Import Tools
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Show loading indicator when form is submitted
    document.getElementById('importForm').addEventListener('submit', function() {
        const btn = document.getElementById('importBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
        btn.disabled = true;
    });

    // Download CSV template
    function downloadTemplate() {
        const csvContent = `Equipment Type,Material Code,Sparepart Name,Brand,Model,Quantity,Unit,Vulnerability,Location,Parts Price,Minimum,Item Type,Path
Tools,T-00001,Kunci L Pas,Tekiro,Tekiro Hex,1,Pcs,Low,WH,,1,Item,sites/SCM
Tools,T-00002,Meteran,Tekiro,Tekiro Meter,1,Pcs,Low,WH,,1,Item,sites/SCM
Tools,T-00003,Tang Snap,Tekiro,Tekiro Snap,1,Pcs,Low,WH,,1,Item,sites/SCM`;

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        link.setAttribute('href', url);
        link.setAttribute('download', 'tools_import_template_' + new Date().toISOString().slice(0,10) + '.csv');
        link.style.visibility = 'hidden';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endpush

@endsection
