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
        Schema::create('page_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facebook_page_id')->constrained()->onDelete('cascade');
            
            // Multi-language questions and answers
            $table->text('question_en');
            $table->text('question_bn')->nullable();
            $table->text('answer_en');
            $table->text('answer_bn')->nullable();
            
            // Display settings
            $table->integer('display_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('quick_reply_text', 10)->nullable(); // "1️⃣", "2️⃣", etc.
            
            // Action settings
            $table->enum('action_type', ['answer_only', 'start_inquiry', 'show_menu', 'custom'])->default('answer_only');
            $table->string('inquiry_type', 20)->nullable(); // 'order', 'booking', etc. when action_type is start_inquiry
            
            $table->timestamps();
            
            // Indexes
            $table->index(['facebook_page_id', 'is_active', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_faqs');
    }
};