@extends('layouts.admin')

@section('page-title', 'Maintenance Jobs')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Maintenance Jobs</h5>
            <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Create New Job
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Job Code</th>
                            <th>Title</th>
                            <th>Equipment</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Scheduled</th>
                            <th>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jobs as $job))
                            <tr>
                                <td>{{ $job->job_code }}</td>
                                <td>{{ Str::limit($job->title, 30) }}</td>
                                <td>{{ $job->machine->name }}</td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($job->type) }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{
                                            $job->priority == 'urgent'
                                                ? 'danger'
                                                : ($job->priority == 'high'
                                                    ? 'warning'
                                                    : ($job->priority == 'medium'
                                                        ? 'primary'
                                                        : 'secondary'))
                                        }}"
                                    >
                                        {{ ucfirst($job->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{
                                            $job->status == 'completed'
                                                ? 'success'
                                                : ($job->status == 'in_progress'
                                                    ? 'primary'
                                                    : ($job->status == 'cancelled'
                                                        ? 'dark'
                                                        : 'secondary'))
                                        }}"
                                    >
                                        {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                    </span>
                                </td>
                                <td>{{ $job->scheduled_date->format('M d, Y') }}</td>
                                <td>{{ $job->assignedUser->name ?? 'Unassigned' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a
                                            href="{{ route('admin.jobs.show', $job) }}"
                                            class="btn btn-info"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a
                                            href="{{ route('admin.jobs.edit', $job) }}"
                                            class="btn btn-warning"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form
                                            action="{{ route('admin.jobs.destroy', $job) }}"
                                            method="POST"
                                            style="display: inline"
                                            onsubmit="return confirm('Are you sure?');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No maintenance jobs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{ $jobs->links() }}
        </div>
    </div>
@endsection
