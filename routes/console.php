<?php

use App\Jobs\ReleaseUncheckedSeatsJob;
use App\Models\Tenant;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler: release seats for unchecked participants at scale
// Dispatches one job per tenant every minute; each job processes classes that just started (with a small lookback window)
Schedule::call(function () {
    Tenant::pluck('id')->each(fn (int $id) => ReleaseUncheckedSeatsJob::dispatch(10, $id));
})->name('release-unchecked-seats')->everyMinute()->onOneServer()->withoutOverlapping(15);

Schedule::command('credits:reset')->daily();
