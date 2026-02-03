<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middleware\RoleMiddleware;

// ========================================
// Health Check Route (for Docker/monitoring)
// ========================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// ========================================
// TEMPORARY: Force logout via GET
// Remove this after testing/deployment
// ========================================
Route::get('/force-logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login')->with('success', 'All sessions cleared successfully!');
})->name('force-logout');

// ========================================
// Authentication Routes
// ========================================
Auth::routes();

// Email Verification Routes
Route::get('/verify-email', [App\Http\Controllers\Auth\RegisterController::class, 'showVerifyCodeForm'])->name('verify.code.form');
Route::post('/verify-email', [App\Http\Controllers\Auth\RegisterController::class, 'verifyCode'])->name('verify.code');
Route::post('/verify-email/resend', [App\Http\Controllers\Auth\RegisterController::class, 'resendCode'])->name('verify.resend');

// Auth pending/waiting page
Route::get('/auth/pending', function () {
    $user = session('user') ?? auth()->user();

    if (!$user) {
        return redirect()->route('login')
            ->with('info', 'Please login or register to continue.');
    }

    return view('auth.pending', compact('user'));
})->name('auth.pending');

// ========================================
// PUBLIC MAINTENANCE REQUEST (No Login Required)
// ========================================
Route::prefix('maintenance-request')
    ->name('maintenance-request.')
    ->group(function () {
        // Public form to create maintenance request
        Route::get('/', [App\Http\Controllers\CorrectiveMaintenanceController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\CorrectiveMaintenanceController::class, 'store'])->name('store');

        // Success page after submission
        Route::get('/success/{ticket}', [App\Http\Controllers\CorrectiveMaintenanceController::class, 'success'])->name('success');

        // Track ticket status
        Route::get('/track', [App\Http\Controllers\CorrectiveMaintenanceController::class, 'track'])->name('track');
        Route::get('/track/search', [App\Http\Controllers\CorrectiveMaintenanceController::class, 'trackSearch'])->name('track.search');
    });

// ========================================
// Root & Dashboard Redirects
// ========================================

// Redirect root based on auth status
Route::get('/', function () {
    if (Auth::check()) {
        // If already logged in, redirect to dashboard
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->hasRole('supervisor_maintenance')) {
            return redirect()->route('supervisor.dashboard');
        } elseif (auth()->user()->hasRole('staff_maintenance')) {
            return redirect()->route('user.dashboard');
        } else {
            // User has no role assigned
            Auth::logout();
            return redirect()
                ->route('login')
                ->with('error', 'No role assigned to your account. Please contact administrator.');
        }
    }
    // If not logged in, go to login page
    return redirect()->route('login');
});

// After login redirect based on role
Route::get('/dashboard', function () {
    // Check if user is authenticated
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    // Redirect based on role
    if (auth()->user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } elseif (auth()->user()->hasRole('supervisor_maintenance')) {
        return redirect()->route('supervisor.dashboard');
    } elseif (auth()->user()->hasRole('staff_maintenance')) {
        return redirect()->route('user.dashboard');
    } else {
        // User has no role, logout and redirect to login
        Auth::logout();
        return redirect()
            ->route('login')
            ->with('error', 'Your account has no assigned role. Please contact administrator.');
    }
})
    ->middleware('auth')
    ->name('dashboard');

// ========================================
// Protected Routes
// ========================================
Route::middleware(['auth'])->group(function () {
    // ========================================
    // ADMIN ROUTES
    // ========================================
    Route::prefix('admin')
        ->name('admin.')
        ->middleware([RoleMiddleware::class . ':admin|supervisor_maintenance'])
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [
                App\Http\Controllers\Admin\DashboardController::class,
                'index',
            ])->name('dashboard');

            // User Management
            Route::resource('users', App\Http\Controllers\Admin\UserController::class);
            Route::patch('users/{user}/toggle-status', [
                App\Http\Controllers\Admin\UserController::class,
                'toggleStatus',
            ])->name('users.toggle-status');
            Route::post('users/{user}/reset-password', [
                App\Http\Controllers\Admin\UserController::class,
                'resetPassword',
            ])->name('users.reset-password');

            // Shift Management
            Route::resource('shifts', App\Http\Controllers\Admin\ShiftController::class);
            Route::post('shifts/{shift}/assign-user', [
                App\Http\Controllers\Admin\ShiftController::class,
                'assignUser',
            ])->name('shifts.assign-user');
            Route::post('shifts/{shift}/remove-user', [
                App\Http\Controllers\Admin\ShiftController::class,
                'removeUser',
            ])->name('shifts.remove-user');
            Route::post('shifts/{shift}/assign-user-hourly', [
                App\Http\Controllers\Admin\ShiftController::class,
                'assignUserHourly',
            ])->name('shifts.assign-user-hourly');
            Route::post('shifts/{shift}/remove-assignment', [
                App\Http\Controllers\Admin\ShiftController::class,
                'removeAssignment',
            ])->name('shifts.remove-assignment');
            Route::post('shifts/{shift}/remove-assignments', [
                App\Http\Controllers\Admin\ShiftController::class,
                'removeAssignments',
            ])->name('shifts.remove-assignments');
            Route::post('shifts/{shift}/clear-all-assignments', [
                App\Http\Controllers\Admin\ShiftController::class,
                'clearAllAssignments',
            ])->name('shifts.clear-all-assignments');
            Route::patch('shifts/{shift}/activate', [
                App\Http\Controllers\Admin\ShiftController::class,
                'activate',
            ])->name('shifts.activate');
            Route::get('shifts/get-shift-for-date', [
                App\Http\Controllers\Admin\ShiftController::class,
                'getShiftForDate',
            ])->name('shifts.get-shift-for-date');
            Route::get('shifts/{shift}/day-details', [
                App\Http\Controllers\Admin\ShiftController::class,
                'getDayDetails',
            ])->name('shifts.day-details');
            Route::post('shifts/change-assignment', [
                App\Http\Controllers\Admin\ShiftController::class,
                'changeAssignment',
            ])->name('shifts.change-assignment');

            // Maintenance Jobs
            Route::resource('jobs', App\Http\Controllers\Admin\MaintenanceJobController::class);
            Route::patch('jobs/{job}/update-status', [
                App\Http\Controllers\Admin\MaintenanceJobController::class,
                'updateStatus',
            ])->name('jobs.update-status');

            // Work Reports
            Route::prefix('work-reports')
                ->name('work-reports.')
                ->group(function () {
                    // List semua laporan
                    Route::get('/', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'index',
                    ])->name('index');

                    // Laporan saya (filter by user)
                    Route::get('/my-reports', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'myReports',
                    ])->name('my-reports');

                    // Form create laporan baru
                    Route::get('/create', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'create',
                    ])->name('create');

                    // Simpan laporan baru
                    Route::post('/', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'store',
                    ])->name('store');

                    // Detail laporan
                    Route::get('/{workReport}', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'show',
                    ])->name('show');

                    // Edit laporan
                    Route::get('/{workReport}/edit', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'edit',
                    ])->name('edit');

                    // Update laporan
                    Route::put('/{workReport}', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'update',
                    ])->name('update');

                    // Hapus laporan
                    Route::delete('/{workReport}', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'destroy',
                    ])->name('destroy');

                    // Validasi laporan
                    Route::patch('/{workReport}/validate', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'validateReport',
                    ])->name('validate');

                    // Hapus lampiran tertentu dari laporan
                    Route::delete('/{workReport}/attachment/{index}', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'deleteAttachment',
                    ])->name('delete-attachment');

                    // ✅ Approve laporan kerja
                    Route::post('/{workReport}/approve', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'approve',
                    ])->name('approve');

                    // ✅ Reject laporan kerja
                    Route::post('/{workReport}/reject', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'reject',
                    ])->name('reject');
                });

            // Help Articles Management
            Route::resource('help-articles', App\Http\Controllers\Admin\HelpArticleController::class);
            Route::patch('help-articles/{helpArticle}/toggle-publish', [
                App\Http\Controllers\Admin\HelpArticleController::class,
                'togglePublish',
            ])->name('help-articles.toggle-publish');

            // KPI Management
            Route::prefix('kpi')
                ->name('kpi.')
                ->group(function () {
                    Route::get('/', [
                        App\Http\Controllers\Admin\KpiController::class,
                        'index',
                    ])->name('index');

                    Route::get('/{user}', [
                        App\Http\Controllers\Admin\KpiController::class,
                        'show',
                    ])->name('show');
                });

            // Inventory Management - Spareparts
            // IMPORTANT: Custom routes must come BEFORE Route::resource to avoid conflicts
            Route::prefix('spareparts')->name('spareparts.')->group(function () {
                // Excel Import (must be before resource routes)
                Route::get('/import', [App\Http\Controllers\Admin\SparepartController::class, 'showImportForm'])->name('import');
                Route::post('/import', [App\Http\Controllers\Admin\SparepartController::class, 'import'])->name('import.process');
                Route::get('/import/template', [App\Http\Controllers\Admin\SparepartController::class, 'downloadTemplate'])->name('import.template');

                // Purchase Orders
                Route::get('/purchase-orders', [App\Http\Controllers\Admin\SparepartController::class, 'purchaseOrders'])->name('purchase-orders');
                Route::get('/purchase-orders/create', [App\Http\Controllers\Admin\SparepartController::class, 'createPurchaseOrder'])->name('purchase-orders.create');
                Route::post('/purchase-orders', [App\Http\Controllers\Admin\SparepartController::class, 'storePurchaseOrder'])->name('purchase-orders.store');
                Route::post('/purchase-orders/{purchaseOrder}/receive', [App\Http\Controllers\Admin\SparepartController::class, 'receivePurchaseOrder'])->name('purchase-orders.receive');

                // Stock Opname
                Route::get('/opname/dashboard', [App\Http\Controllers\Admin\SparepartController::class, 'opnameDashboard'])->name('opname.dashboard');
                Route::get('/opname/schedules', [App\Http\Controllers\Admin\SparepartController::class, 'opnameSchedules'])->name('opname.schedules');
                Route::get('/opname/schedules/create', [App\Http\Controllers\Admin\SparepartController::class, 'createOpnameSchedule'])->name('opname.schedules.create');
                Route::post('/opname/schedules', [App\Http\Controllers\Admin\SparepartController::class, 'storeOpnameSchedule'])->name('opname.schedules.store');
                Route::get('/opname/executions', [App\Http\Controllers\Admin\SparepartController::class, 'opnameExecutions'])->name('opname.executions');
                Route::get('/opname/executions/create', [App\Http\Controllers\Admin\SparepartController::class, 'createOpnameExecution'])->name('opname.executions.create');
                Route::post('/opname/executions', [App\Http\Controllers\Admin\SparepartController::class, 'storeOpnameExecution'])->name('opname.executions.store');
                Route::get('/opname/reports/compliance', [App\Http\Controllers\Admin\SparepartController::class, 'opnameComplianceReport'])->name('opname.compliance-report');
                Route::get('/opname/reports/accuracy', [App\Http\Controllers\Admin\SparepartController::class, 'opnameAccuracyReport'])->name('opname.accuracy-report');

                // Stock Adjustments
                Route::get('/adjustments', [App\Http\Controllers\Admin\SparepartController::class, 'adjustments'])->name('adjustments');
                Route::get('/adjustments/create', [App\Http\Controllers\Admin\SparepartController::class, 'createAdjustment'])->name('adjustments.create');
                Route::post('/adjustments', [App\Http\Controllers\Admin\SparepartController::class, 'storeAdjustment'])->name('adjustments.store');
            });

            // Resource routes (must come AFTER custom routes)
            Route::resource('spareparts', App\Http\Controllers\Admin\SparepartController::class);

            // Tools import route (must come BEFORE resource route)
            Route::post('tools/import', [App\Http\Controllers\Admin\ToolController::class, 'import'])->name('tools.import');

            Route::resource('tools', App\Http\Controllers\Admin\ToolController::class);

            // Assets Management
            // IMPORTANT: Custom routes must come BEFORE Route::resource to avoid conflicts
            Route::prefix('assets')->name('assets.')->group(function () {
                // Excel Import (must be before resource routes)
                Route::get('/import', [App\Http\Controllers\Admin\AssetController::class, 'showImport'])->name('import');
                Route::post('/import', [App\Http\Controllers\Admin\AssetController::class, 'import'])->name('import.process');
                Route::get('/import/template', [App\Http\Controllers\Admin\AssetController::class, 'downloadTemplate'])->name('import.template');
            });

            // Resource routes (must come AFTER custom routes)
            Route::resource('assets', App\Http\Controllers\Admin\AssetController::class);

            // Purchase Orders (Multi-Item Shopping Cart System)
            Route::prefix('purchase-orders')
                ->name('purchase-orders.')
                ->group(function () {
                    Route::get('/', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'index'])->name('index');
                    Route::get('/create', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'create'])->name('create');
                    Route::post('/', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'store'])->name('store');
                    Route::get('/{purchaseOrder}', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'show'])->name('show');

                    // Approval workflow
                    Route::post('/{purchaseOrder}/approve', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'approve'])->name('approve');
                    Route::post('/{purchaseOrder}/reject', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'reject'])->name('reject');

                    // Goods receiving (item-level)
                    Route::get('/{purchaseOrder}/receive', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'showReceiveForm'])->name('receive');
                    Route::post('/{purchaseOrder}/items/{item}/receive', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'receiveItem'])->name('receive-item');
                    Route::post('/batch-receive', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'batchReceive'])->name('batch-receive');

                    // Quality inspection (item-level)
                    Route::post('/{purchaseOrder}/items/{item}/compliance', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'markItemCompliance'])->name('mark-item-compliance');
                    Route::post('/{purchaseOrder}/items/{item}/mark-compliant', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'markCompliant'])->name('mark-compliant');
                    Route::post('/{purchaseOrder}/items/{item}/mark-non-compliant', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'markNonCompliant'])->name('mark-non-compliant');
                    Route::post('/{purchaseOrder}/items/{item}/reverse-compliance', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'reverseCompliance'])->name('reverse-compliance');

                    // Stock management (item-level)
                    Route::post('/{purchaseOrder}/items/{item}/add-to-stock', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'addItemToStock'])->name('add-item-to-stock');
                    Route::post('/{purchaseOrder}/add-all-to-stock', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'addAllCompliantToStock'])->name('add-all-to-stock');

                    // Return and reorder (copies all items)
                    Route::post('/{purchaseOrder}/return-and-reorder', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'returnAndReorder'])->name('return-and-reorder');

                    // Cancel PO
                    Route::post('/{purchaseOrder}/cancel', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'cancel'])->name('cancel');

                    // Delete PO (Admin only)
                    Route::delete('/{purchaseOrder}', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'destroy'])->name('destroy');
                });

            // Stock Opname
            Route::prefix('opname')
                ->name('opname.')
                ->group(function () {
                    // Dashboard
                    Route::get('/dashboard', [App\Http\Controllers\Admin\StockOpnameController::class, 'dashboard'])->name('dashboard');

                    // Schedules
                    Route::prefix('schedules')->name('schedules.')->group(function () {
                        Route::get('/', [App\Http\Controllers\Admin\StockOpnameController::class, 'scheduleIndex'])->name('index');
                        Route::get('/create', [App\Http\Controllers\Admin\StockOpnameController::class, 'scheduleCreate'])->name('create');
                        Route::post('/', [App\Http\Controllers\Admin\StockOpnameController::class, 'scheduleStore'])->name('store');
                        Route::get('/{schedule}', [App\Http\Controllers\Admin\StockOpnameController::class, 'scheduleShow'])->name('show');
                        Route::get('/{schedule}/edit', [App\Http\Controllers\Admin\StockOpnameController::class, 'scheduleEdit'])->name('edit');
                        Route::put('/{schedule}', [App\Http\Controllers\Admin\StockOpnameController::class, 'scheduleUpdate'])->name('update');

                        // Review actions for discrepancies
                        Route::post('/{schedule}/batch-approve', [App\Http\Controllers\Admin\StockOpnameController::class, 'batchApproveItems'])->name('batch-approve');

                        // Stock sync actions
                        Route::post('/{schedule}/sync-to-stock', [App\Http\Controllers\Admin\StockOpnameController::class, 'syncToStock'])->name('sync-to-stock');
                        Route::get('/{schedule}/sync-status', [App\Http\Controllers\Admin\StockOpnameController::class, 'getSyncStatus'])->name('sync-status');

                        // Export to Excel
                        Route::get('/{schedule}/export', [App\Http\Controllers\Admin\StockOpnameController::class, 'exportSchedule'])->name('export');

                        // Close ticket
                        Route::post('/{schedule}/close-ticket', [App\Http\Controllers\Admin\StockOpnameController::class, 'closeTicket'])->name('close-ticket');
                    });

                    // Item review routes
                    Route::post('/items/{item}/approve', [App\Http\Controllers\Admin\StockOpnameController::class, 'approveItem'])->name('items.approve');
                    Route::post('/items/{item}/reject', [App\Http\Controllers\Admin\StockOpnameController::class, 'rejectItem'])->name('items.reject');

                    // Item stock sync routes
                    Route::post('/items/{item}/sync-to-stock', [App\Http\Controllers\Admin\StockOpnameController::class, 'syncItemToStock'])->name('items.sync-to-stock');

                    // Executions
                    Route::prefix('executions')->name('executions.')->group(function () {
                        Route::get('/', [App\Http\Controllers\Admin\StockOpnameController::class, 'executionIndex'])->name('index');
                        Route::get('/create', [App\Http\Controllers\Admin\StockOpnameController::class, 'executionCreate'])->name('create');
                        Route::post('/', [App\Http\Controllers\Admin\StockOpnameController::class, 'executionStore'])->name('store');
                        Route::get('/{execution}', [App\Http\Controllers\Admin\StockOpnameController::class, 'executionShow'])->name('show');
                        Route::post('/{execution}/verify', [App\Http\Controllers\Admin\StockOpnameController::class, 'executionVerify'])->name('verify');
                    });

                    // Reports
                    Route::prefix('reports')->name('reports.')->group(function () {
                        Route::get('/compliance', [App\Http\Controllers\Admin\StockOpnameController::class, 'complianceReport'])->name('compliance');
                        Route::get('/accuracy', [App\Http\Controllers\Admin\StockOpnameController::class, 'accuracyReport'])->name('accuracy');
                    });

                    // Compliance Reports (Closed Tickets)
                    Route::prefix('compliance')->name('compliance.')->group(function () {
                        Route::get('/', [App\Http\Controllers\Admin\ComplianceReportController::class, 'index'])->name('index');
                        Route::get('/{schedule}', [App\Http\Controllers\Admin\ComplianceReportController::class, 'show'])->name('show');
                    });
                });

            // Stock Adjustments
            Route::prefix('adjustments')
                ->name('adjustments.')
                ->group(function () {
                    Route::get('/', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'index'])->name('index');
                    Route::get('/create', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'create'])->name('create');
                    Route::post('/', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'store'])->name('store');
                    Route::get('/{adjustment}', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'show'])->name('show');
                    Route::post('/{adjustment}/approve', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'approve'])->name('approve');
                    Route::post('/{adjustment}/reject', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'reject'])->name('reject');
                });


            // Preventive Maintenance
            Route::prefix('preventive-maintenance')
                ->name('preventive-maintenance.')
                ->group(function () {
                    Route::get('/', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'index',
                    ])->name('index');

                    Route::get('/create', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'create',
                    ])->name('create');

                    Route::post('/', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'store',
                    ])->name('store');

                    Route::get('/shifts-for-date', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'getShiftsForDate',
                    ])->name('shifts-for-date');

                    // Calendar View Routes (must be before wildcard routes)
                    Route::get('/calendar', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'calendar',
                    ])->name('calendar');

                    Route::get('/calendar/events', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'getCalendarEvents',
                    ])->name('calendar.events');

                    Route::post('/calendar/tasks', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'storeCalendarTask',
                    ])->name('calendar.tasks.store');

                    Route::put('/calendar/tasks/{task}', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'updateCalendarTask',
                    ])->name('calendar.tasks.update');

                    Route::patch('/calendar/tasks/{task}/move', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'moveCalendarTask',
                    ])->name('calendar.tasks.move');

                    Route::patch('/calendar/tasks/{task}/resize', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'resizeCalendarTask',
                    ])->name('calendar.tasks.resize');

                    Route::delete('/calendar/tasks/{task}', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'deleteCalendarTask',
                    ])->name('calendar.tasks.delete');

                    Route::get('/{preventive_maintenance}', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'show',
                    ])->name('show');

                    Route::get('/{preventive_maintenance}/edit', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'edit',
                    ])->name('edit');

                    Route::put('/{preventive_maintenance}', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'update',
                    ])->name('update');

                    Route::delete('/{preventive_maintenance}', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'destroy',
                    ])->name('destroy');

                    Route::patch('/{preventive_maintenance}/activate', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'activate',
                    ])->name('activate');

                    // AJAX routes for adding/removing items
                    Route::post('/{schedule}/date', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'addDate',
                    ])->name('add-date');

                    Route::delete('/date/{scheduleDate}', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'deleteDate',
                    ])->name('delete-date');

                    Route::post('/date/{scheduleDate}/cleaning-group', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'addCleaningGroup',
                    ])->name('add-cleaning-group');

                    Route::post('/cleaning-group/{cleaningGroup}/spr', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'addSprGroup',
                    ])->name('add-spr-group');

                    Route::post('/spr/{sprGroup}/task', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'addTask',
                    ])->name('add-task');

                    Route::post('/date/{scheduleDate}/standalone-task', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'addStandaloneTask',
                    ])->name('add-standalone-task');

                    Route::delete('/cleaning-group/{cleaningGroup}', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'deleteCleaningGroup',
                    ])->name('delete-cleaning-group');

                    Route::delete('/spr/{sprGroup}', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'deleteSprGroup',
                    ])->name('delete-spr-group');

                    Route::delete('/task/{task}', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'deleteTask',
                    ])->name('delete-task');

                    Route::post('/task/{task}/status', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'updateTaskStatus',
                    ])->name('update-task-status');

                    Route::put('/task/{task}', [
                        App\Http\Controllers\Admin\PreventiveMaintenanceController::class,
                        'updateTask',
                    ])->name('update-task');

                });

            // Corrective Maintenance Tickets Management
            Route::prefix('corrective-maintenance')
                ->name('corrective-maintenance.')
                ->group(function () {
                    Route::get('/', [
                        App\Http\Controllers\Admin\CorrectiveMaintenanceController::class,
                        'index',
                    ])->name('index');

                    Route::get('/reports', [
                        App\Http\Controllers\Admin\CorrectiveMaintenanceController::class,
                        'reports',
                    ])->name('reports');

                    Route::post('/{ticket}/create-sub-ticket', [
                        App\Http\Controllers\Admin\CorrectiveMaintenanceController::class,
                        'createSubTicket',
                    ])->name('create-sub-ticket');

                    Route::get('/{ticket}', [
                        App\Http\Controllers\Admin\CorrectiveMaintenanceController::class,
                        'show',
                    ])->name('show');

                    Route::patch('/{ticket}/mark-received', [
                        App\Http\Controllers\Admin\CorrectiveMaintenanceController::class,
                        'markReceived',
                    ])->name('mark-received');

                    Route::patch('/{ticket}/assign', [
                        App\Http\Controllers\Admin\CorrectiveMaintenanceController::class,
                        'assign',
                    ])->name('assign');

                    Route::patch('/{ticket}/complete', [
                        App\Http\Controllers\Admin\CorrectiveMaintenanceController::class,
                        'complete',
                    ])->name('complete');

                    Route::delete('/{ticket}/cancel', [
                        App\Http\Controllers\Admin\CorrectiveMaintenanceController::class,
                        'cancel',
                    ])->name('cancel');

                    Route::patch('/{ticket}/update-notes', [
                        App\Http\Controllers\Admin\CorrectiveMaintenanceController::class,
                        'updateNotes',
                    ])->name('update-notes');
                });
        });

    // ========================================
    // SUPERVISOR MAINTENANCE ROUTES
    // ========================================
    Route::prefix('supervisor')
        ->name('supervisor.')
        ->middleware([RoleMiddleware::class . ':supervisor_maintenance'])
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [
                App\Http\Controllers\Admin\DashboardController::class,
                'index',
            ])->name('dashboard');

            // Shift Management
            Route::resource('shifts', App\Http\Controllers\Admin\ShiftController::class);
            Route::post('shifts/{shift}/assign-user', [
                App\Http\Controllers\Admin\ShiftController::class,
                'assignUser',
            ])->name('shifts.assign-user');
            Route::post('shifts/{shift}/remove-user', [
                App\Http\Controllers\Admin\ShiftController::class,
                'removeUser',
            ])->name('shifts.remove-user');
            Route::post('shifts/{shift}/assign-user-hourly', [
                App\Http\Controllers\Admin\ShiftController::class,
                'assignUserHourly',
            ])->name('shifts.assign-user-hourly');
            Route::post('shifts/{shift}/remove-assignment', [
                App\Http\Controllers\Admin\ShiftController::class,
                'removeAssignment',
            ])->name('shifts.remove-assignment');
            Route::post('shifts/{shift}/remove-assignments', [
                App\Http\Controllers\Admin\ShiftController::class,
                'removeAssignments',
            ])->name('shifts.remove-assignments');
            Route::post('shifts/{shift}/clear-all-assignments', [
                App\Http\Controllers\Admin\ShiftController::class,
                'clearAllAssignments',
            ])->name('shifts.clear-all-assignments');
            Route::patch('shifts/{shift}/activate', [
                App\Http\Controllers\Admin\ShiftController::class,
                'activate',
            ])->name('shifts.activate');
            Route::get('shifts/get-shift-for-date', [
                App\Http\Controllers\Admin\ShiftController::class,
                'getShiftForDate',
            ])->name('shifts.get-shift-for-date');
            Route::get('shifts/{shift}/day-details', [
                App\Http\Controllers\Admin\ShiftController::class,
                'getDayDetails',
            ])->name('shifts.day-details');
            Route::post('shifts/change-assignment', [
                App\Http\Controllers\Admin\ShiftController::class,
                'changeAssignment',
            ])->name('shifts.change-assignment');

            // Share all admin routes untuk inventory, stock opname, PM, CM, KPI, help articles
            // Inventory Management routes... (copy dari admin, terlalu panjang)

            // Redirect ke admin routes untuk simplicity - controller sama
            Route::redirect('/spareparts', '/admin/spareparts')->name('spareparts.index');
            Route::redirect('/tools', '/admin/tools')->name('tools.index');
            Route::redirect('/assets', '/admin/assets')->name('assets.index');
            Route::redirect('/purchase-orders', '/admin/purchase-orders')->name('purchase-orders.index');
            Route::redirect('/opname', '/admin/opname')->name('opname.dashboard');
            Route::redirect('/adjustments', '/admin/adjustments')->name('adjustments.index');
            Route::redirect('/preventive-maintenance', '/admin/preventive-maintenance')->name('preventive-maintenance.index');
            Route::redirect('/corrective-maintenance', '/admin/corrective-maintenance')->name('corrective-maintenance.index');
            Route::redirect('/kpi', '/admin/kpi')->name('kpi.index');
            Route::redirect('/help-articles', '/admin/help-articles')->name('help-articles.index');

            // My Tasks (using User controller but need to use admin layout)
            Route::prefix('my-tasks')
                ->name('my-tasks.')
                ->group(function () {
                    // Preventive Maintenance
                    Route::get('/preventive-maintenance', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'preventiveMaintenance',
                    ])->name('preventive-maintenance');

                    Route::get('/preventive-maintenance/{schedule}', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'showPreventiveMaintenance',
                    ])->name('preventive-maintenance.show');

                    Route::post('/preventive-maintenance/task/{task}/status', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'updatePmTaskStatus',
                    ])->name('preventive-maintenance.task.update-status');

                    // Corrective Maintenance
                    Route::get('/corrective-maintenance', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'correctiveMaintenance',
                    ])->name('corrective-maintenance');

                    Route::get('/corrective-maintenance/{ticket}', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'showCorrectiveMaintenance',
                    ])->name('corrective-maintenance.show');

                    Route::patch('/corrective-maintenance/{ticket}/update-notes', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'updateCmNotes',
                    ])->name('corrective-maintenance.update-notes');

                    Route::patch('/corrective-maintenance/{ticket}/submit-report', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'submitCmReport',
                    ])->name('corrective-maintenance.submit-report');

                    Route::patch('/corrective-maintenance/{ticket}/acknowledge', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'acknowledgeCm',
                    ])->name('corrective-maintenance.acknowledge');

                    // Stock Opname
                    Route::get('/stock-opname', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'stockOpname',
                    ])->name('stock-opname');

                    Route::get('/stock-opname/{schedule}', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'showStockOpname',
                    ])->name('stock-opname.show');

                    Route::post('/stock-opname/execute/{item}', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'executeOpnameItem',
                    ])->name('stock-opname.execute');

                    Route::post('/stock-opname/execute-batch', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'executeOpnameBatch',
                    ])->name('stock-opname.execute-batch');

                    Route::post('/stock-opname/cancel/{item}', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'cancelOpnameItem',
                    ])->name('stock-opname.cancel');

                    Route::get('/stock-opname/{schedule}/export-template', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'exportOpnameTemplate',
                    ])->name('stock-opname.export-template');

                    Route::post('/stock-opname/{schedule}/import-excel', [
                        App\Http\Controllers\Supervisor\MyTaskController::class,
                        'importOpnameExcel',
                    ])->name('stock-opname.import-excel');
                });
        });

    // User Routes
    Route::prefix('user')
        ->name('user.')
        ->middleware(['auth', RoleMiddleware::class . ':staff_maintenance|supervisor_maintenance'])
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [
                App\Http\Controllers\User\DashboardController::class,
                'index',
            ])->name('dashboard');

            Route::get('/dashboard/calendar-data', [
                App\Http\Controllers\User\DashboardController::class,
                'getCalendarDataAjax',
            ])->name('dashboard.calendar-data');

            // My Tasks
            Route::get('/tasks', [
                App\Http\Controllers\User\MyTaskController::class,
                'index',
            ])->name('tasks.index');

            Route::get('/tasks/{job}', [
                App\Http\Controllers\User\MyTaskController::class,
                'show',
            ])->name('tasks.show');

            Route::patch('/tasks/{job}/status', [
                App\Http\Controllers\User\MyTaskController::class,
                'updateStatus',
            ])->name('tasks.update-status');

            // My Reports
            Route::resource('reports', App\Http\Controllers\User\MyReportController::class);

            // Help & Support
            Route::get('/help', [
                App\Http\Controllers\User\HelpController::class,
                'index',
            ])->name('help.index');

            Route::get('/help/search', [
                App\Http\Controllers\User\HelpController::class,
                'search',
            ])->name('help.search');

            Route::get('/help/{article}', [
                App\Http\Controllers\User\HelpController::class,
                'show',
            ])->name('help.show');

            // Corrective Maintenance (assigned based on shift)
            Route::prefix('corrective-maintenance')
                ->name('corrective-maintenance.')
                ->group(function () {
                    Route::get('/', [
                        App\Http\Controllers\User\CorrectiveMaintenanceController::class,
                        'index',
                    ])->name('index');

                    Route::get('/{ticket}', [
                        App\Http\Controllers\User\CorrectiveMaintenanceController::class,
                        'show',
                    ])->name('show');

                    Route::patch('/{ticket}/update-notes', [
                        App\Http\Controllers\User\CorrectiveMaintenanceController::class,
                        'updateNotes',
                    ])->name('update-notes');

                    Route::patch('/{ticket}/submit-report', [
                        App\Http\Controllers\User\CorrectiveMaintenanceController::class,
                        'submitReport',
                    ])->name('submit-report');

                    Route::patch('/{ticket}/acknowledge', [
                        App\Http\Controllers\User\CorrectiveMaintenanceController::class,
                        'acknowledge',
                    ])->name('acknowledge');
                });

            // Stock Opname
            Route::prefix('stock-opname')
                ->name('stock-opname.')
                ->group(function () {
                    Route::get('/', [
                        App\Http\Controllers\User\StockOpnameController::class,
                        'index',
                    ])->name('index');

                    Route::get('/{schedule}', [
                        App\Http\Controllers\User\StockOpnameController::class,
                        'show',
                    ])->name('show');

                    Route::post('/execute/{item}', [
                        App\Http\Controllers\User\StockOpnameController::class,
                        'executeItem',
                    ])->name('execute');

                    Route::post('/execute-batch', [
                        App\Http\Controllers\User\StockOpnameController::class,
                        'executeBatch',
                    ])->name('execute-batch');

                    Route::post('/cancel/{item}', [
                        App\Http\Controllers\User\StockOpnameController::class,
                        'cancelItem',
                    ])->name('cancel');

                    Route::get('/{schedule}/export-template', [
                        App\Http\Controllers\User\StockOpnameController::class,
                        'exportTemplate',
                    ])->name('export-template');

                    Route::post('/{schedule}/import-excel', [
                        App\Http\Controllers\User\StockOpnameController::class,
                        'importExcel',
                    ])->name('import-excel');
                });

            // Preventive Maintenance
            Route::prefix('preventive-maintenance')
                ->name('preventive-maintenance.')
                ->group(function () {
                    Route::get('/', [
                        App\Http\Controllers\User\PreventiveMaintenanceController::class,
                        'index',
                    ])->name('index');

                    Route::get('/{schedule}', [
                        App\Http\Controllers\User\PreventiveMaintenanceController::class,
                        'show',
                    ])->name('show');

                    Route::post('/task/{task}/status', [
                        App\Http\Controllers\User\PreventiveMaintenanceController::class,
                        'updateTaskStatus',
                    ])->name('task.update-status');
                });
        });

    // ========================================
    // PROFILE ROUTES (Both Admin & User)
    // ========================================
    // Profile Routes (for all authenticated users)
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name(
            'profile.index',
        );

        Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name(
            'profile.update',
        );

        Route::put('/profile/password', [
            App\Http\Controllers\ProfileController::class,
            'changePassword',
        ])->name('profile.change-password');
    });
});
