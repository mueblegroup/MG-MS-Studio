<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_settings', function (Blueprint $table): void {
            $table->dropUnique('studio_settings_key_unique');
            $table->unique(['studio_id', 'key'], 'studio_settings_studio_id_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('studio_settings', function (Blueprint $table): void {
            $table->dropUnique('studio_settings_studio_id_key_unique');
            $table->unique('key', 'studio_settings_key_unique');
        });
    }
};
