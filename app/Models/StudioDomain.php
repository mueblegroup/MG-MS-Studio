<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudioDomain extends Model
{
    protected $fillable = [
        'studio_id',
        'domain',
        'type',
        'is_primary',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }
}
