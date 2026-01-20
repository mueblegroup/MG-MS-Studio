<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudioSetting extends Model
{
    protected $table = 'studio_settings';

    protected $fillable = [
        'key',
        'value',
    ];
}
