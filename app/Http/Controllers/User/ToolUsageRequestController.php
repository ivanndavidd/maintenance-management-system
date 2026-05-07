<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use App\Models\ToolUsageRequest;
use Illuminate\Http\Request;

class ToolUsageRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ToolUsageRequest::where('requested_by', auth()->id())
            ->with('tool');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderByDesc('created_at')->paginate(15)->appends($request->except('page'));

        $base = fn() => ToolUsageRequest::where('requested_by', auth()->id());
        $stats = [
            'total'    => $base()->count(),
            'pending'  => $base()->where('status', 'pending')->count(),
            'approved' => $base()->where('status', 'approved')->count(),
            'in_use'   => $base()->where('status', 'in_use')->count(),
        ];

        return view('user.tool-requests.index', compact('requests', 'stats'));
    }

    public function create()
    {
        $tools = Tool::where('quantity', '>', 0)->orderBy('equipment_type')->orderBy('sparepart_name')->get();
        return view('user.tool-requests.create', compact('tools'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tool_id'            => 'required|exists:tools,id',
            'quantity_requested' => 'required|integer|min:1',
            'usage_date'         => 'required|date|after_or_equal:today',
            'return_date'        => 'nullable|date|after_or_equal:usage_date',
            'purpose'            => 'required|string|max:500',
            'location'           => 'nullable|string|max:255',
            'notes'              => 'nullable|string|max:1000',
        ]);

        $tool = Tool::findOrFail($request->tool_id);

        if ($request->quantity_requested > $tool->quantity) {
            return back()->withInput()->withErrors([
                'quantity_requested' => "Only {$tool->quantity} {$tool->unit} available in stock.",
            ]);
        }

        // For consumable, return_date is not applicable
        $isConsumable = strtolower($tool->equipment_type) === 'consumable';

        ToolUsageRequest::create([
            'request_number'     => ToolUsageRequest::generateRequestNumber(),
            'tool_id'            => $request->tool_id,
            'requested_by'       => auth()->id(),
            'quantity_requested' => $request->quantity_requested,
            'usage_date'         => $request->usage_date,
            'return_date'        => $isConsumable ? null : $request->return_date,
            'purpose'            => $request->purpose,
            'location'           => $request->location,
            'notes'              => $request->notes,
            'status'             => 'pending',
        ]);

        $user = auth()->user();
        $prefix = ($user->hasRole('supervisor_maintenance') || $user->hasRole('admin')) ? 'supervisor' : 'user';
        if ($user->hasRole('admin')) $prefix = 'admin';

        return redirect()->route($prefix . '.tool-requests.index')
            ->with('success', 'Tool usage request submitted. Waiting for approval.');
    }

    public function show(ToolUsageRequest $toolRequest)
    {
        if ($toolRequest->requested_by !== auth()->id()) {
            abort(403);
        }

        $toolRequest->load(['tool', 'requester', 'reviewer']);

        return view('user.tool-requests.show', compact('toolRequest'));
    }

    public function cancel(ToolUsageRequest $toolRequest)
    {
        if ($toolRequest->requested_by !== auth()->id()) {
            abort(403);
        }

        if ($toolRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        $toolRequest->update(['status' => 'cancelled']);

        return back()->with('success', 'Request cancelled.');
    }

    // For non-consumable (Tools): mark returned, stock goes back
    public function markReturned(Request $request, ToolUsageRequest $toolRequest)
    {
        if ($toolRequest->requested_by !== auth()->id()) {
            abort(403);
        }

        $toolRequest->load('tool');

        if (!$toolRequest->canBeMarkedReturned()) {
            return back()->with('error', 'This request cannot be marked as returned.');
        }

        $request->validate([
            'return_notes' => 'nullable|string|max:1000',
        ]);

        $toolRequest->update([
            'status'       => 'returned',
            'returned_at'  => now(),
            'return_notes' => $request->return_notes,
        ]);

        // Return stock for non-consumable
        $toolRequest->tool->increment('quantity', $toolRequest->quantity_requested);

        return back()->with('success', 'Tool marked as returned. Stock has been updated.');
    }

    // For consumable: mark as used (stock already deducted when approved)
    public function markUsed(Request $request, ToolUsageRequest $toolRequest)
    {
        if ($toolRequest->requested_by !== auth()->id()) {
            abort(403);
        }

        $toolRequest->load('tool');

        if (!$toolRequest->canBeMarkedUsed()) {
            return back()->with('error', 'This request cannot be marked as used.');
        }

        $request->validate([
            'return_notes' => 'nullable|string|max:1000',
        ]);

        $toolRequest->update([
            'status'       => 'used',
            'returned_at'  => now(),
            'return_notes' => $request->return_notes,
        ]);

        return back()->with('success', 'Consumable marked as used.');
    }
}
