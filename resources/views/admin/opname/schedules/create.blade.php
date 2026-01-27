@extends('layouts.admin')

@section('page-title', 'Create Stock Opname Schedule')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2><i class="fas fa-calendar-plus"></i> Create Stock Opname Schedule</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.opname.schedules.index') }}">Schedules</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Schedule Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.opname.schedules.store') }}" method="POST" id="scheduleForm">
                        @csrf

                        {{-- Schedule Code (Display Only) --}}
                        <div class="mb-3">
                            <label class="form-label">Schedule Code</label>
                            <input type="text" class="form-control" value="{{ $scheduleCode }}" readonly>
                            <small class="text-muted">Auto-generated code</small>
                        </div>

                        {{-- Execution Date --}}
                        <div class="mb-3">
                            <label for="execution_date" class="form-label">Execution Date <span class="text-danger">*</span></label>
                            <input type="date" name="execution_date" id="execution_date"
                                class="form-control @error('execution_date') is-invalid @enderror"
                                value="{{ old('execution_date') }}"
                                min="{{ date('Y-m-d') }}"
                                style="cursor: pointer;"
                                required>
                            @error('execution_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">When will this stock opname be executed?</small>
                        </div>

                        {{-- Assigned Users --}}
                        <div class="mb-3">
                            <label class="form-label">Assigned Users <span class="text-danger">*</span></label>
                            @error('assigned_users')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="border rounded p-3 bg-light">
                                <div class="mb-2">
                                    <input type="text" id="userSearch" class="form-control form-control-sm" placeholder="Search users...">
                                </div>
                                <div style="max-height: 200px; overflow-y: auto;">
                                    @if(count($users) > 0)
                                        @foreach($users as $user)
                                        <div class="form-check user-item">
                                            <input class="form-check-input" type="checkbox" name="assigned_users[]"
                                                value="{{ $user->id }}" id="user_{{ $user->id }}"
                                                {{ in_array($user->id, old('assigned_users', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="user_{{ $user->id }}">
                                                {{ $user->name }} - {{ $user->employee_id }}
                                            </label>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="alert alert-info small mb-0">
                                            No users available
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <small class="text-muted">Select one or more users to assign</small>
                        </div>

                        {{-- Item Type Selection --}}
                        <div class="mb-3">
                            <label class="form-label">Item Types to Include <span class="text-danger">*</span></label>
                            @error('include_spareparts')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="border rounded p-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="include_spareparts" value="1"
                                        id="include_spareparts" {{ old('include_spareparts') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="include_spareparts">
                                        <strong>Spareparts</strong> - Select by location
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="include_tools" value="1"
                                        id="include_tools" {{ old('include_tools') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="include_tools">
                                        <strong>Tools</strong> - All tools will be included
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="include_assets" value="1"
                                        id="include_assets" {{ old('include_assets') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="include_assets">
                                        <strong>Assets</strong> - Select by location
                                    </label>
                                </div>
                            </div>
                            <small class="text-muted">Select at least one item type</small>
                        </div>

                        {{-- Sparepart Locations Selection --}}
                        <div class="mb-3" id="sparepartLocationsDiv" style="display: none;">
                            <label class="form-label">Sparepart Locations <span class="text-danger">*</span></label>
                            @error('sparepart_locations')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="border rounded p-3 bg-light">
                                <div class="mb-2">
                                    <input type="text" id="sparepartLocationSearch" class="form-control form-control-sm" placeholder="Search locations...">
                                </div>
                                <div style="max-height: 200px; overflow-y: auto;">
                                    @if(count($sparepartLocations) > 0)
                                        @foreach($sparepartLocations as $location)
                                        <div class="form-check sparepart-location-item">
                                            <input class="form-check-input" type="checkbox" name="sparepart_locations[]"
                                                value="{{ $location }}" id="sparepart_loc_{{ md5($location) }}"
                                                {{ in_array($location, old('sparepart_locations', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sparepart_loc_{{ md5($location) }}">
                                                {{ $location }}
                                            </label>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="alert alert-info small mb-0">
                                            No sparepart locations available
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <small class="text-muted">Select locations for spareparts opname</small>
                        </div>

                        {{-- Asset Locations Selection --}}
                        <div class="mb-3" id="assetLocationsDiv" style="display: none;">
                            <label class="form-label">Asset Locations <span class="text-danger">*</span></label>
                            @error('asset_locations')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="border rounded p-3 bg-light">
                                <div class="mb-2">
                                    <input type="text" id="assetLocationSearch" class="form-control form-control-sm" placeholder="Search locations...">
                                </div>
                                <div style="max-height: 200px; overflow-y: auto;">
                                    @if(count($assetLocations) > 0)
                                        @foreach($assetLocations as $location)
                                        <div class="form-check asset-location-item">
                                            <input class="form-check-input" type="checkbox" name="asset_locations[]"
                                                value="{{ $location }}" id="asset_loc_{{ md5($location) }}"
                                                {{ in_array($location, old('asset_locations', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="asset_loc_{{ md5($location) }}">
                                                {{ $location }}
                                            </label>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="alert alert-info small mb-0">
                                            No asset locations available
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <small class="text-muted">Select locations for assets opname</small>
                        </div>

                        {{-- Notes --}}
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror"
                                rows="3" placeholder="Additional notes or instructions...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Schedule
                            </button>
                            <a href="{{ route('admin.opname.schedules.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Information</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">How it Works:</h6>
                    <ul class="small">
                        <li><strong>Location-Based:</strong> Select items by location instead of individual selection</li>
                        <li><strong>Manual Assignment:</strong> Choose execution date and select responsible users</li>
                        <li><strong>Collaborative:</strong> All assigned users work together - anyone can execute any item</li>
                        <li><strong>Multiple Users:</strong> Check multiple users for team counting</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold">Item Types:</h6>
                    <div class="small">
                        <ul>
                            <li><strong>Spareparts:</strong> Select by locations (e.g., 1C1-12, 1C1-8)</li>
                            <li><strong>Tools:</strong> All tools included automatically (no location filter)</li>
                            <li><strong>Assets:</strong> Select by locations (e.g., Multitier 1, Multitier 2)</li>
                        </ul>
                    </div>

                    <hr>

                    <h6 class="fw-bold">Best Practices:</h6>
                    <div class="small">
                        <ul>
                            <li>Set execution date based on availability of assigned users</li>
                            <li>Assign enough users to complete counting efficiently</li>
                            <li>Select multiple users for large locations using checkboxes</li>
                            <li>Add clear notes for special requirements or instructions</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Estimated Items Counter --}}
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-check-circle"></i> Estimated Items</h6>
                </div>
                <div class="card-body">
                    <div id="estimatedItemsInfo" class="text-center text-muted">
                        <small>Select item types and locations to see estimate</small>
                    </div>
                    <div id="estimatedItemsBreakdown" style="display: none;">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="small text-muted">Spareparts</div>
                                <div class="fw-bold" id="sparepartCount">0</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Tools</div>
                                <div class="fw-bold" id="toolCount">0</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Assets</div>
                                <div class="fw-bold" id="assetCount">0</div>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <h3 id="totalCount" class="mb-0">0</h3>
                            <small class="text-muted">total items</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Make date input clickable anywhere
    const executionDateInput = document.getElementById('execution_date');
    if (executionDateInput) {
        executionDateInput.addEventListener('click', function() {
            this.showPicker();
        });
    }

    const includeSparepartsCheckbox = document.getElementById('include_spareparts');
    const includeToolsCheckbox = document.getElementById('include_tools');
    const includeAssetsCheckbox = document.getElementById('include_assets');
    const sparepartLocationsDiv = document.getElementById('sparepartLocationsDiv');
    const assetLocationsDiv = document.getElementById('assetLocationsDiv');
    const sparepartLocationSearch = document.getElementById('sparepartLocationSearch');
    const assetLocationSearch = document.getElementById('assetLocationSearch');
    const userSearch = document.getElementById('userSearch');

    // Toggle location selections based on checkboxes
    includeSparepartsCheckbox.addEventListener('change', function() {
        sparepartLocationsDiv.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            // Uncheck all sparepart locations
            document.querySelectorAll('input[name="sparepart_locations[]"]').forEach(cb => cb.checked = false);
        }
    });

    includeAssetsCheckbox.addEventListener('change', function() {
        assetLocationsDiv.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            // Uncheck all asset locations
            document.querySelectorAll('input[name="asset_locations[]"]').forEach(cb => cb.checked = false);
        }
    });

    // Search functionality for users
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.user-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });
    }

    // Search functionality for sparepart locations
    if (sparepartLocationSearch) {
        sparepartLocationSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.sparepart-location-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });
    }

    // Search functionality for asset locations
    if (assetLocationSearch) {
        assetLocationSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.asset-location-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });
    }

    // Restore state if there are old values
    if (includeSparepartsCheckbox.checked) {
        sparepartLocationsDiv.style.display = 'block';
    }
    if (includeAssetsCheckbox.checked) {
        assetLocationsDiv.style.display = 'block';
    }
});
</script>
@endpush
@endsection
