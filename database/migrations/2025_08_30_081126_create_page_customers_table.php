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
        Schema::create('page_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facebook_page_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('facebook_user_id')->nullable(); // Facebook-specific user ID for this page
            $table->json('page_specific_data')->nullable(); // Page-specific customer data
            $table->timestamp('first_interaction')->nullable();
            $table->timestamp('last_interaction')->nullable();
            $table->integer('interaction_count')->default(0);
            $table->enum('status', ['active', 'blocked', 'inactive'])->default('active');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('facebook_page_id')->references('id')->on('facebook_pages')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            
            // Unique constraint: one record per page-customer combination
            $table->unique(['facebook_page_id', 'customer_id'], 'unique_page_customer');
            
            // Index for faster queries
            $table->index(['facebook_page_id', 'status']);
            $table->index(['customer_id']);
            $table->index(['facebook_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_customers');
    }
};
