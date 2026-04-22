<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Minimal class for Retention policy binding.
 * No database table; used only for Gate::policy(Retention::class, RetentionPolicy::class).
 */
class Retention
{
    //
}
