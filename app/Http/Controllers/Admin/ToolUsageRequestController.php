<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ToolUsageRequest;
use Illuminate\Http\Request;

class ToolUsageRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ToolUsageRequest::with(['tool', 'requester']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('requester', fn($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('tool', fn($t) => $t->where('sparepart_name', 'like', "%{$search}%"));
            });
        }

        $requests = $query->orderByDesc('created_at')->paginate(20)->appends($request->except('page'));

        $stats = [
            'pending'  => ToolUsageRequest::where('status', 'pending')->count(),
            'approved' => ToolUsageRequest::where('status', 'approved')->count(),
            'in_use'   => ToolUsageRequest::where('status', 'in_use')->count(),
            'total'    => ToolUsageRequest::count(),
        ];

        return view('admin.tool-requests.index', compact('requests', 'stats'));
    }

    public function show(ToolUsageRequest $toolRequest)
    {
        $toolRequest->load(['tool', 'requester', 'reviewer']);
        return view('admin.tool-requests.show', compact('toolRequest'));
    }

    public function approve(Request $request, ToolUsageRequest $toolRequest)
    {
        if ($toolRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $toolRequest->load('tool');
        $tool = $toolRequest->tool;

        if ($toolRequest->quantity_requested > $tool->quantity) {
            return back()->with('error', "Insufficient stock. Available: {$tool->quantity} {$tool->unit}.");
        }

        $isConsumable = strtolower($tool->equipment_type) === 'consumable';

        $toolRequest->update([
            'status'       => $isConsumable ? 'approved' : 'approved',
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
            'review_notes' => $request->review_notes,
        ]);

        // Consumable: deduct stock immediately on approval
        if ($isConsumable) {
            $tool->decrement('quantity', $toolRequest->quantity_requested);
        }

        return back()->with('success', 'Request approved.' . ($isConsumable ? ' Stock has been deducted.' : ' User can now pick up the tool.'));
    }

    public function reject(Request $request, ToolUsageRequest $toolRequest)
    {
        if ($toolRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $request->validate([
            'review_notes' => 'required|string|max:1000',
        ]);

        $toolRequest->update([
            'status'       => 'rejected',
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
            'review_notes' => $request->review_notes,
        ]);

        return back()->with('success', 'Request rejected.');
    }

    public function markInUse(ToolUsageRequest $toolRequest)
    {
        if ($toolRequest->status !== 'approved') {
            return back()->with('error', 'Only approved requests can be marked as in use.');
        }

        $toolRequest->load('tool');

        if (strtolower($toolRequest->tool->equipment_type) === 'consumable') {
            return back()->with('error', 'Consumable items do not have an in-use state.');
        }

        $toolRequest->tool->decrement('quantity', $toolRequest->quantity_requested);
        $toolRequest->update(['status' => 'in_use']);

        return back()->with('success', 'Tool marked as in use. Stock deducted.');
    }

    // Admin/supervisor can also mark a borrowed tool as returned
    public function markReturned(Request $request, ToolUsageRequest $toolRequest)
    {
        if (!in_array($toolRequest->status, ['approved', 'in_use'])) {
            return back()->with('error', 'Only approved or in-use requests can be marked as returned.');
        }

        $toolRequest->load('tool');

        if (strtolower($toolRequest->tool->equipment_type) === 'consumable') {
            return back()->with('error', 'Consumable items cannot be returned.');
        }

        $request->validate([
            'return_notes' => 'nullable|string|max:1000',
        ]);

        $wasInUse = $toolRequest->status === 'in_use';

        $toolRequest->update([
            'status'       => 'returned',
            'returned_at'  => now(),
            'return_notes' => $request->return_notes,
        ]);

        // Restore stock only if it was already deducted (in_use)
        if ($wasInUse) {
            $toolRequest->tool->increment('quantity', $toolRequest->quantity_requested);
        }

        return back()->with('success', 'Tool marked as returned. Stock restored.');
    }
}
