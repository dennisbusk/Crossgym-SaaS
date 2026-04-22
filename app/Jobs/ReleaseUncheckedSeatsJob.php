<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\GymClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ReleaseUncheckedSeatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public int $timeout = 120;

    /**
     * Process classes whose start time is in [now - $lookbackMinutes, now],
     * releasing (detaching) participants that have not checked in.
     *
     * @param  int  $lookbackMinutes  Minutes to look back for classes that just started
     * @param  int|null  $tenantId  When set, only process classes for this tenant; when null, process all tenants
     */
    public function __construct(
        public int $lookbackMinutes = 10,
        public ?int $tenantId = null
    ) {}

    public function handle(): void
    {
        $now = Carbon::now();
        $from = $now->copy()->subMinutes($this->lookbackMinutes);

        $releasedSeats = 0;
        $processedClasses = 0;

        GymClass::query()
            ->with(['participants'])
            ->when($this->tenantId, fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->whereBetween('class_start', [$from, $now])
            ->chunkById(200, function ($classes) use (&$releasedSeats, &$processedClasses) {
                /** @var GymClass $class */
                foreach ($classes as $class) {
                    $processedClasses++;
                    // Detach all participants that are not checked in
                    $ids = $class->participants()
                        ->where(function ($q) {
                            $q->whereNull('gym_class_user.check_in_id')
                                ->orWhereIn('gym_class_user.check_in_id', function ($query) {
                                    $query->select('id')->from('check_ins')->whereNull('checked_at');
                                });
                        })
                        ->pluck('users.id')
                        ->all();

                    if (! empty($ids)) {
                        $class->participants()->detach($ids);
                        $releasedSeats += count($ids);
                    }
                }
            });

        if ($releasedSeats > 0) {
            Log::info('ReleaseUncheckedSeatsJob: released seats', [
                'released' => $releasedSeats,
                'classes' => $processedClasses,
                'tenant_id' => $this->tenantId,
            ]);
        } else {
            Log::debug('ReleaseUncheckedSeatsJob: no seats to release', [
                'classes' => $processedClasses,
                'tenant_id' => $this->tenantId,
            ]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ReleaseUncheckedSeatsJob failed', [
            'tenant_id' => $this->tenantId,
            'error' => $e->getMessage(),
        ]);
    }
}
