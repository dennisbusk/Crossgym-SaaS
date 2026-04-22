<?php

// app/Providers/LivewireRelationCacheServiceProvider.php

namespace App\Providers;

use Blade;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class LivewireRelationCacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Relation macro: ->cached() (kan stadig bruges, men ikke nødvendig med trait)
        if (! Relation::hasMacro('cached')) {
            Relation::macro('cached', function () {
                $parent = $this->getParent();
                $relationName = $this->getRelationName() ?? 'relation';
                $parentKey = method_exists($parent, 'getKey') ? $parent->getKey() : null;
                $key = sprintf('rel_%s_%s_%s', get_class($parent), (string) $parentKey, $relationName);

                return request_cache($key, fn () => $this->getResults());
            });
        }

        // Blade directive: @cachedRelation($model, 'relation')
        Blade::directive('cachedRelation', function ($expression) {
            return "<?php echo app('App\\Providers\\LivewireRelationCacheServiceProvider')->bladeCachedRelation({$expression}); ?>";
        });
    }

    /**
     * Bruges af Blade directive
     */
    public function bladeCachedRelation($model, $relation)
    {
        if (! $model) {
            return null;
        }

        if (method_exists($model, $relation)) {
            try {
                return $model->$relation()->getResults();
            } catch (\Throwable $e) {
                return $model->getRelationValue($relation);
            }
        }

        return $model->getRelationValue($relation);
    }
}
