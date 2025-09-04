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
        Schema::create('business_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facebook_page_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('inquiry_number', 20)->unique(); // INQ2024001, ORD2024001, etc.
            
            // Flexible inquiry type
            $table->enum('inquiry_type', ['order', 'booking', 'quote', 'consultation', 'appointment', 'reservation', 'purchase', 'inquiry'])->default('inquiry');
            
            // Customer info (always collected)
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();
            
            // Flexible inquiry details
            $table->string('service_name')->nullable(); // "Website Development", "Haircut", etc.
            $table->text('description')->nullable(); // Customer's detailed requirements
            $table->date('preferred_date')->nullable(); // For bookings/appointments
            $table->time('preferred_time')->nullable(); // For bookings/appointments
            $table->string('budget_range', 50)->nullable(); // "$1000-5000", "Under $100", etc.
            $table->integer('quantity')->default(1); // For orders
            
            // Flexible extra data
            $table->json('extra_fields')->nullable(); // Store any custom fields
            
            // Status and language
            $table->enum('status', ['pending', 'contacted', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->string('language', 5)->default('en');
            
            // Admin notes
            $table->text('admin_notes')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['facebook_page_id', 'status']);
            $table->index(['customer_id', 'created_at']);
            $table->index(['inquiry_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_inquiries');
    }
};