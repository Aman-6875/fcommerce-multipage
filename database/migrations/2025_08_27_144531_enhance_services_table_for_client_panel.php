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
        Schema::table('services', function (Blueprint $table) {
            // Add new columns for client panel functionality
            $table->string('image_url')->nullable()->after('service_price');
            $table->string('service_link')->nullable()->after('image_url'); // Facebook post or external link
            $table->string('category')->nullable()->after('service_link');
            $table->json('tags')->nullable()->after('category');
            $table->boolean('is_active')->default(true)->after('tags');
            $table->integer('duration_hours')->default(1)->after('is_active'); // Service duration
            $table->json('available_days')->nullable()->after('duration_hours'); // Days available (Mon, Tue, etc)
            $table->time('start_time')->nullable()->after('available_days');
            $table->time('end_time')->nullable()->after('start_time');
            $table->integer('max_bookings_per_day')->default(10)->after('end_time');
            $table->integer('advance_booking_days')->default(1)->after('max_bookings_per_day'); // Minimum days in advance
            $table->json('service_areas')->nullable()->after('advance_booking_days'); // Areas where service is available
            $table->text('cancellation_policy')->nullable()->after('service_areas');
            $table->integer('sort_order')->default(0)->after('cancellation_policy');
            
            // Add indexes
            $table->index(['client_id', 'is_active']);
            $table->index(['client_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'is_active']);
            $table->dropIndex(['client_id', 'category']);
            
            $table->dropColumn([
                'image_url',
                'service_link',
                'category',
                'tags',
                'is_active',
                'duration_hours',
                'available_days',
                'start_time',
                'end_time',
                'max_bookings_per_day',
                'advance_booking_days',
                'service_areas',
                'cancellation_policy',
                'sort_order'
            ]);
        });
    }
};
