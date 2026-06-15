<?php

namespace App\Models\Concerns;

use App\Models\Studio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait AssignsStudio
{
    protected static function bootAssignsStudio(): void
    {
        static::addGlobalScope('studio', function (Builder $builder): void {
            if (! Schema::hasColumn($builder->getModel()->getTable(), 'studio_id')) {
                return;
            }

            if (function_exists('current_studio_id') && current_studio_id()) {
                $builder->where($builder->getModel()->getTable() . '.studio_id', current_studio_id());
            }
        });

        static::creating(function ($model) {
            if (! Schema::hasColumn($model->getTable(), 'studio_id')) {
                return;
            }

            if (! $model->studio_id && function_exists('current_studio_id') && current_studio_id()) {
                $model->studio_id = current_studio_id();
            }
        });
    }

    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }
}
