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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('facebook_user_id');
            $table->string('name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('area', 100)->nullable();
            $table->json('profile_data')->nullable();
            $table->json('interaction_stats')->nullable();
            $table->json('tags')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamp('first_interaction')->nullable();
            $table->timestamp('last_interaction')->nullable();
            $table->integer('interaction_count')->default(0);
            $table->enum('status', ['active', 'blocked', 'unsubscribed'])->default('active');
            $table->timestamps();
            
            $table->unique(['client_id', 'facebook_user_id']);
            $table->index(['client_id']);
            $table->index(['facebook_user_id']);
            $table->index(['last_interaction']);
            $table->index(['status']);
            $table->fullText(['name', 'email', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
