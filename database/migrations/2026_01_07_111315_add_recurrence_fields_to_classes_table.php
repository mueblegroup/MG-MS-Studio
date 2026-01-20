<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('type');

            // same values as your old system
            $table->string('recurrence_frequency')->nullable()->after('is_recurring'); // everyday, 7days, monthly, yearly, custom
            $table->unsignedInteger('custom_frequency_days')->nullable()->after('recurrence_frequency'); // for custom
            $table->date('until_date')->nullable()->after('custom_frequency_days');
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn([
                'is_recurring',
                'recurrence_frequency',
                'custom_frequency_days',
                'until_date',
            ]);
        });
    }
};
