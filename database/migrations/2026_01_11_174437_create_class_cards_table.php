<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('class_cards');
        Schema::enableForeignKeyConstraints();

        Schema::create('class_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('total_classes');      // e.g. 10 credits
            $table->unsignedInteger('validity_weeks');     // e.g. 12 weeks
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_cards');
    }
};
