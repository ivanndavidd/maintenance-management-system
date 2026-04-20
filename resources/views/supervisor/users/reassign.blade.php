@extends('layouts.admin')

@section('page-title', 'Reassign User Data')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4 gap-2">
        <a href="{{ route('supervisor.users.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h4 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Reassign & Delete User</h4>
    </div>

    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        User <strong>{{ $user->name }}</strong> has linked data. Reassign all records to another user before deleting.
    </div>

    {{-- Linked data summary --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Linked Data Summary</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Data Type</th>
                        <th class="text-center">Count</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>CM Reports (submitted by)</td>
                        <td class="text-center">
                            <span class="badge {{ $linked['cm_reports'] > 0 ? 'bg-danger' : 'bg-secondary' }}">
                                {{ $linked['cm_reports'] }}
                            </span>
                        </td>
                        <td><small class="text-muted">Will be reassigned to selected user</small></td>
                    </tr>
                    <tr>
                        <td>CM Requests (assigned to)</td>
                        <td class="text-center">
                            <span class="badge {{ $linked['cm_requests'] > 0 ? 'bg-danger' : 'bg-secondary' }}">
                                {{ $linked['cm_requests'] }}
                            </span>
                        </td>
                        <td><small class="text-muted">Will be reassigned to selected user</small></td>
                    </tr>
                    <tr>
                        <td>PM Tasks</td>
                        <td class="text-center">
                            <span class="badge {{ $linked['pm_tasks'] > 0 ? 'bg-danger' : 'bg-secondary' }}">
                                {{ $linked['pm_tasks'] }}
                            </span>
                        </td>
                        <td><small class="text-muted">Will be reassigned to selected user</small></td>
                    </tr>
                    <tr>
                        <td>Sparepart Usages</td>
                        <td class="text-center">
                            <span class="badge {{ $linked['sparepart_usages'] > 0 ? 'bg-danger' : 'bg-secondary' }}">
                                {{ $linked['sparepart_usages'] }}
                            </span>
                        </td>
                        <td><small class="text-muted">Will be reassigned to selected user</small></td>
                    </tr>
                    <tr>
                        <td>Shift Assignments</td>
                        <td class="text-center">
                            <span class="badge {{ $linked['shift_assignments'] > 0 ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                {{ $linked['shift_assignments'] }}
                            </span>
                        </td>
                        <td><small class="text-muted">Will be deleted (reassign via shift schedule)</small></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Reassign form --}}
    <div class="card">
        <div class="card-header fw-bold">Select Replacement User</div>
        <div class="card-body">
            <form action="{{ route('supervisor.users.reassign', $user) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reassign all data to <span class="text-danger">*</span></label>
                    <select name="reassign_to" class="form-select" required>
                        <option value="">-- Select User --</option>
                        @foreach($candidates as $candidate)
                            <option value="{{ $candidate->id }}">
                                {{ $candidate->name }}
                                ({{ $candidate->roles->first()?->name ?? 'no role' }})
                            </option>
                        @endforeach
                    </select>
                    @error('reassign_to')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Reassign all data from {{ $user->name }} and permanently delete this user?')">
                        <i class="fas fa-exchange-alt me-1"></i> Reassign & Delete User
                    </button>
                    <a href="{{ route('supervisor.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
