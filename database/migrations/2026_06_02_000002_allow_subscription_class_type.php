<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('classes', 'type')) {
            DB::statement("ALTER TABLE `classes` MODIFY `type` ENUM('single', 'recurring', 'subscription') NOT NULL DEFAULT 'single'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('classes', 'type')) {
            DB::statement("UPDATE `classes` SET `type` = 'recurring' WHERE `type` = 'subscription'");
            DB::statement("ALTER TABLE `classes` MODIFY `type` ENUM('single', 'recurring') NOT NULL DEFAULT 'single'");
        }
    }
};
