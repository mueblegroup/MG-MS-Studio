<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_payment_gateways', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('studio_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32);
            $table->boolean('enabled')->default(false);
            $table->string('environment', 16)->default('sandbox');
            $table->text('credentials')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status', 32)->nullable();
            $table->text('last_test_message')->nullable();
            $table->timestamps();

            $table->unique(['studio_id', 'provider']);
            $table->index(['provider', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_payment_gateways');
    }
};
