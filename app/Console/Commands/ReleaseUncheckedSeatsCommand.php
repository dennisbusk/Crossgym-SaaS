<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ReleaseUncheckedSeatsJob;
use Illuminate\Console\Command;

class ReleaseUncheckedSeatsCommand extends Command
{
    protected $signature = 'classes:release-unchecked {--lookback=10 : Minutes to look back from now for classes that just started}';

    protected $description = 'Release seats for participants who have not checked in when a class has started.';

    public function handle(): int
    {
        $lookback = (int) $this->option('lookback');
        $this->info(__('Releasing unchecked seats for classes started in the last :min minutes...', ['min' => $lookback]));

        // Run the job synchronously in this process
        (new ReleaseUncheckedSeatsJob(lookbackMinutes: $lookback))->handle();

        $this->info(__('Done.'));

        return self::SUCCESS;
    }
}
