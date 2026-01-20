<?php

// database/migrations/2026_01_16_000003_upgrade_payments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('payments', 'status')) {
                $table->string('status')->default('pending')->after('reference'); // pending|paid|failed|cancelled
            }
            if (!Schema::hasColumn('payments', 'provider')) {
                $table->string('provider')->nullable()->after('method'); // stripe|hitpay
            }
            if (!Schema::hasColumn('payments', 'provider_reference')) {
                $table->string('provider_reference')->nullable()->after('reference');
            }
            if (!Schema::hasColumn('payments', 'payload')) {
                $table->json('payload')->nullable()->after('provider_reference');
            }
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payload');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // optional to reverse; you can leave empty in real projects
        });
    }
};
