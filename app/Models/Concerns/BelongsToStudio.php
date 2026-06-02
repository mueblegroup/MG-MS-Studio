<?php

namespace App\Models\Concerns;

use App\Models\Studio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait BelongsToStudio
{
    protected static function bootBelongsToStudio(): void
    {
        static::creating(function ($model) {
            if (!Schema::hasColumn($model->getTable(), 'studio_id')) {
                return;
            }

            if (!$model->studio_id && function_exists('current_studio_id') && current_studio_id()) {
                $model->studio_id = current_studio_id();
            }
        });

        static::addGlobalScope('studio', function (Builder $builder) {
            $model = $builder->getModel();

            if (!Schema::hasColumn($model->getTable(), 'studio_id')) {
                return;
            }

            if (app()->runningInConsole()) {
                return;
            }

            if (auth()->check() && auth()->user()?->role === 'superadmin') {
                return;
            }

            $studioId = function_exists('current_studio_id') ? current_studio_id() : null;

            if ($studioId) {
                $builder->where($model->getTable() . '.studio_id', $studioId);
            }
        });
    }

    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }

    public function scopeForStudio(Builder $query, int $studioId): Builder
    {
        return $query->withoutGlobalScope('studio')->where($query->getModel()->getTable() . '.studio_id', $studioId);
    }
}
