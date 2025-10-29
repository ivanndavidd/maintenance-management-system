<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceJob;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Http\Request;

class MaintenanceJobController extends Controller
{
    public function index()
    {
        $jobs = MaintenanceJob::with(['machine', 'assignedUser', 'creator'])
                             ->latest()
                             ->paginate(10);
        
        return view('admin.jobs.index', compact('jobs'));
    }

    public function create()
    {
        $machines = Machine::where('status', '!=', 'retired')->get();
        $users = User::role('user')->get();
        
        return view('admin.jobs.create', compact('machines', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'machine_id' => 'required|exists:machines,id',
            'assigned_to' => 'nullable|exists:users,id',
            'type' => 'required|in:preventive,corrective,predictive,breakdown',
            'priority' => 'required|in:low,medium,high,urgent',
            'scheduled_date' => 'required|date',
            'estimated_duration' => 'nullable|integer|min:1',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'pending';

        $job = MaintenanceJob::create($validated);

        return redirect()->route('admin.jobs.index')
                        ->with('success', 'Maintenance job created successfully!');
    }

    public function show(MaintenanceJob $job)
    {
        $job->load(['machine', 'assignedUser', 'creator', 'workReport']);
        return view('admin.jobs.show', compact('job'));
    }

    public function edit(MaintenanceJob $job)
    {
        $machines = Machine::where('status', '!=', 'retired')->get();
        $users = User::role('user')->get();
        
        return view('admin.jobs.edit', compact('job', 'machines', 'users'));
    }

    public function update(Request $request, MaintenanceJob $job)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'machine_id' => 'required|exists:machines,id',
            'assigned_to' => 'nullable|exists:users,id',
            'type' => 'required|in:preventive,corrective,predictive,breakdown',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,cancelled,overdue',
            'scheduled_date' => 'required|date',
            'estimated_duration' => 'nullable|integer|min:1',
        ]);

        $job->update($validated);

        return redirect()->route('admin.jobs.index')
                        ->with('success', 'Maintenance job updated successfully!');
    }

    public function destroy(MaintenanceJob $job)
    {
        $job->delete();
        return redirect()->route('admin.jobs.index')
                        ->with('success', 'Maintenance job deleted successfully!');
    }
}