<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Color;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeduplicateColors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deduplicate-colors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deduplicate colors with the same name and consolidate classes.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting color deduplication...');

        // Hent alle navne der findes mere end én gang (per tenant)
        $duplicateNames = Color::select('name', 'tenant_id')
            ->groupBy('name', 'tenant_id')
            ->having(DB::raw('count(*)'), '>', 1)
            ->get();

        if ($duplicateNames->isEmpty()) {
            $this->info('No duplicate color names found.');

            return 0;
        }

        foreach ($duplicateNames as $duplicate) {
            $name = $duplicate->name ?? '';
            $this->info("Processing duplicates for name: '{$name}' (Tenant: {$duplicate->tenant_id})");

            $colors = Color::where('name', $name)
                ->where('tenant_id', $duplicate->tenant_id)
                ->withCount('classes')
                ->orderByDesc('classes_count')
                ->get();

            if ($colors->count() <= 1) {
                continue;
            }

            // Den første er den med flest klasser
            $targetColor = $colors->shift();
            $this->comment("  Target color ID: {$targetColor->id} with {$targetColor->classes_count} classes.");

            foreach ($colors as $color) {
                if ($color->classes_count > 0) {
                    $this->line("    Moving {$color->classes_count} classes from ID {$color->id} to {$targetColor->id}...");
                    $color->classes()->update(['color_id' => $targetColor->id]);
                }

                $this->line("    Deleting color ID {$color->id}...");
                $color->delete();
            }
        }

        $this->info('Color deduplication completed.');

        return 0;
    }
}
