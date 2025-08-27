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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->string('booking_number')->unique(); // BKG-2024-001
            $table->date('booking_date');
            $table->time('booking_time');
            $table->time('end_time')->nullable(); // Calculated from service duration
            $table->decimal('service_price', 10, 2);
            $table->decimal('total_amount', 10, 2); // Including any additional charges
            $table->json('customer_info'); // Name, phone, address
            $table->json('booking_details')->nullable(); // Additional booking requirements
            $table->json('location_info')->nullable(); // Service location if applicable
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->text('notes')->nullable(); // Internal notes
            $table->text('customer_notes')->nullable(); // Customer requests/notes
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('cancellation_info')->nullable(); // Reason, cancelled_by, cancelled_at
            $table->timestamps();
            
            $table->index(['client_id', 'booking_date']);
            $table->index(['client_id', 'status']);
            $table->index(['customer_id']);
            $table->index(['service_id']);
            $table->unique(['service_id', 'booking_date', 'booking_time']); // Prevent double booking
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
