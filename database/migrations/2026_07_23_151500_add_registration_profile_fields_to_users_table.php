<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('organisation_name')->nullable()->after('phone_number');
            $table->string('job_title')->nullable()->after('organisation_name');
            $table->string('country', 2)->nullable()->after('job_title');
            $table->date('date_of_birth')->nullable()->after('country');
            $table->string('gender', 30)->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('gender');
            $table->string('emergency_contact_name')->nullable()->after('address');
            $table->string('emergency_contact_phone', 40)->nullable()->after('emergency_contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'organisation_name',
                'job_title',
                'country',
                'date_of_birth',
                'gender',
                'address',
                'emergency_contact_name',
                'emergency_contact_phone',
            ]);
        });
    }
};
