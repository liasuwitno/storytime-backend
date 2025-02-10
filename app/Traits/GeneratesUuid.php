<?php

namespace App\Traits;

use CaliCastle\Cuid;
use Illuminate\Support\Str;

trait GeneratesUuid
{
    /**
     * Boot function from Laravel.
     */
    protected static function bootGeneratesUuid()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Cuid::make();
            }
        });
    }
}
