<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupAsset;
use Illuminate\Http\Request;

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
}
