<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\ShiftAssignment;
use App\Observers\ShiftAssignmentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Register observers
        ShiftAssignment::observe(ShiftAssignmentObserver::class);

        // Share routePrefix with all admin views for supervisor/admin compatibility
        View::composer(['admin.*', 'layouts.admin', 'supervisor.*'], function ($view) {
            if (auth()->check()) {
                $routePrefix = auth()->user()->hasRole('supervisor_maintenance') ? 'supervisor' : 'admin';
                $view->with('routePrefix', $routePrefix);
            }
        });

        // Share routePrefix with user tool-request views when accessed by supervisor
        View::composer('user.tool-requests.*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->hasRole('supervisor_maintenance') || $user->hasRole('admin')) {
                    $routePrefix = $user->hasRole('supervisor_maintenance') ? 'supervisor' : 'admin';
                } else {
                    $routePrefix = 'user';
                }
                $view->with('routePrefix', $routePrefix);
            }
        });
    }
}
