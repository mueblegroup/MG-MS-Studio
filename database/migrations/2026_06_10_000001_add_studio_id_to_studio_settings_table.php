<?php

use App\Models\Studio;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('studio_settings')) {
            return;
        }

        Schema::table('studio_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('studio_settings', 'studio_id')) {
                $table->foreignId('studio_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('studio_settings')) {
            return;
        }

        Schema::table('studio_settings', function (Blueprint $table) {
            if (Schema::hasColumn('studio_settings', 'studio_id')) {
                $table->dropConstrainedForeignId('studio_id');
            }
        });
    }
};