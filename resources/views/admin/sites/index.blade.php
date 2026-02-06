@extends('layouts.admin')

@section('page-title', 'Site Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Site Management</h2>
        <a href="{{ route('admin.sites.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Site
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Database</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sites as $site)
                            <tr>
                                <td><code>{{ $site->code }}</code></td>
                                <td>
                                    <strong>{{ $site->name }}</strong>
                                    @if($site->logo)
                                        <img src="{{ asset('storage/' . $site->logo) }}" alt="" class="ms-2" style="height: 20px;">
                                    @endif
                                </td>
                                <td><code>{{ $site->database_name }}</code></td>
                                <td>{{ Str::limit($site->description, 50) }}</td>
                                <td>
                                    @if($site->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $site->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.sites.edit', $site) }}"
                                           class="btn btn-sm btn-info"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('admin.sites.toggle-status', $site) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm {{ $site->is_active ? 'btn-warning' : 'btn-success' }}"
                                                    title="{{ $site->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas {{ $site->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.sites.migrate', $site) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Run migrations for this site?')">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm btn-secondary"
                                                    title="Run Migrations">
                                                <i class="fas fa-database"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No sites found. Create your first site to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Site Management Info</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i>
                <strong>Multi-tenancy System:</strong>
                <ul class="mb-0 mt-2">
                    <li>Each site has its own separate database</li>
                    <li>Users in each site are independent</li>
                    <li>Admin users can access all sites</li>
                    <li>Creating a new site automatically creates its database and runs migrations</li>
                    <li>Use "Run Migrations" button to update site database schema</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
