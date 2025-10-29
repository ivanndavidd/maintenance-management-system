@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4">My Dashboard</h2>

        <!-- My Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5>Pending Tasks</h5>
                        <h2>{{ $stats['my_pending_tasks'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>In Progress</h5>
                        <h2>{{ $stats['my_in_progress'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Completed (This Month)</h5>
                        <h2>{{ $stats['completed_this_month'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5>Total Reports</h5>
                        <h2>{{ $stats['my_reports'] }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Tasks -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">My Maintenance Tasks</h5>
                    </div>
                    <div class="card-body">
                        @if ($myTasks->count() > 0)
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Job Code</th>
                                            <th>Title</th>
                                            <th>Equipment</th>
                                            <th>Department</th>
                                            <th>Scheduled</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($myTasks as $task))
                                            <tr>
                                                <td>{{ $task->job_code }}</td>
                                                <td>{{ $task->title }}</td>
                                                <td>{{ $task->machine->name }}</td>
                                                <td>{{ $task->machine->department->name }}</td>
                                                <td>
                                                    {{ $task->scheduled_date->format('M d, Y H:i') }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $task->priority == 'urgent' ? 'danger' : ($task->priority == 'high' ? 'warning' : 'info') }}"
                                                    >
                                                        {{ ucfirst($task->priority) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $task->status == 'in_progress' ? 'primary' : 'secondary' }}"
                                                    >
                                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a
                                                        href="{{ route('user.tasks.show', $task->id) }}"
                                                        class="btn btn-sm btn-primary"
                                                    >
                                                        View
                                                    </a>
                                                    @if ($task->status == 'pending')
                                                        <button class="btn btn-sm btn-success">
                                                            Start
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p>No pending tasks at the moment.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
