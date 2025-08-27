<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            $table->enum('plan_type', ['free', 'premium', 'enterprise'])->default('free');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->json('profile_data')->nullable();
            $table->json('settings')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['email']);
            $table->index(['status']);
            $table->index(['plan_type']);
            $table->index(['subscription_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
