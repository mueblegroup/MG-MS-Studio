<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSsoProvider extends Model
{
    protected $fillable = [
        'provider',
        'is_enabled',
        'client_id',
        'client_secret',
        'tenant_id',
        'secret_expires_at',
        'notes',
    ];

    protected $hidden = [
        'client_secret',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'client_secret' => 'encrypted',
            'secret_expires_at' => 'date',
        ];
    }
}
