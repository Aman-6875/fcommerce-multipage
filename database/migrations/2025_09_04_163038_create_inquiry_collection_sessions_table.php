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
        Schema::create('inquiry_collection_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('facebook_page_id')->constrained()->onDelete('cascade');
            
            // Session settings
            $table->string('language', 5)->default('en');
            $table->string('inquiry_type', 20)->default('inquiry'); // 'order', 'booking', 'consultation', etc.
            
            // Collection progress
            $table->enum('current_step', ['name', 'phone', 'email', 'service', 'description', 'date', 'time', 'budget', 'quantity', 'custom_fields', 'completed'])->default('name');
            $table->integer('step_index')->default(0); // Track which step we're on
            
            // Collected data (JSON)
            $table->json('collected_data')->nullable(); // Store all collected information
            
            // Session management
            $table->timestamp('expires_at'); // Auto-expire after 30 minutes of inactivity
            $table->timestamp('last_activity_at')->useCurrent();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['customer_id', 'facebook_page_id']);
            $table->index(['expires_at']);
            $table->index(['current_step', 'last_activity_at']);
            
            // Unique constraint - one active session per customer per page
            $table->unique(['customer_id', 'facebook_page_id'], 'unique_active_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry_collection_sessions');
    }
};