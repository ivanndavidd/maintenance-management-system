@extends('layouts.admin')

@section('page-title', 'Import Assets from Excel')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Import Assets from Excel</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.assets.index') }}">Assets</a></li>
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
                    <form action="{{ route($routePrefix.'.assets.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Before uploading:</h6>
                            <ol class="mb-0">
                                <li>Download the Excel template below</li>
                                <li>Fill in your asset data following the template format</li>
                                <li><strong>Important:</strong> Each sheet represents an equipment type</li>
                                <li>Cell A1 should contain the location</li>
                                <li>Data starts from row 2 (row 1 is for headers)</li>
                                <li>Upload the Excel file here</li>
                            </ol>
                        </div>

                        <div class="alert alert-warning">
                            <strong><i class="fas fa-exclamation-triangle"></i> Excel File Structure:</strong>
                            <ul class="small mb-0 mt-2">
                                <li><strong>Sheet Name:</strong> Equipment Type (e.g., "Compressor", "Conveyor")</li>
                                <li><strong>Cell A1:</strong> Location (e.g., "Warehouse A")</li>
                                <li><strong>Column B (Row 2+):</strong> Equipment ID</li>
                                <li><strong>Column C (Row 2+):</strong> Description (Asset Name)</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label for="excel_file" class="form-label">Select Excel File <span class="text-danger">*</span></label>
                            <input type="file" name="excel_file" id="excel_file"
                                class="form-control @error('excel_file') is-invalid @enderror"
                                accept=".xlsx,.xls"
                                required>
                            <small class="text-muted">
                                Accepted formats: .xlsx, .xls | Maximum size: 10MB
                            </small>
                            @error('excel_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="fileInfo" class="alert alert-secondary d-none">
                            <strong>Selected file:</strong> <span id="fileName"></span><br>
                            <strong>Size:</strong> <span id="fileSize"></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route($routePrefix.'.assets.index') }}" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="fas fa-file-excel"></i> Download Excel Template</h5>
                </div>
                <div class="card-body">
                    <p>Download the Excel template with sample data to get started.</p>
                    <a href="{{ route($routePrefix.'.assets.import.template') }}" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-download"></i> Download Excel Template
                    </a>
                    <small class="text-muted d-block">
                        Template includes multiple sheets with example data.
                    </small>
                </div>
            </div>

            <!-- Instructions Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-book"></i> Instructions</h5>
                </div>
                <div class="card-body">
                    <h6>Excel File Structure:</h6>
                    <ul class="small">
                        <li><strong>Sheet Name:</strong> Equipment Type (e.g., "Compressor")</li>
                        <li><strong>Cell A1:</strong> Location (e.g., "Warehouse A")</li>
                        <li><strong>Row 1:</strong> Headers (Location, Equipment, Description)</li>
                        <li><strong>Row 2+:</strong> Data rows</li>
                    </ul>

                    <h6 class="mt-3">Data Columns (from Row 2):</h6>
                    <ul class="small">
                        <li><strong>Column A:</strong> Location (merged cells)</li>
                        <li><strong>Column B:</strong> Equipment ID (optional)</li>
                        <li><strong>Column C:</strong> Description/Asset Name (required)</li>
                    </ul>

                    <div class="alert alert-warning small mt-3 mb-0">
                        <strong>Note:</strong> Asset IDs will be auto-generated with format: AST + YYYYMMDD + XXX
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
                        <li>Use separate sheets for different equipment types</li>
                        <li>Sheet name will be used as Equipment Type</li>
                        <li>Location in cell A1 applies to all items in that sheet</li>
                        <li>Equipment ID is optional but recommended</li>
                        <li>Asset Name (Description) is required</li>
                        <li>Empty rows will be skipped automatically</li>
                        <li>For large files, import may take a few minutes</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('excel_file').addEventListener('change', function(e) {
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
