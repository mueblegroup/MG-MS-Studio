<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Individual class assignment: prevent duplicates
        Schema::table('class_session_assignments', function (Blueprint $table) {
            // Adjust index name if it already exists
            $table->unique(['user_id', 'class_session_id'], 'uniq_user_class_session');
        });

        // Plan ownership: prevent duplicates
        Schema::table('user_plans', function (Blueprint $table) {
            $table->unique(['user_id', 'plan_id'], 'uniq_user_plan');
        });

        // If you want to prevent duplicate cards with same order+card (optional),
        // uncomment this and ensure it fits your logic.
        // Schema::table('user_class_cards', function (Blueprint $table) {
        //     $table->index(['user_id', 'class_card_id', 'order_id'], 'idx_user_card_order');
        // });
    }

    public function down(): void
    {
        Schema::table('class_session_assignments', function (Blueprint $table) {
            $table->dropUnique('uniq_user_class_session');
        });

        Schema::table('user_plans', function (Blueprint $table) {
            $table->dropUnique('uniq_user_plan');
        });

        // Schema::table('user_class_cards', function (Blueprint $table) {
        //     $table->dropIndex('idx_user_card_order');
        // });
    }
};
