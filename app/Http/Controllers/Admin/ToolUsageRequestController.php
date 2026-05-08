<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ToolRequestApproved;
use App\Mail\ToolRequestInUse;
use App\Mail\ToolRequestRejected;
use App\Mail\ToolRequestReturned;
use App\Models\ToolUsageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        $toolRequest->update([
            'status'       => 'in_use',
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
            'review_notes' => $request->review_notes,
        ]);

        $tool->decrement('quantity', $toolRequest->quantity_requested);

        $toolRequest->load('requester', 'reviewer');
        if ($toolRequest->requester?->email) {
            Mail::to($toolRequest->requester->email)->queue(new ToolRequestApproved($toolRequest));
            Mail::to($toolRequest->requester->email)->queue(new ToolRequestInUse($toolRequest));
        }

        return back()->with('success', 'Request approved. Stock deducted — tool is now in use.');
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

        $toolRequest->load('requester', 'reviewer');
        if ($toolRequest->requester?->email) {
            Mail::to($toolRequest->requester->email)->queue(new ToolRequestRejected($toolRequest));
        }

        return back()->with('success', 'Request rejected.');
    }


    // Admin/supervisor can also mark a borrowed tool as returned
    public function markReturned(Request $request, ToolUsageRequest $toolRequest)
    {
        if (!in_array($toolRequest->status, ['approved', 'in_use'])) {
            return back()->with('error', 'Only approved or in-use requests can be marked as returned.');
        }

        $toolRequest->load('tool');

        $request->validate([
            'return_notes' => 'nullable|string|max:1000',
        ]);

        $wasInUse = $toolRequest->status === 'in_use';

        $toolRequest->update([
            'status'       => 'returned',
            'returned_at'  => now(),
            'return_notes' => $request->return_notes,
        ]);

        if ($wasInUse) {
            $toolRequest->tool->increment('quantity', $toolRequest->quantity_requested);
        }

        $toolRequest->load('requester');
        if ($toolRequest->requester?->email) {
            Mail::to($toolRequest->requester->email)->queue(new ToolRequestReturned($toolRequest));
        }

        return back()->with('success', 'Tool marked as returned.' . ($wasInUse ? ' Stock restored.' : ''));
    }
}
