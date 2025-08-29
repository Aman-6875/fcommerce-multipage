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
        Schema::create('conversation_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
            $table->foreignId('facebook_page_id')->constrained()->onDelete('cascade');
            $table->integer('current_step_index')->default(0);
            $table->string('language', 5)->default('en'); // Customer's chosen language
            $table->enum('status', ['active', 'completed', 'abandoned', 'paused'])->default('active');
            $table->json('step_responses')->nullable(); // Responses for each completed step
            $table->json('step_retry_counts')->nullable(); // Retry counts per step
            $table->json('temp_data')->nullable(); // Temporary data during workflow
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'status']);
            $table->index(['workflow_id', 'status']);
            $table->index(['facebook_page_id', 'status']);
            $table->index(['status', 'last_activity_at']);
            
            // Ensure one active conversation per customer per page
            $table->unique(['customer_id', 'facebook_page_id', 'status'], 'unique_active_conversation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_states');
    }
};