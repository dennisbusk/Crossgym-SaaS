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
            $model = str_replace('Policy', '', class_basename($policy));

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (in_array($method->name, ['__construct', 'before'])) {
                    continue;
                }

                $docComment = $method->getDocComment();
                $description = null;
                if ($docComment !== false) {
                    $description = $this->parseDescription($docComment);
                }

                $found->push([
                    'model' => $model,
                    'ability' => $method->name,
                    'description' => $description,
                ]);
            }
        }

        $existing = Permission::all();
        $existingNames = $existing->map(fn ($p) => "{$p->model}.{$p->ability}");

        foreach ($found as $perm) {
            $name = "{$perm['model']}.{$perm['ability']}";
            $permission = Permission::updateOrCreate(
                ['model' => $perm['model'], 'ability' => $perm['ability']],
                ['description' => $perm['description']]
            );

            if ($permission->wasRecentlyCreated) {
                $this->line("Added: {$name}");
            } elseif ($permission->wasChanged('description')) {
                $this->line("Updated description for: {$name}");
            }
        }

        $toDelete = $existingNames->reject(fn ($name) => $found->contains(fn ($p) => "{$p['model']}.{$p['ability']}" === $name)
        );

        foreach ($toDelete as $name) {
            [$model, $ability] = explode('.', $name);
            Permission::where('model', $model)->where('ability', $ability)->delete();
            $this->warn("Removed: {$name}");
        }

        $this->info('Permissions synchronized successfully ✅');

        return self::SUCCESS;
    }

    private function parseDescription(string $docComment): string
    {
        $lines = explode("\n", $docComment);
        $descriptionLines = [];
        foreach ($lines as $line) {
            $line = trim($line, "/* \t\r\n");
            if (empty($line) || str_starts_with($line, '@')) {
                continue;
            }
            $descriptionLines[] = $line;
        }

        return implode(' ', $descriptionLines);
    }
}
