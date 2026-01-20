<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();

            // Polymorphic item: ClassSession OR Plan OR ClassCard
            $table->morphs('purchasable'); // purchasable_type, purchasable_id

            $table->unsignedInteger('quantity')->default(1);

            // snapshot pricing (important so price doesn't change later)
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('currency', 3)->default('MYR');

            // optional extra data (eg: plan start date chosen later, etc.)
            $table->json('meta')->nullable();

            $table->timestamps();

            // prevent duplicates for same cart + item
            $table->unique(['cart_id', 'purchasable_type', 'purchasable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
