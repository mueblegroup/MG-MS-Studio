<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studios', function (Blueprint $table): void {
            $table->timestamp('manually_suspended_at')->nullable()->after('canceled_at');
            $table->text('suspension_reason')->nullable()->after('manually_suspended_at');
            $table->index('manually_suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('studios', function (Blueprint $table): void {
            $table->dropIndex(['manually_suspended_at']);
            $table->dropColumn(['manually_suspended_at', 'suspension_reason']);
        });
    }
};
