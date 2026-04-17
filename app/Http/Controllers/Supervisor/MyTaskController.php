<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\PmSchedule;
use App\Models\PmTask;
use App\Models\PmTaskReport;
use App\Models\CorrectiveMaintenanceRequest;
use App\Models\CmReport;
use App\Models\StockOpnameSchedule;
use App\Models\StockOpnameScheduleItem;
use App\Imports\StockOpnameImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MyTaskController extends Controller
{
    /**
     * Build a query for PM tasks that are covered by an existing active shift schedule.
     * This prevents supervisor from accessing future tasks that have no shift schedule yet.
     */
    private function supervisorTasksQuery()
    {
        return PmTask::whereNotNull('task_date')
            ->whereNotNull('assigned_shift_id')
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('shift_assignments as sa')
                    ->join('shift_schedules as ss', 'ss.id', '=', 'sa.shift_schedule_id')
                    ->whereColumn('sa.shift_id', 'pm_tasks.assigned_shift_id')
                    ->whereNull('sa.change_action')
                    ->whereRaw("sa.day_of_week = LOWER(DAYNAME(pm_tasks.task_date))")
                    ->whereColumn('ss.start_date', '<=', 'pm_tasks.task_date')
                    ->whereColumn('ss.end_date', '>=', 'pm_tasks.task_date')
                    ->where('ss.status', 'active');
            });
    }

    /**
     * Display preventive maintenance tasks assigned to supervisor
     */
    public function preventiveMaintenance(Request $request)
    {
        // Supervisor sees all PM tasks covered by an active shift schedule
        $query = $this->supervisorTasksQuery()->with('latestReport')
            ->whereNotNull('task_date');

        // Filter by month
        if ($request->filled('month')) {
            $query->whereYear('task_date', date('Y', strtotime($request->month . '-01')))
                  ->whereMonth('task_date', date('m', strtotime($request->month . '-01')));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->orderBy('task_date', 'asc')->get();

        // Group tasks by month (Y-m format) — current month first, then descending
        $currentMonth = \Carbon\Carbon::now()->format('Y-m');
        $tasksByMonth = $tasks->groupBy(function ($task) {
            return \Carbon\Carbon::parse($task->task_date)->format('Y-m');
        })->sortBy(function ($tasks, $month) use ($currentMonth) {
            if ($month === $currentMonth) {
                return '0';
            }
            return '1-' . (9999 - intval(str_replace('-', '', $month)));
        });

        // Calculate stats per month
        $monthlyStats = [];
        foreach ($tasksByMonth as $month => $monthTasks) {
            $total = $monthTasks->count();
            $completed = $monthTasks->where('status', 'completed')->count();
            $inProgress = $monthTasks->where('status', 'in_progress')->count();
            $pending = $monthTasks->where('status', 'pending')->count();
            $monthlyStats[$month] = [
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'pending' => $pending,
                'progress' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        }

        // Overall stats
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $inProgressTasks = $tasks->where('status', 'in_progress')->count();
        $pendingTasks = $tasks->where('status', 'pending')->count();

        return view('supervisor.my-tasks.preventive-maintenance.index', compact(
            'tasksByMonth', 'monthlyStats', 'totalTasks', 'completedTasks', 'inProgressTasks', 'pendingTasks'
        ));
    }

    /**
     * Show individual PM task detail (supervisor)
     */
    public function showPmTask(PmTask $task)
    {
        abort_unless(
            $this->supervisorTasksQuery()->where('pm_tasks.id', $task->id)->exists(),
            403
        );

        $task->load('latestReport.furtherRepairAssets', 'logs.user');

        return view('supervisor.my-tasks.preventive-maintenance.show-task', compact('task'));
    }

    /**
     * Show specific PM schedule
     */
    public function showPreventiveMaintenance($id)
    {
        $schedule = PmSchedule::with([
            'scheduleDate.cleaningGroups.sprGroups.tasks' => function ($q) {
                $q->where('assigned_user_id', auth()->id());
            },
            'scheduleDate.standaloneTasks' => function ($q) {
                $q->where('assigned_user_id', auth()->id());
            },
        ])->findOrFail($id);

        return view('supervisor.my-tasks.preventive-maintenance.show', compact('schedule'));
    }

    /**
     * Update PM task status
     */
    public function updatePmTaskStatus(Request $request, $taskId)
    {
        $task = $this->supervisorTasksQuery()->where('pm_tasks.id', $taskId)->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'notes' => 'nullable|string',
        ]);

        $task->update($validated);

        // Send notification to supervisors and admins if task is completed
        if ($validated['status'] === 'completed') {
            try {
                $supervisors = \App\Models\User::role('supervisor_maintenance')->where('id', '!=', auth()->id())->get();
                $admins = \App\Models\User::role('admin')->get();

                foreach ($supervisors as $supervisor) {
                    \Mail::to($supervisor->email)->send(new \App\Mail\PmTaskCompleted($task));
                }
                foreach ($admins as $admin) {
                    \Mail::to($admin->email)->send(new \App\Mail\PmTaskCompleted($task));
                }

                \Log::info('PM task completion notifications sent to supervisors and admins', [
                    'task_id' => $task->id,
                    'task_name' => $task->task_name,
                    'completed_by' => auth()->user()->name,
                    'supervisors_count' => $supervisors->count(),
                    'admins_count' => $admins->count(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send PM task completion notification: ' . $e->getMessage(), [
                    'task_id' => $task->id,
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Task status updated successfully!']);
        }

        return redirect()
            ->back()
            ->with('success', 'Task status updated successfully!');
    }

    /**
     * Store PM task report (supervisor)
     */
    public function storePmReport(Request $request, PmTask $task)
    {
        // Verify task is covered by an active shift schedule
        if (!$this->supervisorTasksQuery()->where('pm_tasks.id', $task->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'description' => 'required|string',
            'photos.*' => 'nullable|image|max:5120',
            'assets' => 'nullable|array',
            'assets.*.id' => 'required_with:assets|exists:assets_master,id',
            'assets.*.notes' => 'nullable|string',
        ]);

        // Store photos
        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('pm-reports', 'public');
                $photos[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }

        // Calculate day diff: submitted date minus task date (negative=early, 0=on time, positive=late)
        $submittedDate = now()->startOfDay();
        $taskDate      = \Carbon\Carbon::parse($task->task_date)->startOfDay();
        $dayDiff       = $submittedDate->diffInDays($taskDate, false) * -1;

        // Create report
        $report = PmTaskReport::create([
            'pm_task_id'         => $task->id,
            'description'        => $request->description,
            'photos'             => $photos ?: null,
            'status'             => 'submitted',
            'submitted_by'       => auth()->id(),
            'submitted_at'       => now(),
            'submitted_day_diff' => (int) $dayDiff,
        ]);

        // Attach further repair assets
        if ($request->filled('assets')) {
            foreach ($request->assets as $assetData) {
                $report->furtherRepairAssets()->attach($assetData['id'], [
                    'notes' => $assetData['notes'] ?? null,
                ]);
            }
        }

        // Update task status to completed
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ]);

        // Log the report submission
        $task->logs()->create([
            'user_id' => auth()->id(),
            'action' => 'report_submitted',
            'notes' => 'Report submitted with ' . count($photos) . ' photo(s)',
        ]);

        return response()->json(['success' => true, 'message' => 'Report submitted successfully!']);
    }

    /**
     * Show PM task report detail
     */
    public function showPmReport(PmTask $task, PmTaskReport $report)
    {
        $report->load(['submitter', 'reviewer', 'furtherRepairAssets']);

        return response()->json([
            'success' => true,
            'report' => [
                'id' => $report->id,
                'description' => $report->description,
                'photos' => collect($report->photos)->map(function ($photo) {
                    return [
                        'path' => $photo['path'],
                        'url' => Storage::url($photo['path']),
                        'original_name' => $photo['original_name'],
                    ];
                }),
                'status' => $report->status,
                'status_label' => $report->getStatusLabel(),
                'status_badge' => $report->getStatusBadgeClass(),
                'admin_comments' => $report->admin_comments,
                'submitted_by' => $report->submitter->name ?? '-',
                'submitted_at' => $report->submitted_at?->format('d M Y, H:i'),
                'reviewed_by' => $report->reviewer->name ?? null,
                'reviewed_at' => $report->reviewed_at?->format('d M Y, H:i'),
                'further_repair_assets' => $report->furtherRepairAssets->map(function ($asset) {
                    return [
                        'id' => $asset->id,
                        'equipment_id' => $asset->equipment_id ?? '-',
                        'asset_name' => $asset->asset_name,
                        'location' => $asset->location ?? '-',
                        'notes' => $asset->pivot->notes,
                    ];
                }),
                'task' => [
                    'id' => $task->id,
                    'task_name' => $task->task_name,
                    'task_date' => $task->task_date?->format('d M Y'),
                    'shift' => $task->assigned_shift_id,
                ],
            ],
        ]);
    }

    /**
     * Display corrective maintenance tasks assigned to supervisor
     */
    public function correctiveMaintenance(Request $request)
    {
        $query = CorrectiveMaintenanceRequest::query()->with(['technicians']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->latest()->paginate(15)->appends($request->except('page'));

        $stats = [
            'total' => CorrectiveMaintenanceRequest::count(),
            'in_progress' => CorrectiveMaintenanceRequest::where('status', 'in_progress')->count(),
            'done' => CorrectiveMaintenanceRequest::whereIn('status', ['done', 'completed'])->count(),
            'further_repair' => CorrectiveMaintenanceRequest::whereIn('status', ['further_repair', 'failed'])->count(),
        ];

        return view('supervisor.my-tasks.corrective-maintenance.index', compact('tickets', 'stats'));
    }

    /**
     * Show specific CM ticket
     */
    public function showCorrectiveMaintenance($id)
    {
        $ticket = CorrectiveMaintenanceRequest::with([
                'technicians',
                'report.asset',
                'report.submitter',
                'childTickets.technicians',
                'childTickets.report',
                'parentTicket.report.asset'
            ])
            ->findOrFail($id);

        $assets = \App\Models\Asset::where('status', 'active')->orderBy('asset_name')->get();
        $groupAssets = \App\Models\GroupAsset::orderBy('group_name')->get();
        $spareparts = \App\Models\Sparepart::orderBy('sparepart_name')->get();

        return view('supervisor.my-tasks.corrective-maintenance.show', compact('ticket', 'assets', 'groupAssets', 'spareparts'));
    }

    /**
     * Update CM notes
     */
    public function updateCmNotes(Request $request, $id)
    {
        $ticket = CorrectiveMaintenanceRequest::findOrFail($id);

        $validated = $request->validate([
            'work_notes' => 'required|string',
        ]);

        $ticket->update($validated);

        return redirect()
            ->back()
            ->with('success', 'Work notes updated successfully!');
    }

    /**
     * Submit CM report
     */
    public function submitCmReport(Request $request, $id)
    {
        $userId = auth()->id();

        $ticket = CorrectiveMaintenanceRequest::findOrFail($id);

        if ($ticket->status !== 'in_progress') {
            return redirect()->back()->with('error', 'Report can only be submitted for in-progress tickets.');
        }

        $isLiftCategory = in_array($ticket->problem_category, ['lift_merah', 'lift_kuning']);

        $rules = [
            'status' => 'required|in:done,further_repair,failed',
            'asset_id' => $isLiftCategory ? 'nullable|exists:assets_master,id' : 'required|exists:assets_master,id',
            'problem_detail' => 'required|string|max:2000',
            'work_done' => 'required|string|max:2000',
            'notes' => 'nullable|string|max:1000',
        ];
        if (!$isLiftCategory) {
            $rules['sparepart_usage'] = 'required|in:yes,no';
            $rules['spareparts'] = 'required_if:sparepart_usage,yes|array|min:1';
            $rules['spareparts.*.sparepart_id'] = 'required_with:spareparts|exists:spareparts,id';
            $rules['spareparts.*.quantity_used'] = 'required_with:spareparts|integer|min:1';
        }
        $request->validate($rules);

        // Calculate severity from group asset + duration
        $severity = null;
        $asset = \App\Models\Asset::with('group')->find($request->asset_id);
        if ($asset && $asset->group) {
            $durationMinutes = $ticket->in_progress_at
                ? $ticket->in_progress_at->diffInMinutes(now())
                : 0;
            $severity = CmReport::calculateSeverity($asset->group->severity, $durationMinutes);
        }

        // Create report
        $report = CmReport::create([
            'cm_request_id' => $ticket->id,
            'asset_id' => $request->asset_id,
            'severity' => $severity,
            'status' => $request->status,
            'problem_detail' => $request->problem_detail,
            'work_done' => $request->work_done,
            'notes' => $request->notes,
            'submitted_by' => $userId,
            'submitted_at' => now(),
        ]);

        // Process sparepart usages
        if (!$isLiftCategory && $request->sparepart_usage === 'yes' && $request->filled('spareparts')) {
            $this->processSparepartUsages($report, $ticket, $request->spareparts, $userId);
        }

        // Update ticket status
        $ticket->status = $request->status;
        $ticket->report_submitted_at = now();
        $ticket->completed_at = now();
        $ticket->resolution = $request->work_done;
        $ticket->save();

        // Send email to requestor
        try {
            \Mail::to($ticket->requestor_email)->send(new \App\Mail\MaintenanceRequestCompleted($ticket));
        } catch (\Exception $e) {
            \Log::error('Failed to send report email: ' . $e->getMessage());
        }

        // Send notification to supervisors and admins
        try {
            $supervisors = \App\Models\User::role('supervisor_maintenance')->where('id', '!=', $userId)->get();
            $admins = \App\Models\User::role('admin')->get();

            foreach ($supervisors as $supervisor) {
                \Mail::to($supervisor->email)->send(new \App\Mail\MaintenanceRequestCompleted($ticket));
            }
            foreach ($admins as $admin) {
                \Mail::to($admin->email)->send(new \App\Mail\MaintenanceRequestCompleted($ticket));
            }

            \Log::info('CM report completion notifications sent to supervisors and admins', [
                'ticket' => $ticket->ticket_number,
                'status' => $request->status,
                'supervisors_count' => $supervisors->count(),
                'admins_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send completion notification to supervisors/admins: ' . $e->getMessage());
        }

        $statusLabels = [
            'done' => 'Done',
            'further_repair' => 'Further Repair',
            'failed' => 'Failed',
        ];
        $statusText = $statusLabels[$request->status] ?? ucfirst($request->status);
        return redirect()->route('supervisor.my-tasks.corrective-maintenance')
            ->with('success', "Report submitted successfully. Status: {$statusText}.");
    }

    private function processSparepartUsages(CmReport $report, CorrectiveMaintenanceRequest $ticket, array $spareparts, int $userId): void
    {
        $usageData = [];

        foreach ($spareparts as $item) {
            $sparepart = \App\Models\Sparepart::find($item['sparepart_id']);
            if (!$sparepart) continue;

            $qty = min((int)$item['quantity_used'], $sparepart->quantity);
            if ($qty <= 0) continue;

            \App\Models\SparepartUsage::create([
                'cm_report_id'  => $report->id,
                'ticket_number' => $ticket->ticket_number,
                'sparepart_id'  => $sparepart->id,
                'quantity_used' => $qty,
                'used_at'       => now()->toDateString(),
                'notes'         => 'Used in CM ticket: ' . $ticket->ticket_number,
                'used_by'       => $userId,
            ]);

            $sparepart->decrement('quantity', $qty);

            $usageData[] = [
                'name'          => $sparepart->sparepart_name,
                'material_code' => $sparepart->material_code,
                'qty'           => $qty,
                'unit'          => $sparepart->unit,
                'remaining'     => $sparepart->quantity - $qty,
                'minimum_stock' => $sparepart->minimum_stock,
            ];
        }

        if (empty($usageData)) return;

        try {
            $report->load(['cmRequest', 'asset', 'submitter']);
            $supervisors = \App\Models\User::role('supervisor_maintenance')->where('id', '!=', $userId)->get();
            $admins = \App\Models\User::role('admin')->get();
            $recipients = $supervisors->merge($admins)->unique('id');

            foreach ($recipients as $recipient) {
                \Mail::to($recipient->email)->send(new \App\Mail\SparepartUsedInCm($report, $usageData));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send sparepart usage notification: ' . $e->getMessage());
        }
    }

    public function reportSparepartOutOfStock(\App\Models\Sparepart $sparepart)
    {
        try {
            $supervisors = \App\Models\User::role('supervisor_maintenance')->where('id', '!=', auth()->id())->get();
            $admins = \App\Models\User::role('admin')->get();
            $recipients = $supervisors->merge($admins)->unique('id');

            foreach ($recipients as $recipient) {
                \Mail::to($recipient->email)->send(new \App\Mail\SparepartOutOfStock($sparepart, auth()->user()));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send out-of-stock notification: ' . $e->getMessage());
        }

        return back()->with('success', 'Supervisor and admin have been notified about the out-of-stock sparepart.');
    }

    /**
     * Acknowledge CM assignment
     */
    public function acknowledgeCm($id)
    {
        $userId = auth()->id();

        $ticket = CorrectiveMaintenanceRequest::findOrFail($id);

        $pivot = $ticket->technicians()->where('user_id', $userId)->first();

        if ($pivot) {
            $ticket->technicians()->updateExistingPivot($userId, [
                'acknowledged_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Assignment acknowledged.');
    }

    /**
     * Display stock opname schedules assigned to supervisor
     */
    public function stockOpname(Request $request)
    {
        $query = StockOpnameSchedule::whereHas('userAssignments', function ($q) {
            $q->where('user_id', auth()->id());
        });

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->with(['userAssignments.user', 'scheduleItems'])
            ->orderBy('execution_date')
            ->paginate(15);

        return view('supervisor.my-tasks.stock-opname.index', compact('schedules'));
    }

    /**
     * Show specific stock opname schedule
     */
    public function showStockOpname(Request $request, $id)
    {
        $schedule = StockOpnameSchedule::with(['createdByUser', 'userAssignments.user'])
            ->whereHas('userAssignments', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->findOrFail($id);

        // Build query with filters
        $query = $schedule->scheduleItems()->with(['executedByUser', 'sparepart', 'tool', 'asset']);

        // Filter by item type
        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        // Filter by execution status
        if ($request->filled('status')) {
            $query->where('execution_status', $request->status);
        }

        // Paginate (50 per page) with filter persistence
        $scheduleItems = $query->paginate(50)->appends($request->except('page'));

        // Get statistics
        $stats = [
            'total_items' => $schedule->total_items,
            'completed_items' => $schedule->completed_items,
            'cancelled_items' => $schedule->cancelled_items,
            'pending_items' => $schedule->pendingItems()->count(),
            'progress_percentage' => $schedule->getProgressPercentage(),
            'days_remaining' => $schedule->getDaysRemaining(),
            'is_overdue' => $schedule->isOverdue(),
        ];

        return view('supervisor.my-tasks.stock-opname.show', compact('schedule', 'scheduleItems', 'stats'));
    }

    /**
     * Execute stock opname item
     */
    public function executeOpnameItem(Request $request, $itemId)
    {
        $item = StockOpnameItem::whereHas('schedule.userAssignments', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($itemId);

        $validated = $request->validate([
            'actual_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $item->update([
            'actual_quantity' => $validated['actual_quantity'],
            'discrepancy' => $validated['actual_quantity'] - $item->expected_quantity,
            'notes' => $validated['notes'],
            'counted_by' => auth()->id(),
            'counted_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Item counted successfully!');
    }

    /**
     * Execute batch stock opname
     */
    public function executeOpnameBatch(Request $request)
    {
        $user = auth()->user();

        // Log incoming request for debugging
        \Log::info('executeOpnameBatch called', [
            'user_id' => $user->id,
            'request_data' => $request->all()
        ]);

        // Validate input
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:stock_opname_schedule_items,id',
            'items.*.physical_quantity' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $itemsWithDiscrepancy = [];
        $scheduleId = null;

        foreach ($request->items as $itemData) {
            try {
                $item = StockOpnameScheduleItem::findOrFail($itemData['item_id']);
                $schedule = $item->schedule;
                $scheduleId = $schedule->id;

                \Log::info('Processing item', [
                    'item_id' => $item->id,
                    'schedule_id' => $schedule->id
                ]);

                // Verify user is assigned to this schedule
                $isAssigned = $schedule->userAssignments()
                    ->where('user_id', $user->id)
                    ->exists();

                \Log::info('User assignment check', [
                    'user_id' => $user->id,
                    'is_assigned' => $isAssigned
                ]);

                if (!$isAssigned) {
                    $errorCount++;
                    $errors[] = "Not assigned to item {$item->id}";
                    \Log::warning('User not assigned to schedule', [
                        'user_id' => $user->id,
                        'schedule_id' => $schedule->id,
                        'item_id' => $item->id
                    ]);
                    continue;
                }

                // Use markCompleted method which handles discrepancy calculation and review status
                $item->markCompleted($user->id, $itemData['physical_quantity'], $itemData['notes'] ?? null);

                // Track items with discrepancies
                if ($item->review_status === 'needs_review') {
                    $itemsWithDiscrepancy[] = $item;
                }

                $successCount++;
                \Log::info('Item saved successfully', ['item_id' => $item->id]);
            } catch (\Exception $e) {
                $errorCount++;
                $errorMsg = "Failed to save item {$itemData['item_id']}: {$e->getMessage()}";
                $errors[] = $errorMsg;
                \Log::error('Error saving item', [
                    'item_id' => $itemData['item_id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Send notification if there are items needing review
        if (count($itemsWithDiscrepancy) > 0 && $scheduleId) {
            try {
                $schedule = StockOpnameSchedule::find($scheduleId);
                $supervisors = \App\Models\User::role('supervisor_maintenance')->where('id', '!=', $user->id)->get();
                $admins = \App\Models\User::role('admin')->get();

                foreach ($supervisors as $supervisor) {
                    \Mail::to($supervisor->email)->send(new \App\Mail\StockOpnameItemCompleted($schedule, $itemsWithDiscrepancy));
                }
                foreach ($admins as $admin) {
                    \Mail::to($admin->email)->send(new \App\Mail\StockOpnameItemCompleted($schedule, $itemsWithDiscrepancy));
                }

                \Log::info('Stock opname discrepancy notifications sent to supervisors and admins', [
                    'schedule_code' => $schedule->schedule_code,
                    'items_with_discrepancy' => count($itemsWithDiscrepancy),
                    'supervisors_count' => $supervisors->count(),
                    'admins_count' => $admins->count(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send stock opname discrepancy notification: ' . $e->getMessage(), [
                    'schedule_id' => $scheduleId,
                ]);
            }
        }

        if ($errorCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Saved {$successCount} items, failed {$errorCount} items",
                'errors' => $errors,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully saved {$successCount} items",
            'data' => [
                'count' => $successCount,
            ]
        ]);
    }

    /**
     * Cancel stock opname item
     */
    public function cancelOpnameItem($itemId)
    {
        $item = StockOpnameItem::whereHas('schedule.userAssignments', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($itemId);

        $item->update([
            'actual_quantity' => null,
            'discrepancy' => null,
            'notes' => null,
            'counted_by' => null,
            'counted_at' => null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Item count cancelled!');
    }

    /**
     * Export stock opname template
     */
    public function exportOpnameTemplate($id)
    {
        $user = auth()->user();

        $schedule = StockOpnameSchedule::findOrFail($id);

        // Check if user is assigned to this schedule
        $isAssigned = $schedule->userAssignments()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAssigned) {
            return redirect()->route('supervisor.my-tasks.stock-opname')
                ->with('error', 'You are not assigned to this schedule.');
        }

        $filename = 'StockOpname_' . $schedule->schedule_code . '_' . date('Ymd_His') . '.xlsx';

        // Get pending items
        $items = $schedule->scheduleItems()
            ->with(['sparepart', 'tool', 'asset'])
            ->where('execution_status', 'pending')
            ->get();

        return response()->streamDownload(function() use ($items) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set sheet title
            $sheet->setTitle('Stock Opname');

            // Define headers (tanpa Item ID dan System Qty untuk meningkatkan akurasi)
            $headers = ['Item Type', 'Item Code', 'Item Name', 'Location', 'Physical Qty', 'Notes'];

            // Set headers in row 1
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            // Style header row
            $sheet->getStyle('A1:F1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Fill data rows
            $row = 2;
            foreach ($items as $item) {
                $location = '-';
                if ($item->item_type === 'sparepart' && $item->sparepart) {
                    $location = $item->sparepart->location ?? '-';
                } elseif ($item->item_type === 'asset' && $item->asset) {
                    $location = $item->asset->location ?? '-';
                }

                $sheet->setCellValue('A' . $row, ucfirst($item->item_type));
                // Set Item Code as text to prevent scientific notation
                $sheet->setCellValueExplicit('B' . $row, $item->getItemCode(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $row, $item->getItemName());
                $sheet->setCellValue('D' . $row, $location);
                $sheet->setCellValue('E' . $row, ''); // Physical Qty - to be filled
                $sheet->setCellValue('F' . $row, ''); // Notes - optional

                $row++;
            }

            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(12);  // Item Type
            $sheet->getColumnDimension('B')->setWidth(20);  // Item Code
            $sheet->getColumnDimension('C')->setWidth(40);  // Item Name
            $sheet->getColumnDimension('D')->setWidth(15);  // Location
            $sheet->getColumnDimension('E')->setWidth(15);  // Physical Qty
            $sheet->getColumnDimension('F')->setWidth(30);  // Notes

            // Add borders to all cells with data
            if ($row > 2) {
                $sheet->getStyle('A1:F' . ($row - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename);
    }

    /**
     * Import stock opname from Excel
     */
    public function importOpnameExcel(Request $request, $id)
    {
        $user = auth()->user();

        $schedule = StockOpnameSchedule::findOrFail($id);

        // Check if user is assigned to this schedule
        $isAssigned = $schedule->userAssignments()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this schedule.'
            ], 403);
        }

        // Validate file
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('excel_file');
            $import = new StockOpnameImport($user->id, $schedule->id);

            // Call import method with file path (only parses, doesn't save)
            $import->import($file->getRealPath());

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();
            $importedData = $import->getImportedData();

            return response()->json([
                'success' => true,
                'message' => "Berhasil membaca {$successCount} item dari Excel.",
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => array_slice($errors, 0, 10),
                'data' => $importedData, // Data to fill in UI
            ]);

        } catch (\Exception $e) {
            \Log::error('Stock Opname Import Error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
                'file' => $request->file('excel_file')->getClientOriginalName(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
