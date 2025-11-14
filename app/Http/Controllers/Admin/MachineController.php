<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\Department;
use App\Models\MachineCategory;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    /**
     * Display a listing of machines
     */
    public function index(Request $request)
    {
        $query = Machine::with(['department', 'category']);

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Department filter
        if ($request->has('department') && $request->department != '') {
            $query->where('department_id', $request->department);
        }

        // Category filter (instead of type)
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort columns
        $allowedSortColumns = [
            'code',
            'name',
            'location',
            'status',
            'next_maintenance_date',
            'created_at'
        ];

        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // Apply sorting
        if ($sortBy === 'created_at') {
            $query->orderBy('created_at', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $machines = $query->paginate(15);

        // Get filter options
        $departments = Department::all();
        $categories = MachineCategory::all();
        $statuses = ['operational', 'maintenance', 'breakdown', 'retired'];

        return view(
            'admin.machines.index',
            compact('machines', 'departments', 'categories', 'statuses'),
        );
    }

    /**
     * Show the form for creating a new machine
     */
    public function create()
    {
        $departments = Department::all();
        $categories = MachineCategory::all();

        return view('admin.machines.create', compact('departments', 'categories'));
    }

    /**
     * Store a newly created machine
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:machines'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:machine_categories,id'],
            'model' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'warranty_expiry' => ['nullable', 'date'],
            'department_id' => ['required', 'exists:departments,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:operational,maintenance,breakdown,retired'],
            'specifications' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'maintenance_interval_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $machine = Machine::create($validated);

        return redirect()
            ->route('admin.machines.index')
            ->with('success', 'Machine created successfully!');
    }

    /**
     * Display the specified machine
     */
    public function show(Machine $machine)
    {
        $machine->load(['department', 'category', 'maintenanceJobs.assignedUser']);

        // Calculate machine statistics
        $stats = [
            'total_jobs' => $machine->maintenanceJobs()->count(),
            'completed_jobs' => $machine->maintenanceJobs()->where('status', 'completed')->count(),
            'pending_jobs' => $machine->maintenanceJobs()->where('status', 'pending')->count(),
            'in_progress_jobs' => $machine
                ->maintenanceJobs()
                ->where('status', 'in_progress')
                ->count(),
            'breakdown_count' => $machine->maintenanceJobs()->where('type', 'breakdown')->count(),
            'preventive_count' => $machine->maintenanceJobs()->where('type', 'preventive')->count(),
            'last_maintenance' => $machine
                ->maintenanceJobs()
                ->where('status', 'completed')
                ->latest('completed_at')
                ->first(),
        ];

        // Recent maintenance jobs
        $recentJobs = $machine->maintenanceJobs()->with('assignedUser')->latest()->limit(10)->get();

        // Uptime calculation (days since last breakdown)
        $lastBreakdown = $machine
            ->maintenanceJobs()
            ->where('type', 'breakdown')
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();

        $uptimeDays = $lastBreakdown ? now()->diffInDays($lastBreakdown->completed_at) : 'N/A';

        return view('admin.machines.show', compact('machine', 'stats', 'recentJobs', 'uptimeDays'));
    }

    /**
     * Show the form for editing the specified machine
     */
    public function edit(Machine $machine)
    {
        $departments = Department::all();
        $categories = MachineCategory::all();

        return view('admin.machines.edit', compact('machine', 'departments', 'categories'));
    }

    /**
     * Update the specified machine
     */
    public function update(Request $request, Machine $machine)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:machines,code,' . $machine->id],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:machine_categories,id'],
            'model' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'warranty_expiry' => ['nullable', 'date'],
            'department_id' => ['required', 'exists:departments,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:operational,maintenance,breakdown,retired'],
            'specifications' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'last_maintenance_date' => ['nullable', 'date'],
            'next_maintenance_date' => ['nullable', 'date'],
            'maintenance_interval_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $machine->update($validated);

        return redirect()
            ->route('admin.machines.index')
            ->with('success', 'Machine updated successfully!');
    }

    /**
     * Remove the specified machine
     */
    public function destroy(Machine $machine)
    {
        // Check if machine has active jobs
        $activeJobs = $machine
            ->maintenanceJobs()
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        if ($activeJobs > 0) {
            return redirect()
                ->route('admin.machines.index')
                ->with('error', 'Cannot delete machine with active maintenance jobs!');
        }

        $machine->delete();

        return redirect()
            ->route('admin.machines.index')
            ->with('success', 'Machine deleted successfully!');
    }

    /**
     * Update machine status
     */
    public function updateStatus(Request $request, Machine $machine)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:operational,maintenance,breakdown,retired'],
        ]);

        $machine->update([
            'status' => $validated['status'],
        ]);

        return redirect()->back()->with('success', 'Machine status updated successfully!');
    }
}
