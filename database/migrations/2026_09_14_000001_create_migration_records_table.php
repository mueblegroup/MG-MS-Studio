<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('migration_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('studio_id')->index();
            $table->string('source_system', 100);
            $table->string('entity_type', 100);
            $table->string('source_id', 191);
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(
                ['studio_id', 'source_system', 'entity_type', 'source_id'],
                'migration_records_source_unique'
            );
            $table->index(['studio_id', 'target_type', 'target_id'], 'migration_records_target_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('migration_records');
    }
};
