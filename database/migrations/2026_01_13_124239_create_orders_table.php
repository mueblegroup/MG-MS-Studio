<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('currency', 3)->default('MYR');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->string('status')->default('pending'); // pending|paid|cancelled|failed
            $table->string('payment_provider')->nullable(); // stripe|hitpay
            $table->string('provider_reference')->nullable(); // stripe session id, hitpay id, etc.

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['payment_provider', 'provider_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

