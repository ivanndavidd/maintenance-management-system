@extends('layouts.admin')

@section('page-title', 'Import Spareparts from Excel')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Import Spareparts from Excel</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.spareparts.index') }}">Spareparts</a></li>
                <li class="breadcrumb-item active">Import Excel</li>
            </ol>
        </nav>
    </div>

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
            @if(session('errors'))
                <hr>
                <strong>Errors:</strong>
                <ul class="mb-0">
                    @foreach(session('errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-upload"></i> Upload Excel File</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.spareparts.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Before uploading:</h6>
                            <ol class="mb-0">
                                <li>Download the CSV template below</li>
                                <li>Fill in your sparepart data following the template format</li>
                                <li><strong>Important:</strong> Save your Excel file as CSV format (.csv) before uploading</li>
                                <li>Upload the CSV file here</li>
                            </ol>
                        </div>

                        <div class="alert alert-warning">
                            <strong><i class="fas fa-exclamation-triangle"></i> How to convert Excel to CSV:</strong>
                            <ol class="small mb-0 mt-2">
                                <li>Open your Excel file</li>
                                <li>Click "File" → "Save As"</li>
                                <li>Choose file type: <strong>CSV (Comma delimited) (*.csv)</strong></li>
                                <li>Click Save</li>
                            </ol>
                        </div>

                        <div class="mb-4">
                            <label for="file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="file"
                                class="form-control"
                                accept=".csv"
                                required>
                            <small class="text-muted">
                                Accepted format: .csv only | Maximum size: 2MB
                            </small>
                        </div>

                        <div id="fileInfo" class="alert alert-secondary d-none">
                            <strong>Selected file:</strong> <span id="fileName"></span><br>
                            <strong>Size:</strong> <span id="fileSize"></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.spareparts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-upload"></i> Import Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Import Progress (shown during upload) -->
            <div class="card mt-3 d-none" id="progressCard">
                <div class="card-body">
                    <h6>Importing data...</h6>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                    </div>
                    <small class="text-muted">Please wait, do not close this page.</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Download Template Card -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-file-csv"></i> Download CSV Template</h5>
                </div>
                <div class="card-body">
                    <p>Download the CSV template with sample data to get started.</p>
                    <a href="{{ route('admin.spareparts.import.template') }}" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-download"></i> Download CSV Template
                    </a>
                    <small class="text-muted d-block">
                        Opens in Excel automatically. Fill data, then save as CSV.
                    </small>
                </div>
            </div>

            <!-- Instructions Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-book"></i> Instructions</h5>
                </div>
                <div class="card-body">
                    <h6>Required Columns:</h6>
                    <ul class="small">
                        <li><strong>equipment_type:</strong> electrical, mechanical, pneumatic, hydraulic, electronic, other</li>
                        <li><strong>sparepart_name:</strong> Name of the sparepart</li>
                        <li><strong>quantity:</strong> Initial stock quantity</li>
                        <li><strong>minimum_stock:</strong> Minimum stock level</li>
                        <li><strong>unit:</strong> pcs, unit, set, box, pack, kg, liter, meter</li>
                        <li><strong>parts_price:</strong> Price per unit</li>
                    </ul>

                    <h6 class="mt-3">Optional Columns:</h6>
                    <ul class="small">
                        <li><strong>brand:</strong> Brand name</li>
                        <li><strong>model:</strong> Model number</li>
                        <li><strong>vulnerability:</strong> low, medium, high, critical</li>
                        <li><strong>location:</strong> Storage location</li>
                    </ul>

                    <div class="alert alert-warning small mt-3 mb-0">
                        <strong>Note:</strong> Material codes will be auto-generated during import.
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Use the template to ensure correct format</li>
                        <li>Don't modify the header row</li>
                        <li>Remove example data before filling your own</li>
                        <li>Check for typos in required fields</li>
                        <li>Price should be numbers only (no commas or currency symbols)</li>
                        <li>For large files, import may take a few minutes</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = (file.size / 1024).toFixed(2) + ' KB';
            document.getElementById('fileInfo').classList.remove('d-none');
        } else {
            document.getElementById('fileInfo').classList.add('d-none');
        }
    });

    document.getElementById('importForm').addEventListener('submit', function(e) {
        // Show progress card
        document.getElementById('progressCard').classList.remove('d-none');
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
    });
</script>
@endpush
@endsection
