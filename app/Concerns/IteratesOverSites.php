<?php

namespace App\Concerns;

use App\Models\Site;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

trait IteratesOverSites
{
    protected function forEachSite(callable $callback): void
    {
        $sites = Site::on('central')->active()->get();

        foreach ($sites as $site) {
            Config::set('database.connections.site.database', $site->database_name);
            DB::purge('site');
            DB::reconnect('site');

            $callback($site);
        }
    }
}
