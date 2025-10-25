<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\SystemSettingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

#[ObservedBy([SystemSettingObserver::class])]
class SystemSetting extends Model {

    protected $fillable
        = [
            'key',
            'value',
        ];

    //Value Mutator
    protected function value(): Attribute {
        return Attribute::make(
        // MUTATOR: called when setting the value
            get: fn( $value ) => json_decode(Crypt::decrypt($value), true),

            // ACCESSOR: called when getting the value
            set: fn( $value ) => Crypt::encrypt(json_encode($value)),
        );
    }
}
