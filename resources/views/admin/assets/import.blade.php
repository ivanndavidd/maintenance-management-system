@extends('layouts.admin')

@section('page-title', 'Import Assets from CSV')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Import Assets from CSV</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.assets.index') }}">Assets</a></li>
                <li class="breadcrumb-item active">Import CSV</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-upload"></i> Upload CSV File</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route($routePrefix.'.assets.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> CSV Format</h6>
                            <p class="mb-1">The CSV file must have a header row with these columns:</p>
                            <code>EquipmentID,AssetName,BOMID,GroupID</code>
                            <ul class="small mb-0 mt-2">
                                <li><strong>EquipmentID</strong> — Equipment identifier (optional, used for upsert)</li>
                                <li><strong>AssetName</strong> — Asset name (required)</li>
                                <li><strong>BOMID</strong> — BOM reference ID (optional)</li>
                                <li><strong>GroupID</strong> — Group ID matching group_assets table (optional, e.g. G01)</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label for="csv_file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="csv_file" id="csv_file"
                                class="form-control @error('csv_file') is-invalid @enderror"
                                accept=".csv,.txt"
                                required>
                            <small class="text-muted">Accepted format: .csv | Maximum size: 10MB</small>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="fileInfo" class="alert alert-secondary d-none">
                            <strong>Selected file:</strong> <span id="fileName"></span> &nbsp;
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
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Notes</h5>
                </div>
                <div class="card-body small">
                    <ul class="mb-0">
                        <li>First row must be the header row</li>
                        <li>If <strong>EquipmentID</strong> already exists, the asset will be updated (upsert)</li>
                        <li>If <strong>EquipmentID</strong> is empty, a new asset is always inserted</li>
                        <li><strong>GroupID</strong> must match an existing group (e.g. G01, G08)</li>
                        <li>Empty AssetName rows are skipped automatically</li>
                        <li>Status defaults to <em>active</em> for new assets</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('csv_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = (file.size / 1024).toFixed(2) + ' KB';
            document.getElementById('fileInfo').classList.remove('d-none');
        } else {
            document.getElementById('fileInfo').classList.add('d-none');
        }
    });

    document.getElementById('importForm').addEventListener('submit', function() {
        document.getElementById('progressCard').classList.remove('d-none');
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
    });
</script>
@endpush
@endsection
