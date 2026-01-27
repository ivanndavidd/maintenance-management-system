@extends('layouts.admin')

@section('page-title', 'Edit Stock Opname Schedule')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2><i class="fas fa-calendar-edit"></i> Edit Stock Opname Schedule</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.opname.schedules.index') }}">Schedules</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.opname.schedules.show', $schedule) }}">{{ $schedule->schedule_code }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
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
                    <form action="{{ route('admin.opname.schedules.update', $schedule) }}" method="POST" id="scheduleForm">
                        @csrf
                        @method('PUT')

                        {{-- Schedule Code (Display Only) --}}
                        <div class="mb-3">
                            <label class="form-label">Schedule Code</label>
                            <input type="text" class="form-control" value="{{ $schedule->schedule_code }}" readonly>
                            <small class="text-muted">Auto-generated code</small>
                        </div>

                        {{-- Execution Date --}}
                        <div class="mb-3">
                            <label for="execution_date" class="form-label">Execution Date <span class="text-danger">*</span></label>
                            <input type="date" name="execution_date" id="execution_date"
                                class="form-control @error('execution_date') is-invalid @enderror"
                                value="{{ old('execution_date', $schedule->execution_date->format('Y-m-d')) }}"
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
                                                {{ in_array($user->id, old('assigned_users', $assignedUserIds)) ? 'checked' : '' }}>
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
                                        id="include_spareparts" {{ old('include_spareparts', $schedule->include_spareparts) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="include_spareparts">
                                        <strong>Spareparts</strong> - Select by location
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="include_tools" value="1"
                                        id="include_tools" {{ old('include_tools', $schedule->include_tools) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="include_tools">
                                        <strong>Tools</strong> - All tools will be included
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="include_assets" value="1"
                                        id="include_assets" {{ old('include_assets', $schedule->include_assets) ? 'checked' : '' }}>
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
                                                {{ in_array($location, old('sparepart_locations', $schedule->sparepart_locations ?? [])) ? 'checked' : '' }}>
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
                                                {{ in_array($location, old('asset_locations', $schedule->asset_locations ?? [])) ? 'checked' : '' }}>
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

                        {{-- Status --}}
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $schedule->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="in_progress" {{ old('status', $schedule->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ old('status', $schedule->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $schedule->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror"
                                rows="3" placeholder="Additional notes or instructions...">{{ old('notes', $schedule->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Schedule
                            </button>
                            <a href="{{ route('admin.opname.schedules.show', $schedule) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Important Notes</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning small mb-3">
                        <strong>Warning:</strong> Changing item types or locations will regenerate all schedule items. Any existing execution data will be preserved but items may be re-created.
                    </div>

                    <h6 class="fw-bold">Current Schedule Info:</h6>
                    <ul class="small">
                        <li><strong>Total Items:</strong> {{ $schedule->total_items }}</li>
                        <li><strong>Completed:</strong> {{ $schedule->completed_items }}</li>
                        <li><strong>Pending:</strong> {{ $schedule->total_items - $schedule->completed_items - $schedule->cancelled_items }}</li>
                        <li><strong>Progress:</strong> {{ $schedule->getProgressPercentage() }}%</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Edit Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <ul>
                            <li>You can update execution date, assigned users, and item selections</li>
                            <li>Use checkboxes to select multiple users for collaborative counting</li>
                            <li>Changing item types will regenerate schedule items</li>
                            <li>Completed items will not be affected by regeneration</li>
                        </ul>
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

    // Restore state if there are old values or existing data
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
