<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SiteManagementController extends Controller
{
    /**
     * Display a listing of sites.
     */
    public function index()
    {
        $sites = Site::on('central')->orderBy('name')->get();

        return view('admin.sites.index', compact('sites'));
    }

    /**
     * Show the form for creating a new site.
     */
    public function create()
    {
        return view('admin.sites.create');
    }

    /**
     * Store a newly created site.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:central.sites,code|regex:/^[a-z0-9_]+$/',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // Generate database name from code
        $databaseName = 'warehouse_' . $validated['code'];

        try {
            DB::beginTransaction();

            // Create site record in central database
            $site = Site::on('central')->create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'database_name' => $databaseName,
                'description' => $validated['description'],
                'is_active' => true,
            ]);

            // Create the database
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}`");

            // Configure site connection
            Config::set('database.connections.site.database', $databaseName);
            DB::purge('site');
            DB::reconnect('site');

            // Run migrations on the new database
            Artisan::call('migrate', [
                '--database' => 'site',
                '--force' => true,
            ]);

            // Create roles in the new database
            $this->createRolesInSiteDatabase();

            DB::commit();

            return redirect()
                ->route('admin.sites.index')
                ->with('success', "Site '{$site->name}' created successfully with database '{$databaseName}'! Central admins can now access this site.");

        } catch (\Exception $e) {
            DB::rollBack();

            // Try to clean up the database if it was created
            try {
                DB::statement("DROP DATABASE IF EXISTS `{$databaseName}`");
            } catch (\Exception $cleanupException) {
                // Ignore cleanup errors
            }

            return back()
                ->withInput()
                ->with('error', 'Failed to create site: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing a site.
     */
    public function edit(string $id)
    {
        $site = Site::on('central')->findOrFail($id);

        return view('admin.sites.edit', compact('site'));
    }

    /**
     * Update the specified site.
     */
    public function update(Request $request, string $id)
    {
        $site = Site::on('central')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $site->update($validated);

        return redirect()
            ->route('admin.sites.index')
            ->with('success', "Site '{$site->name}' updated successfully!");
    }

    /**
     * Toggle site active status.
     */
    public function toggleStatus(string $id)
    {
        $site = Site::on('central')->findOrFail($id);
        $site->is_active = !$site->is_active;
        $site->save();

        $status = $site->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Site '{$site->name}' has been {$status}.");
    }

    /**
     * Run migrations for a specific site.
     */
    public function migrate(string $id)
    {
        $site = Site::on('central')->findOrFail($id);

        try {
            // Configure site connection
            Config::set('database.connections.site.database', $site->database_name);
            DB::purge('site');
            DB::reconnect('site');

            // Run migrations
            Artisan::call('migrate', [
                '--database' => 'site',
                '--force' => true,
            ]);

            // Ensure roles exist
            $this->createRolesInSiteDatabase();

            return back()->with('success', "Migrations completed for site '{$site->name}'.");

        } catch (\Exception $e) {
            return back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }

    /**
     * Create roles in site database.
     */
    protected function createRolesInSiteDatabase(): void
    {
        $roles = ['admin', 'supervisor_maintenance', 'staff_maintenance'];

        foreach ($roles as $roleName) {
            DB::connection('site')->table('roles')->insertOrIgnore([
                'name' => $roleName,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
