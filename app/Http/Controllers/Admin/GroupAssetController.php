<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupAssetController extends Controller
{
    public function index()
    {
        $groups = GroupAsset::with(['creator', 'updater'])
            ->orderByRaw('CAST(SUBSTRING(group_id, 2) AS UNSIGNED) ASC')
            ->get();

        return view('admin.group-assets.index', compact('groups'));
    }

    public function create()
    {
        $severityLabels = GroupAsset::severityLabels();
        $nextGroupId    = GroupAsset::generateGroupId();

        return view('admin.group-assets.create', compact('severityLabels', 'nextGroupId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_id'   => 'nullable|string|max:10|unique:group_assets,group_id',
            'group_name' => 'required|string|max:255',
            'severity'   => 'required|in:high,medium,low',
        ]);

        GroupAsset::create($validated);

        return redirect()->route('admin.group-assets.index')
            ->with('success', 'Group created successfully.');
    }

    public function show(GroupAsset $groupAsset)
    {
        $groupAsset->load(['creator', 'updater']);

        return view('admin.group-assets.show', compact('groupAsset'));
    }

    public function edit(GroupAsset $groupAsset)
    {
        $severityLabels = GroupAsset::severityLabels();

        return view('admin.group-assets.edit', compact('groupAsset', 'severityLabels'));
    }

    public function update(Request $request, GroupAsset $groupAsset)
    {
        $validated = $request->validate([
            'group_id'   => 'required|string|max:10|unique:group_assets,group_id,' . $groupAsset->id,
            'group_name' => 'required|string|max:255',
            'severity'   => 'required|in:high,medium,low',
        ]);

        $groupAsset->update($validated);

        return redirect()->route('admin.group-assets.index')
            ->with('success', 'Group updated successfully.');
    }

    public function destroy(GroupAsset $groupAsset)
    {
        $groupAsset->delete();

        return redirect()->route('admin.group-assets.index')
            ->with('success', 'Group deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file     = $request->file('csv_file');
        $handle   = fopen($file->getRealPath(), 'r');
        $header   = null;
        $imported = 0;
        $skipped  = 0;
        $errors   = [];
        $validSeverities = ['high', 'medium', 'low'];

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            // Skip header row
            if ($header === null) {
                $header = array_map('trim', $row);
                continue;
            }

            if (count($row) < 3) continue;

            $groupId   = trim($row[0]);
            $groupName = trim($row[1]);
            $severity  = strtolower(trim($row[2]));

            if (empty($groupId) || empty($groupName)) continue;

            if (!in_array($severity, $validSeverities)) {
                $errors[] = "Row skipped — invalid severity '{$row[2]}' for GroupID: {$groupId}";
                $skipped++;
                continue;
            }

            // Upsert: update if group_id exists, insert if not
            $existing = GroupAsset::withTrashed()->where('group_id', $groupId)->first();
            if ($existing) {
                $existing->restore();
                $existing->update([
                    'group_name' => $groupName,
                    'severity'   => $severity,
                    'updated_by' => auth()->id(),
                ]);
                $imported++;
            } else {
                GroupAsset::create([
                    'group_id'   => $groupId,
                    'group_name' => $groupName,
                    'severity'   => $severity,
                ]);
                $imported++;
            }
        }

        fclose($handle);

        $message = "Import complete: {$imported} groups imported/updated.";
        if ($skipped > 0) {
            $message .= " {$skipped} rows skipped.";
        }

        return redirect()->route('admin.group-assets.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }
}
