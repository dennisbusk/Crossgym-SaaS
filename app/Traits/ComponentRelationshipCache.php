<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait ComponentRelationshipCache
{
    /**
     * Cache for relations during this component instance lifecycle
     */
    protected array $relationshipCache = [];

    /**
     * Magic getter: hvis property matcher en relation, caches resultatet automatisk
     */
    public function __get($key)
    {
        $parentValue = null;

        // Hvis Livewire eller parent har property, returnér den først
        if (method_exists(get_parent_class($this), '__get')) {
            try {
                $parentValue = parent::__get($key);
                if ($parentValue !== null) {
                    return $parentValue;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $this->autoCacheRelation($this, $key);
    }

    /**
     * Auto-cache relationer på modellen
     */
    protected function autoCacheRelation($model, string $relation)
    {
        if (! $model instanceof Model) {
            return $model->$relation ?? null;
        }

        $class = get_class($model);
        $cacheKey = "{$this->getId()}.{$class}.{$model->getKey()}.{$relation}";

        if (isset($this->relationshipCache[$cacheKey])) {
            return $this->relationshipCache[$cacheKey];
        }

        if (! method_exists($model, $relation)) {
            return $model->$relation ?? null;
        }

        $resolved = $model->$relation()->getResults();
        $this->relationshipCache[$cacheKey] = $resolved;

        return $resolved;
    }

    /**
     * Ryd component-level relation cache
     */
    public function clearRelationCache(): void
    {
        $this->relationshipCache = [];
    }
}
