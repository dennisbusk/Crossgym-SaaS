<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Scan policies and sync all permissions to the database';

    public function handle(): int
    {
        $this->info('Scanning policies...');

        $policiesPath = app_path('Policies');
        if (! is_dir($policiesPath)) {
            $this->warn('No app/Policies directory found.');

            return self::SUCCESS;
        }

        $policies = collect(File::files($policiesPath))
            ->map(fn ($file) => 'App\\Policies\\'.pathinfo($file->getFilename(), PATHINFO_FILENAME))
            ->filter(fn ($class) => class_exists($class));

        $found = collect();

        foreach ($policies as $policy) {
            $reflection = new ReflectionClass($policy);
            $methods = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
                ->reject(fn ($m) => in_array($m->name, ['__construct', 'before']))
                ->pluck('name');

            $model = str_replace('Policy', '', class_basename($policy));

            foreach ($methods as $ability) {
                $found->push(['model' => $model, 'ability' => $ability]);
            }
        }

        $existing = Permission::all()->map(fn ($p) => "{$p->model}.{$p->ability}");

        foreach ($found as $perm) {
            $name = "{$perm['model']}.{$perm['ability']}";
            if (! $existing->contains($name)) {
                Permission::create($perm);
                $this->line("Added: {$name}");
            }
        }

        $toDelete = $existing->reject(fn ($name) => $found->contains(fn ($p) => "{$p['model']}.{$p['ability']}" === $name)
        );

        foreach ($toDelete as $name) {
            [$model, $ability] = explode('.', $name);
            Permission::where('model', $model)->where('ability', $ability)->delete();
            $this->warn("Removed: {$name}");
        }

        $this->info('Permissions synchronized successfully ✅');

        return self::SUCCESS;
    }
}
