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
        Schema::create('facebook_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('page_id')->unique();
            $table->string('page_name');
            $table->text('access_token')->nullable();
            $table->json('page_data')->nullable();
            $table->boolean('is_connected')->default(false);
            $table->timestamp('last_sync')->nullable();
            $table->timestamps();
            
            $table->index(['client_id']);
            $table->index(['page_id']);
            $table->index(['is_connected']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_pages');
    }
};
