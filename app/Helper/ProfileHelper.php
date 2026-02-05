<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('ProfileHelper::profileLayout')) {
    function profileLayout()
    {
        $user = Auth::user();

        if (!$user) {
            return 'layouts.user';
        }

        if ($user->hasRole(['admin', 'supervisor_maintenance'])) {
            return 'layouts.admin';
        }

        if ($user->hasRole('pic')) {
            return 'layouts.pic';
        }

        return 'layouts.user';
    }
}
