<?php

namespace App\Models\Concerns;

use App\Models\Studio;
use Illuminate\Support\Facades\Schema;

trait AssignsStudio
{
    protected static function bootAssignsStudio(): void
    {
        static::creating(function ($model) {
            if (!Schema::hasColumn($model->getTable(), 'studio_id')) {
                return;
            }

            if (!$model->studio_id && function_exists('current_studio_id') && current_studio_id()) {
                $model->studio_id = current_studio_id();
            }
        });
    }

    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }
}
