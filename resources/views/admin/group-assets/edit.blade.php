@extends('layouts.admin')

@section('page-title', 'Edit Group Asset')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Edit Group Asset</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.group-assets.index') }}">Group Assets</a></li>
                <li class="breadcrumb-item active">Edit {{ $groupAsset->group_id }}</li>
            </ol>
        </nav>
    </div>

    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <h5 class="mb-0">Edit Group — {{ $groupAsset->group_id }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route($routePrefix.'.group-assets.update', $groupAsset) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Group ID --}}
                <div class="mb-3">
                    <label for="group_id" class="form-label">Group ID <span class="text-danger">*</span></label>
                    <input type="text" id="group_id" name="group_id"
                           class="form-control @error('group_id') is-invalid @enderror"
                           value="{{ old('group_id', $groupAsset->group_id) }}" required>
                    @error('group_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Group Name --}}
                <div class="mb-3">
                    <label for="group_name" class="form-label">Group Name <span class="text-danger">*</span></label>
                    <input type="text" id="group_name" name="group_name"
                           class="form-control @error('group_name') is-invalid @enderror"
                           value="{{ old('group_name', $groupAsset->group_name) }}" required>
                    @error('group_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Severity --}}
                <div class="mb-4">
                    <label for="severity" class="form-label">Severity <span class="text-danger">*</span></label>
                    <select id="severity" name="severity"
                            class="form-select @error('severity') is-invalid @enderror" required>
                        <option value="">-- Select Severity --</option>
                        @foreach($severityLabels as $value => $label)
                            <option value="{{ $value }}"
                                {{ old('severity', $groupAsset->severity) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('severity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Group</button>
                    <a href="{{ route($routePrefix.'.group-assets.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
