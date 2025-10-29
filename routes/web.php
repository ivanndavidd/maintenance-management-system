<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middleware\RoleMiddleware;

// Authentication Routes
Auth::routes();

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// After login redirect based on role
Route::get('/dashboard', function () {
    if (
        auth()
            ->user()
            ->hasRole(['admin', 'super-admin'])
    ) {
        return redirect()->route('admin.dashboard');
    } else {
        return redirect()->route('user.dashboard');
    }
})
    ->middleware('auth')
    ->name('dashboard');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Admin Routes - Gunakan full class name
    Route::prefix('admin')
        ->name('admin.')
        ->middleware([RoleMiddleware::class . ':admin|super-admin'])
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
                    Route::get('/', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'index',
                    ])->name('index');
                    Route::get('/my-reports', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'myReports',
                    ])->name('my-reports');
                    Route::get('/create', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'create',
                    ])->name('create');
                    Route::post('/', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'store',
                    ])->name('store');
                    Route::get('/{workReport}', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'show',
                    ])->name('show');
                    Route::get('/{workReport}/edit', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'edit',
                    ])->name('edit');
                    Route::put('/{workReport}', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'update',
                    ])->name('update');
                    Route::delete('/{workReport}', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'destroy',
                    ])->name('destroy');
                    Route::patch('/{workReport}/validate', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'validateReport', // ✅ FIXED
                    ])->name('validate');
                    Route::delete('/{workReport}/attachment/{index}', [
                        App\Http\Controllers\Admin\WorkReportController::class,
                        'deleteAttachment',
                    ])->name('delete-attachment');
                });

            // Machines/Equipment
            Route::resource('machines', App\Http\Controllers\Admin\MachineController::class);
            Route::patch('machines/{machine}/update-status', [
                App\Http\Controllers\Admin\MachineController::class,
                'updateStatus',
            ])->name('machines.update-status');

            // Parts & Inventory
            Route::resource('parts', App\Http\Controllers\Admin\PartController::class);
        });

    // User/Operator Routes
    Route::prefix('user')
        ->name('user.')
        ->middleware([RoleMiddleware::class . ':user'])
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [
                App\Http\Controllers\User\DashboardController::class,
                'index',
            ])->name('dashboard');

            // My Tasks
            Route::get('/tasks', [
                App\Http\Controllers\User\MyTaskController::class,
                'index',
            ])->name('tasks.index');
            Route::get('/tasks/{job}', [
                App\Http\Controllers\User\MyTaskController::class,
                'show',
            ])->name('tasks.show');

            // My Reports
            Route::resource('reports', App\Http\Controllers\User\MyReportController::class);
        });

    // Common Routes (Both Admin & User)
    Route::prefix('profile')
        ->name('profile.')
        ->group(function () {
            Route::get('/', [App\Http\Controllers\ProfileController::class, 'index'])->name(
                'index',
            );
            Route::put('/', [App\Http\Controllers\ProfileController::class, 'update'])->name(
                'update',
            );
            Route::put('/password', [
                App\Http\Controllers\ProfileController::class,
                'updatePassword', // ✅ FIXED
            ])->name('password');
        });
});
