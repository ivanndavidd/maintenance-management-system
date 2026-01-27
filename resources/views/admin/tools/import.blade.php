@extends('layouts.admin')

@section('page-title', 'Import Tools from CSV')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2><i class="fas fa-file-upload"></i> Import Tools from CSV</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.tools.index') }}">Tools</a></li>
                <li class="breadcrumb-item active">Import CSV</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-upload"></i> Upload CSV File</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tools.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,.txt" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum file size: 2MB. Accepted formats: CSV (.csv, .txt)</small>
                        </div>

                        <div class="alert alert-warning">
                            <strong><i class="fas fa-exclamation-triangle"></i> Important:</strong>
                            <ul class="mb-0 mt-2">
                                <li>If a Tool ID already exists, the data will be <strong>updated</strong></li>
                                <li>If a Tool ID doesn't exist, a new tool will be <strong>created</strong></li>
                                <li>Make sure your CSV file follows the correct format (see example below)</li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-file-import"></i> Import Tools
                            </button>
                            <a href="{{ route('admin.tools.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> CSV Format Instructions</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Required CSV Headers:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Column Name</th>
                                    <th>Required</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>tool_id</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                </tr>
                                <tr>
                                    <td><code>tool_name</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                </tr>
                                <tr>
                                    <td><code>material_code</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>brand</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>model</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>quantity</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>unit</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>minimum_stock</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>location</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>parts_price</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <h6 class="fw-bold">Example CSV:</h6>
                    <pre class="bg-light p-2 rounded small">tool_id,tool_name,material_code,brand,model,quantity,unit,minimum_stock,location,parts_price
T-00001,Tang Potong,T-00001,Tekiro,PL-001,10,pcs,5,WH,50000
T-00002,Obeng Plus,T-00002,Stanley,SD-002,15,pcs,5,WH,25000</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
