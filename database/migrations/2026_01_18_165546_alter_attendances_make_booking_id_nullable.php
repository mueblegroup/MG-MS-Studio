<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // booking_id must allow null because attendance may come from assignment
            $table->unsignedBigInteger('booking_id')->nullable()->change();
            $table->unsignedBigInteger('class_session_assignment_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id')->nullable(false)->change();
            $table->unsignedBigInteger('class_session_assignment_id')->nullable(false)->change();
        });
    }
};
