<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use App\Models\SparepartUsage;
use Illuminate\Http\Request;

class SparepartUsageController extends Controller
{
    public function index(Request $request)
    {
        $query = SparepartUsage::with(['sparepart', 'usedByUser', 'pmReport.task', 'cmTicket'])
            ->where(function ($q) {
                $q->whereNull('pm_report_id')
                  ->orWhereExists(function ($sub) {
                      $sub->selectRaw('1')
                          ->from('pm_task_reports')
                          ->whereColumn('pm_task_reports.id', 'sparepart_usages.pm_report_id')
                          ->where('pm_task_reports.sparepart_approval_status', 'approved');
                  });
            })
            ->latest('used_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('sparepart', function ($q) use ($search) {
                $q->where('sparepart_name', 'LIKE', "%{$search}%")
                  ->orWhere('material_code', 'LIKE', "%{$search}%");
            })->orWhere('notes', 'LIKE', "%{$search}%");
        }

        if ($request->filled('sparepart_id')) {
            $query->where('sparepart_id', $request->sparepart_id);
        }

        $usages = $query->paginate(20)->appends($request->except('page'));
        $spareparts = Sparepart::orderBy('sparepart_name')->get();
        $routePrefix = auth()->user()->hasRole('supervisor_maintenance') ? 'supervisor' : 'admin';

        return view('admin.sparepart-usage.index', compact('usages', 'spareparts', 'routePrefix'));
    }

    public function create()
    {
        $spareparts = Sparepart::where('quantity', '>', 0)->orderBy('sparepart_name')->get();
        return view('admin.sparepart-usage.create', compact('spareparts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sparepart_id'  => 'required|exists:spareparts,id',
            'quantity_used' => 'required|integer|min:1',
            'used_at'       => 'required|date',
            'notes'         => 'nullable|string|max:500',
        ]);

        $sparepart = Sparepart::findOrFail($validated['sparepart_id']);

        if ($validated['quantity_used'] > $sparepart->quantity) {
            return back()->withErrors(['quantity_used' => 'Quantity used cannot exceed current stock (' . $sparepart->quantity . ' ' . $sparepart->unit . ')'])->withInput();
        }

        SparepartUsage::create([
            'sparepart_id'  => $validated['sparepart_id'],
            'quantity_used' => $validated['quantity_used'],
            'used_at'       => $validated['used_at'],
            'notes'         => $validated['notes'] ?? null,
            'used_by'       => auth()->id(),
        ]);

        // Deduct from stock
        $sparepart->decrement('quantity', $validated['quantity_used']);

        return redirect()->route($this->getRoutePrefix() . '.sparepart-usage.index')
            ->with('success', 'Sparepart usage recorded successfully.');
    }

    public function destroy(SparepartUsage $sparepartUsage)
    {
        if (!auth()->user()->isSuper()) {
            abort(403, 'Only super admin can delete sparepart usage records.');
        }

        // Restore stock
        $sparepartUsage->sparepart->increment('quantity', $sparepartUsage->quantity_used);
        $sparepartUsage->delete();

        return back()->with('success', 'Usage record deleted and stock restored.');
    }

    private function getRoutePrefix(): string
    {
        return auth()->user()->hasRole('admin') ? 'admin' : 'supervisor';
    }
}
