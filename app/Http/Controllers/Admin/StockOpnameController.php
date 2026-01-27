<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockOpnameSchedule;
use App\Models\StockOpnameScheduleItem;
use App\Models\StockOpnameExecution;
use App\Models\Sparepart;
use App\Models\Tool;
use App\Models\Asset;
use App\Models\User;
use App\Services\StockOpnameService;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    protected $stockOpnameService;

    public function __construct(StockOpnameService $stockOpnameService)
    {
        $this->stockOpnameService = $stockOpnameService;
    }

    // Schedule Management
    public function scheduleIndex(Request $request)
    {
        $query = StockOpnameSchedule::with(['createdByUser', 'scheduleItems', 'userAssignments']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by execution date
        if ($request->filled('execution_date')) {
            $query->whereDate('execution_date', $request->execution_date);
        }

        $schedules = $query->latest()->paginate(15)->appends($request->except('page'));

        return view('admin.opname.schedules.index', compact('schedules'));
    }

    public function scheduleCreate()
    {
        $scheduleCode = StockOpnameSchedule::generateScheduleCode();

        // Get available locations for spareparts and assets
        $sparepartLocations = $this->stockOpnameService->getSparepartLocations();
        $assetLocations = $this->stockOpnameService->getAssetLocations();

        // Get all active users with staff_maintenance role for assignment
        $users = User::role('staff_maintenance')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.opname.schedules.create', compact('scheduleCode', 'sparepartLocations', 'assetLocations', 'users'));
    }

    public function scheduleStore(Request $request)
    {
        $validated = $request->validate([
            'execution_date' => 'required|date|after_or_equal:today',
            'assigned_users' => 'required|array|min:1',
            'assigned_users.*' => 'exists:users,id',
            'include_spareparts' => 'boolean',
            'include_tools' => 'boolean',
            'include_assets' => 'boolean',
            'sparepart_locations' => 'nullable|array',
            'sparepart_locations.*' => 'string',
            'asset_locations' => 'nullable|array',
            'asset_locations.*' => 'string',
            'notes' => 'nullable|string',
        ]);

        // Validate at least one item type is selected
        if (empty($validated['include_spareparts']) && empty($validated['include_tools']) && empty($validated['include_assets'])) {
            return back()->withErrors(['include_spareparts' => 'Please select at least one item type (Spareparts, Tools, or Assets).'])->withInput();
        }

        // Validate sparepart locations if spareparts are included
        if (!empty($validated['include_spareparts']) && empty($validated['sparepart_locations'])) {
            return back()->withErrors(['sparepart_locations' => 'Please select at least one sparepart location.'])->withInput();
        }

        // Validate asset locations if assets are included
        if (!empty($validated['include_assets']) && empty($validated['asset_locations'])) {
            return back()->withErrors(['asset_locations' => 'Please select at least one asset location.'])->withInput();
        }

        // Use execution_date as both start and end date (for backwards compatibility with DB schema)
        $executionDate = $validated['execution_date'];

        // Create schedule
        $schedule = StockOpnameSchedule::create([
            'schedule_code' => StockOpnameSchedule::generateScheduleCode(),
            'execution_date' => $executionDate,
            'include_spareparts' => !empty($validated['include_spareparts']),
            'include_tools' => !empty($validated['include_tools']),
            'include_assets' => !empty($validated['include_assets']),
            'sparepart_locations' => $validated['sparepart_locations'] ?? null,
            'asset_locations' => $validated['asset_locations'] ?? null,
            'created_by' => auth()->id(),
            'status' => 'active',
            'notes' => $validated['notes'],
        ]);

        // Create schedule items based on selections
        $totalItems = $this->stockOpnameService->createScheduleItems($schedule, $validated);

        // Manual user assignment
        $assignedUsersCount = $this->stockOpnameService->assignUsersManually(
            $schedule,
            $validated['assigned_users'],
            $validated['execution_date']
        );

        return redirect()
            ->route('admin.opname.schedules.index')
            ->with('success', "Stock Opname schedule created successfully! Total items: {$totalItems}, Assigned users: {$assignedUsersCount}");
    }

    public function scheduleShow(Request $request, StockOpnameSchedule $schedule)
    {
        $schedule->load(['createdByUser', 'executions', 'userAssignments.user']);

        // Build query for schedule items with filters
        $query = $schedule->scheduleItems();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Search in sparepart
                $q->whereHas('sparepart', function($sq) use ($search) {
                    $sq->where('sparepart_name', 'like', "%{$search}%")
                       ->orWhere('material_code', 'like', "%{$search}%");
                })
                // Search in tool
                ->orWhereHas('tool', function($tq) use ($search) {
                    $tq->where('sparepart_name', 'like', "%{$search}%")
                       ->orWhere('material_code', 'like', "%{$search}%");
                })
                // Search in asset
                ->orWhereHas('asset', function($aq) use ($search) {
                    $aq->where('asset_name', 'like', "%{$search}%")
                       ->orWhere('equipment_id', 'like', "%{$search}%");
                });
            });
        }

        // Filter by item type
        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        // Filter by execution status
        if ($request->filled('status')) {
            $query->where('execution_status', $request->status);
        }

        // Filter by review status
        if ($request->filled('review_status')) {
            $query->where('review_status', $request->review_status);
        }

        // Paginate schedule items (50 per page for better performance)
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

        // Get analytics data
        $analytics = $schedule->getAnalytics();

        return view('admin.opname.schedules.show', compact('schedule', 'scheduleItems', 'stats', 'analytics'));
    }

    public function scheduleEdit(StockOpnameSchedule $schedule)
    {
        // Get available locations for spareparts and assets
        $sparepartLocations = $this->stockOpnameService->getSparepartLocations();
        $assetLocations = $this->stockOpnameService->getAssetLocations();

        // Get all active users with staff_maintenance role for assignment
        $users = User::role('staff_maintenance')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get currently assigned user IDs
        $assignedUserIds = $schedule->userAssignments()->pluck('user_id')->toArray();

        $schedule->load('scheduleItems', 'userAssignments');

        return view('admin.opname.schedules.edit', compact('schedule', 'sparepartLocations', 'assetLocations', 'users', 'assignedUserIds'));
    }

    public function scheduleUpdate(Request $request, StockOpnameSchedule $schedule)
    {
        $validated = $request->validate([
            'execution_date' => 'required|date|after_or_equal:today',
            'assigned_users' => 'required|array|min:1',
            'assigned_users.*' => 'exists:users,id',
            'include_spareparts' => 'boolean',
            'include_tools' => 'boolean',
            'include_assets' => 'boolean',
            'sparepart_locations' => 'nullable|array',
            'sparepart_locations.*' => 'string',
            'asset_locations' => 'nullable|array',
            'asset_locations.*' => 'string',
            'status' => 'required|in:active,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Validate at least one item type is selected
        if (empty($validated['include_spareparts']) && empty($validated['include_tools']) && empty($validated['include_assets'])) {
            return back()->withErrors(['include_spareparts' => 'Please select at least one item type (Spareparts, Tools, or Assets).'])->withInput();
        }

        // Validate sparepart locations if spareparts are included
        if (!empty($validated['include_spareparts']) && empty($validated['sparepart_locations'])) {
            return back()->withErrors(['sparepart_locations' => 'Please select at least one sparepart location.'])->withInput();
        }

        // Validate asset locations if assets are included
        if (!empty($validated['include_assets']) && empty($validated['asset_locations'])) {
            return back()->withErrors(['asset_locations' => 'Please select at least one asset location.'])->withInput();
        }

        // Use execution_date as both start and end date
        $executionDate = $validated['execution_date'];

        // Update schedule basic info
        $schedule->update([
            'execution_date' => $executionDate,
            'include_spareparts' => !empty($validated['include_spareparts']),
            'include_tools' => !empty($validated['include_tools']),
            'include_assets' => !empty($validated['include_assets']),
            'sparepart_locations' => $validated['sparepart_locations'] ?? null,
            'asset_locations' => $validated['asset_locations'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'],
        ]);

        // Delete only pending schedule items (preserve completed/cancelled ones)
        $schedule->scheduleItems()->where('execution_status', 'pending')->delete();

        // Recreate schedule items based on new selections
        $totalItems = $this->stockOpnameService->createScheduleItems($schedule, $validated);

        // Update user assignments - remove old, add new
        $schedule->userAssignments()->delete();

        $assignedUsersCount = $this->stockOpnameService->assignUsersManually(
            $schedule,
            $validated['assigned_users'],
            $validated['execution_date']
        );

        // Recalculate total items (including preserved completed/cancelled ones)
        $completedCount = $schedule->scheduleItems()->where('execution_status', 'completed')->count();
        $cancelledCount = $schedule->scheduleItems()->where('execution_status', 'cancelled')->count();
        $allItemsCount = $schedule->scheduleItems()->count();

        $schedule->update([
            'total_items' => $allItemsCount,
            'completed_items' => $completedCount,
            'cancelled_items' => $cancelledCount,
        ]);

        return redirect()
            ->route('admin.opname.schedules.show', $schedule)
            ->with('success', "Stock Opname schedule updated successfully! Total items: {$allItemsCount}, Assigned users: {$assignedUsersCount}");
    }

    // Execution Management
    public function executionIndex(Request $request)
    {
        $query = StockOpnameExecution::with(['schedule', 'executedByUser', 'verifiedByUser']);

        // Filter by item_type
        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        // Filter by status (compliance)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $executions = $query->latest('execution_date')->paginate(15)->appends($request->except('page'));

        return view('admin.opname.executions.index', compact('executions'));
    }

    public function executionCreate()
    {
        $schedules = StockOpnameSchedule::where('status', 'active')
            ->with('scheduleItems')
            ->get();
        $spareparts = Sparepart::orderBy('sparepart_name')->get();
        $tools = Tool::orderBy('sparepart_name')->get();
        $executionCode = StockOpnameExecution::generateExecutionCode();

        return view('admin.opname.executions.create', compact('schedules', 'spareparts', 'tools', 'executionCode'));
    }

    public function executionStore(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'nullable|exists:stock_opname_schedules,id',
            'item_type' => 'required|in:sparepart,tool',
            'item_id' => 'required|integer',
            'execution_date' => 'required|date',
            'physical_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string',
            'discrepancy_notes' => 'nullable|string',
        ]);

        // Get item
        if ($validated['item_type'] === 'sparepart') {
            $item = Sparepart::findOrFail($validated['item_id']);
        } else {
            $item = Tool::findOrFail($validated['item_id']);
        }

        $validated['execution_code'] = StockOpnameExecution::generateExecutionCode();
        $validated['system_quantity'] = $item->quantity;
        $validated['executed_by'] = auth()->id();

        // Get scheduled date if from schedule
        if ($validated['schedule_id']) {
            $schedule = StockOpnameSchedule::find($validated['schedule_id']);
            $validated['scheduled_date'] = $schedule->scheduled_date;
        }

        $execution = StockOpnameExecution::create($validated);

        // Calculate discrepancy
        $itemPrice = $item->parts_price ?? 0;
        $execution->calculateDiscrepancy($itemPrice);

        // Calculate compliance status
        $execution->calculateComplianceStatus();

        // Update schedule if exists
        if ($execution->schedule_id) {
            $schedule = $execution->schedule;
            if ($execution->is_missed) {
                $schedule->incrementMissedCount();
            }
            $schedule->incrementExecutionCount();
        }

        return redirect()
            ->route('admin.opname.executions.index')
            ->with('success', 'Opname execution recorded successfully!');
    }

    public function executionShow(StockOpnameExecution $execution)
    {
        $execution->load(['schedule', 'executedByUser', 'verifiedByUser', 'adjustments']);

        // Load item based on type
        if ($execution->item_type === 'sparepart') {
            $item = Sparepart::find($execution->item_id);
        } else {
            $item = Tool::find($execution->item_id);
        }

        return view('admin.opname.executions.show', compact('execution', 'item'));
    }

    public function executionVerify(Request $request, StockOpnameExecution $execution)
    {
        $execution->update([
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.opname.executions.show', $execution)
            ->with('success', 'Opname execution verified successfully!');
    }

    // Dashboard
    public function dashboard()
    {
        // Count overdue schedules (end_date has passed)
        $overdueCount = 0;
        $dueSoonCount = 0;

        $activeSchedules = StockOpnameSchedule::where('status', 'active')->get();

        foreach ($activeSchedules as $schedule) {
            if ($schedule->isOverdue()) {
                $overdueCount++;
            } elseif ($schedule->isDueSoon()) {
                $dueSoonCount++;
            }
        }

        // Calculate accuracy rate across all active schedules
        $totalCompletedItems = 0;
        $totalAccurateItems = 0;

        foreach ($activeSchedules as $schedule) {
            $completedItems = $schedule->items()->where('execution_status', 'completed')->get();
            $totalCompletedItems += $completedItems->count();
            $totalAccurateItems += $completedItems->filter(fn($item) => !$item->hasDiscrepancy())->count();
        }

        $averageAccuracy = $totalCompletedItems > 0
            ? round(($totalAccurateItems / $totalCompletedItems) * 100, 1)
            : 0;

        // Calculate missed executions (schedules more than 7 days overdue)
        $missedExecutions = 0;
        foreach ($activeSchedules as $schedule) {
            if ($schedule->isOverdue()) {
                $daysOverdue = abs($schedule->getDaysRemaining());
                if ($daysOverdue > 7) {
                    $missedExecutions++;
                }
            }
        }

        $stats = [
            'total_schedules' => $activeSchedules->count(),
            'overdue_schedules' => $overdueCount,
            'due_soon_schedules' => $dueSoonCount,
            'total_items' => StockOpnameSchedule::where('status', 'active')->sum('total_items'),
            'completed_items' => StockOpnameSchedule::where('status', 'active')->sum('completed_items'),
            'total_executions' => $totalCompletedItems,
            'average_accuracy' => $averageAccuracy,
            'missed_executions' => $missedExecutions,
        ];

        // Get upcoming schedules (ordered by execution_date)
        $upcomingSchedules = StockOpnameSchedule::with(['scheduleItems', 'userAssignments.user'])
            ->where('status', 'active')
            ->orderBy('execution_date')
            ->limit(10)
            ->get();

        // Get recent executions (last 10 executions ordered by execution date)
        $recentExecutions = StockOpnameExecution::with(['schedule', 'executedByUser'])
            ->whereNotNull('execution_date')
            ->orderBy('execution_date', 'desc')
            ->limit(10)
            ->get();

        return view('admin.opname.dashboard', compact('stats', 'upcomingSchedules', 'recentExecutions'));
    }

    // Reports
    public function complianceReport(Request $request)
    {
        $query = StockOpnameExecution::with(['schedule', 'executedByUser']);

        // Filter by item_type
        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('execution_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('execution_date', '<=', $request->date_to);
        }

        $executions = $query->orderBy('execution_date', 'desc')->paginate(20)->appends($request->except('page'));

        // Calculate stats based on filtered results
        $statsQuery = StockOpnameExecution::query();

        if ($request->filled('item_type')) {
            $statsQuery->where('item_type', $request->item_type);
        }

        if ($request->filled('date_from')) {
            $statsQuery->whereDate('execution_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $statsQuery->whereDate('execution_date', '<=', $request->date_to);
        }

        $stats = [
            'on_time' => (clone $statsQuery)->where('status', 'on_time')->count(),
            'late' => (clone $statsQuery)->where('status', 'late')->count(),
            'early' => (clone $statsQuery)->where('status', 'early')->count(),
            'missed' => (clone $statsQuery)->where('is_missed', true)->count(),
        ];

        return view('admin.opname.reports.compliance', compact('executions', 'stats'));
    }

    public function accuracyReport(Request $request)
    {
        $query = StockOpnameExecution::with(['executedByUser']);

        // Filter by item_type
        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        // Filter by has_discrepancy
        if ($request->filled('has_discrepancy')) {
            if ($request->has_discrepancy === 'yes') {
                $query->where('discrepancy_qty', '!=', 0);
            } elseif ($request->has_discrepancy === 'no') {
                $query->where('discrepancy_qty', '=', 0);
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('execution_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('execution_date', '<=', $request->date_to);
        }

        // Filter by accuracy level
        if ($request->filled('accuracy_level')) {
            $allExecutions = $query->get();

            $filteredExecutions = $allExecutions->filter(function($execution) use ($request) {
                $accuracy = $execution->getAccuracyPercentage();

                switch ($request->accuracy_level) {
                    case 'excellent':
                        return $accuracy >= 95;
                    case 'good':
                        return $accuracy >= 80 && $accuracy < 95;
                    case 'poor':
                        return $accuracy < 80;
                    default:
                        return true;
                }
            });

            // Manual pagination for filtered collection
            $perPage = 20;
            $currentPage = request()->get('page', 1);
            $filteredExecutions = $filteredExecutions->values();
            $total = $filteredExecutions->count();

            $executions = new \Illuminate\Pagination\LengthAwarePaginator(
                $filteredExecutions->forPage($currentPage, $perPage),
                $total,
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $executions = $query->orderBy('execution_date', 'desc')->paginate(20)->appends($request->except('page'));
        }

        // Calculate stats based on filtered data
        $statsQuery = StockOpnameExecution::query();

        if ($request->filled('item_type')) {
            $statsQuery->where('item_type', $request->item_type);
        }

        if ($request->filled('has_discrepancy')) {
            if ($request->has_discrepancy === 'yes') {
                $statsQuery->where('discrepancy_qty', '!=', 0);
            } elseif ($request->has_discrepancy === 'no') {
                $statsQuery->where('discrepancy_qty', '=', 0);
            }
        }

        if ($request->filled('date_from')) {
            $statsQuery->whereDate('execution_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $statsQuery->whereDate('execution_date', '<=', $request->date_to);
        }

        $allFilteredExecutions = $statsQuery->get();

        // Apply accuracy level filter to stats
        if ($request->filled('accuracy_level')) {
            $allFilteredExecutions = $allFilteredExecutions->filter(function($execution) use ($request) {
                $accuracy = $execution->getAccuracyPercentage();

                switch ($request->accuracy_level) {
                    case 'excellent':
                        return $accuracy >= 95;
                    case 'good':
                        return $accuracy >= 80 && $accuracy < 95;
                    case 'poor':
                        return $accuracy < 80;
                    default:
                        return true;
                }
            });
        }

        // Calculate statistics
        $totalAccuracy = 0;
        $totalDiscrepancyValue = 0;
        $itemsWithDiscrepancy = 0;
        $excellent = 0;
        $good = 0;
        $poor = 0;

        foreach ($allFilteredExecutions as $execution) {
            $accuracy = $execution->getAccuracyPercentage();
            $totalAccuracy += $accuracy;
            $totalDiscrepancyValue += abs($execution->discrepancy_value);

            if ($execution->discrepancy_qty != 0) {
                $itemsWithDiscrepancy++;
            }

            if ($accuracy >= 95) {
                $excellent++;
            } elseif ($accuracy >= 80) {
                $good++;
            } else {
                $poor++;
            }
        }

        $stats = [
            'average_accuracy' => $allFilteredExecutions->count() > 0 ? round($totalAccuracy / $allFilteredExecutions->count(), 2) : 100,
            'total_discrepancy_value' => $totalDiscrepancyValue,
            'items_with_discrepancy' => $itemsWithDiscrepancy,
            'excellent' => $excellent,
            'good' => $good,
            'poor' => $poor,
        ];

        return view('admin.opname.reports.accuracy', compact('executions', 'stats'));
    }

    // Admin Review Discrepancies
    public function approveItem(Request $request, StockOpnameScheduleItem $item)
    {
        $validated = $request->validate([
            'review_notes' => 'nullable|string|max:500',
        ]);

        // Only allow approval for items with discrepancies
        if (!$item->hasDiscrepancy()) {
            return back()->withErrors(['error' => 'This item has no discrepancy to review.']);
        }

        // Only allow approval for pending review items
        if (!$item->needsReview()) {
            return back()->withErrors(['error' => 'This item is not pending review.']);
        }

        $item->approveDiscrepancy(auth()->id(), $validated['review_notes'] ?? null);

        return back()->with('success', 'Discrepancy approved successfully!');
    }

    public function rejectItem(Request $request, StockOpnameScheduleItem $item)
    {
        $validated = $request->validate([
            'review_notes' => 'required|string|max:500',
        ]);

        // Only allow rejection for items with discrepancies
        if (!$item->hasDiscrepancy()) {
            return back()->withErrors(['error' => 'This item has no discrepancy to review.']);
        }

        // Only allow rejection for pending review items
        if (!$item->needsReview()) {
            return back()->withErrors(['error' => 'This item is not pending review.']);
        }

        $item->rejectDiscrepancy(auth()->id(), $validated['review_notes']);

        return back()->with('success', 'Discrepancy rejected. Item has been reset for re-execution.');
    }

    // Batch approve multiple items
    public function batchApproveItems(Request $request, StockOpnameSchedule $schedule)
    {
        $validated = $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:stock_opname_schedule_items,id',
            'review_notes' => 'nullable|string|max:500',
        ]);

        $approvedCount = 0;

        foreach ($validated['item_ids'] as $itemId) {
            $item = StockOpnameScheduleItem::find($itemId);

            if ($item && $item->needsReview() && $item->hasDiscrepancy()) {
                $item->approveDiscrepancy(auth()->id(), $validated['review_notes'] ?? null);
                $approvedCount++;
            }
        }

        return back()->with('success', "Successfully approved {$approvedCount} item(s)!");
    }

    // Sync approved items to stock adjustments
    public function syncToStock(Request $request, StockOpnameSchedule $schedule)
    {
        $validated = $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:stock_opname_schedule_items,id',
        ]);

        $syncedCount = 0;
        $skippedCount = 0;
        $adjustments = [];

        foreach ($validated['item_ids'] as $itemId) {
            $item = StockOpnameScheduleItem::find($itemId);

            // Check if item can be synced
            if (!$item || $item->review_status !== 'approved' || !$item->hasDiscrepancy()) {
                $skippedCount++;
                continue;
            }

            // Check if already synced
            if ($item->isSynced()) {
                $skippedCount++;
                continue;
            }

            // Sync item to stock adjustment
            $adjustment = $item->syncToStockAdjustment();

            if ($adjustment) {
                $adjustments[] = $adjustment;
                $syncedCount++;
            } else {
                $skippedCount++;
            }
        }

        $message = "Successfully synced {$syncedCount} item(s) to stock adjustments.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} item(s) were skipped (already synced or not eligible).";
        }

        return back()->with('success', $message);
    }

    // Sync single item to stock adjustment
    public function syncItemToStock(StockOpnameScheduleItem $item)
    {
        // Validate item is approved and has discrepancy
        if ($item->review_status !== 'approved') {
            return back()->with('error', 'Only approved items can be synced to stock.');
        }

        if (!$item->hasDiscrepancy()) {
            return back()->with('info', 'No discrepancy found. No stock adjustment needed.');
        }

        // Check if already synced
        if ($item->isSynced()) {
            $adjustment = $item->stockAdjustment();
            return back()->with('info', 'Item already synced to stock adjustment: ' . $adjustment->adjustment_code);
        }

        // Sync to stock adjustment
        $adjustment = $item->syncToStockAdjustment();

        if ($adjustment) {
            return back()->with('success', 'Successfully synced to stock adjustment: ' . $adjustment->adjustment_code);
        }

        return back()->with('error', 'Failed to sync item to stock adjustment.');
    }

    // Get sync status for schedule
    public function getSyncStatus(StockOpnameSchedule $schedule)
    {
        $approvedItems = $schedule->items()
            ->where('review_status', 'approved')
            ->where('discrepancy_qty', '!=', 0)
            ->get();

        $syncedCount = 0;
        $pendingSyncCount = 0;

        foreach ($approvedItems as $item) {
            if ($item->isSynced()) {
                $syncedCount++;
            } else {
                $pendingSyncCount++;
            }
        }

        return response()->json([
            'total_approved' => $approvedItems->count(),
            'synced' => $syncedCount,
            'pending_sync' => $pendingSyncCount,
        ]);
    }

    // Export schedule to Excel
    public function exportSchedule(StockOpnameSchedule $schedule)
    {
        $schedule->load(['createdByUser', 'userAssignments.user']);
        $items = $schedule->items()->get();
        $analytics = $schedule->getAnalytics();

        // Generate filename
        $filename = 'Stock_Opname_' . $schedule->schedule_code . '_' . now()->format('Y-m-d_His') . '.xlsx';

        // Create spreadsheet
        return response()->streamDownload(function() use ($schedule, $items, $analytics) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header styling
            $headerStyle = [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ];

            // Title section
            $sheet->setCellValue('A1', 'STOCK OPNAME REPORT');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->mergeCells('A1:K1');

            // Schedule info
            $row = 3;
            $sheet->setCellValue('A' . $row, 'Schedule Code:');
            $sheet->setCellValue('B' . $row, $schedule->schedule_code);
            $row++;
            $sheet->setCellValue('A' . $row, 'Date:');
            $sheet->setCellValue('B' . $row, $schedule->start_date->format('Y-m-d'));
            $row++;
            $sheet->setCellValue('A' . $row, 'Status:');
            $sheet->setCellValue('B' . $row, ucfirst($schedule->status));
            $row++;
            $sheet->setCellValue('A' . $row, 'Progress:');
            $sheet->setCellValue('B' . $row, $schedule->completed_items . '/' . $schedule->total_items . ' (' . $schedule->getProgressPercentage() . '%)');
            $row += 2;

            // Analytics summary
            $sheet->setCellValue('A' . $row, 'ANALYTICS SUMMARY');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
            $row++;

            $sheet->setCellValue('A' . $row, 'Accuracy Rate:');
            $sheet->setCellValue('B' . $row, $analytics['discrepancy']['accuracy_rate'] . '%');
            $row++;
            $sheet->setCellValue('A' . $row, 'Items with Discrepancy:');
            $sheet->setCellValue('B' . $row, $analytics['discrepancy']['items_with_discrepancy'] . ' / ' . $analytics['discrepancy']['total_items']);
            $row++;
            $sheet->setCellValue('A' . $row, 'Net Value Impact:');
            $sheet->setCellValue('B' . $row, 'Rp ' . number_format(abs($analytics['discrepancy']['total_negative_value'] - $analytics['discrepancy']['total_positive_value']), 0, ',', '.'));
            $row += 2;

            // Items table header
            $sheet->setCellValue('A' . $row, 'No');
            $sheet->setCellValue('B' . $row, 'Item Code');
            $sheet->setCellValue('C' . $row, 'Item Name');
            $sheet->setCellValue('D' . $row, 'Type');
            $sheet->setCellValue('E' . $row, 'System Qty');
            $sheet->setCellValue('F' . $row, 'Physical Qty');
            $sheet->setCellValue('G' . $row, 'Discrepancy');
            $sheet->setCellValue('H' . $row, 'Exec Status');
            $sheet->setCellValue('I' . $row, 'Review Status');
            $sheet->setCellValue('J' . $row, 'Executed By');
            $sheet->setCellValue('K' . $row, 'Notes');
            $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray($headerStyle);
            $row++;

            // Items data
            $no = 1;
            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $item->getItemCode());
                $sheet->setCellValue('C' . $row, $item->getItemName());
                $sheet->setCellValue('D' . $row, ucfirst($item->item_type));
                $sheet->setCellValue('E' . $row, $item->system_quantity ?? '-');
                $sheet->setCellValue('F' . $row, $item->physical_quantity ?? '-');
                $sheet->setCellValue('G' . $row, $item->discrepancy_qty ?? 0);
                $sheet->setCellValue('H' . $row, ucfirst(str_replace('_', ' ', $item->execution_status)));
                $sheet->setCellValue('I' . $row, ucfirst(str_replace('_', ' ', $item->review_status)));
                $sheet->setCellValue('J' . $row, $item->executedByUser->name ?? '-');
                $sheet->setCellValue('K' . $row, $item->notes ?? '-');

                // Color code discrepancies
                if ($item->hasDiscrepancy()) {
                    $color = $item->discrepancy_qty > 0 ? 'C8E6C9' : 'FFCDD2';
                    $sheet->getStyle('G' . $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($color);
                }

                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Output
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Close the ticket (mark schedule as completed and closed)
     */
    public function closeTicket(StockOpnameSchedule $schedule)
    {
        // Check if can be closed
        if (!$schedule->canBeClosed()) {
            return redirect()->back()->with('error', 'Cannot close ticket. Not all items have been completed.');
        }

        // Close the ticket
        $success = $schedule->closeTicket(auth()->id());

        if ($success) {
            return redirect()->route('admin.opname.compliance.show', $schedule)
                ->with('success', 'Ticket has been closed successfully and moved to Compliance Report.');
        }

        return redirect()->back()->with('error', 'Failed to close ticket.');
    }
}
