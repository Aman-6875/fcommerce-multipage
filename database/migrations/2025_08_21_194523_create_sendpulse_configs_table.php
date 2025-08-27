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
        Schema::create('sendpulse_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('api_user_id')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('bot_id')->nullable();
            $table->string('webhook_url', 500)->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('configuration')->nullable();
            $table->timestamp('last_sync')->nullable();
            $table->timestamps();
            
            $table->unique(['client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sendpulse_configs');
    }
};
