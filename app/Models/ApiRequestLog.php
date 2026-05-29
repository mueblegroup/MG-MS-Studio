<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRequestLog extends Model
{
    protected $fillable = [
        'user_id',
        'token_id',
        'token_name',
        'method',
        'endpoint',
        'ip_address',
        'user_agent',
        'status_code',
        'request_payload',
        'response_summary',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_summary' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
