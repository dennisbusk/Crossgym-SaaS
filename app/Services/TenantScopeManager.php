<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantScopeManager
{
    protected string $cacheKey = 'tenant_aware_models';

    /**
     * Apply tenant scoping to all tenant-aware models
     */
    public function applyScopes(object $tenant): void
    {
        $models = $this->getTenantAwareModels();
        foreach ($models as $modelClass) {
            // Add global scope
            $modelClass::addGlobalScope('tenant', function (Builder $builder) use ($tenant, $modelClass) {
                $builder->where('tenant_id', $tenant->id);
            });
            // Auto-assign tenant_id on create
            $modelClass::creating(function ($model) use ($tenant) {
                if (!$model->tenant_id) {
                    $model->tenant_id = $tenant->id;
                }
            });
        }
    }

    /**
     * Discover and cache all models with tenant_id column
     *
     * @return array<string>
     */
    protected function getTenantAwareModels(): array
    {
        return Cache::rememberForever($this->cacheKey, function () {
            $modelsPath = app_path('Models');

            return collect(File::allFiles($modelsPath))
                ->map(fn($file) => 'App\\Models\\' . Str::studly($file->getFilenameWithoutExtension()))
                ->filter(fn($class) => class_exists($class))
                ->filter(function ($class) {
                    $instance = new $class;

                    if (!method_exists($instance, 'getTable')) {
                        return false;
                    }

                    $table = $instance->getTable();

                    // Skip tables that don't exist yet
                    if (!Schema::hasTable($table)) {
                        return false;
                    }

                    // Only include models with tenant_id column
                    return Schema::hasColumn($table, 'tenant_id');
                })
                ->values()
                ->all();
        });
    }

    /**
     * Clear the cached tenant-aware models
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
