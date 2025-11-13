<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middleware\RoleMiddleware;

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

// Auth pending/waiting page
Route::get('/auth/pending', function () {
    $user = session('user') ?? auth()->user();

    if (!$user) {
        return redirect()->route('login');
    }

    return view('auth.pending', compact('user'));
})->name('auth.pending');

// ========================================
// Root & Dashboard Redirects
// ========================================

// Redirect root based on auth status
Route::get('/', function () {
    if (Auth::check()) {
        // If already logged in, redirect to dashboard
        if (
            auth()
                ->user()
                ->hasRole(['admin', 'super-admin'])
        ) {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->hasRole('user')) {
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
    if (
        auth()
            ->user()
            ->hasRole(['admin', 'super-admin'])
    ) {
        return redirect()->route('admin.dashboard');
    } elseif (auth()->user()->hasRole('user')) {
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

            // Machines/Equipment
            Route::resource('machines', App\Http\Controllers\Admin\MachineController::class);
            Route::patch('machines/{machine}/update-status', [
                App\Http\Controllers\Admin\MachineController::class,
                'updateStatus',
            ])->name('machines.update-status');

            // Parts & Inventory
            Route::resource('parts', App\Http\Controllers\Admin\PartController::class);

            // Help Articles Management
            Route::resource('help-articles', App\Http\Controllers\Admin\HelpArticleController::class);
            Route::patch('help-articles/{helpArticle}/toggle-publish', [
                App\Http\Controllers\Admin\HelpArticleController::class,
                'togglePublish',
            ])->name('help-articles.toggle-publish');
        });

    // User Routes
    Route::prefix('user')
        ->name('user.')
        ->middleware(['auth', RoleMiddleware::class . ':user'])
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
