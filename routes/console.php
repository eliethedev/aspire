<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Deferred AI (offline sync): requeue observations stuck at ai_status=failed.
// Requires the scheduler (`php artisan schedule:run`) and a queue worker
// (`php artisan queue:work`) running on the server.
Schedule::command('ai:retry-failed --limit=50')->hourly();
