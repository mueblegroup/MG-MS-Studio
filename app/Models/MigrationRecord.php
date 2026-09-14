<?php

namespace App\Models;

use App\Models\Concerns\AssignsStudio;
use Illuminate\Database\Eloquent\Model;

class MigrationRecord extends Model
{
    use AssignsStudio;

    protected $fillable = [
        'studio_id',
        'source_system',
        'entity_type',
        'source_id',
        'target_type',
        'target_id',
        'checksum',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
