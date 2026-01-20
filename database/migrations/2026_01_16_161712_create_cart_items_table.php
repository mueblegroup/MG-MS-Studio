<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_cart_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('cart_items');
        Schema::enableForeignKeyConstraints();
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();

            $table->morphs('purchasable'); // purchasable_type + purchasable_id

            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->char('currency', 3)->default('MYR');

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['cart_id', 'purchasable_type', 'purchasable_id']); // prevents duplicates
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
