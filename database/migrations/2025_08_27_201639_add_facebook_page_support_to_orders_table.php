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
        Schema::table('orders', function (Blueprint $table) {
            // Add Facebook page support for page-wise separation
            $table->foreignId('facebook_page_id')->after('customer_id')->constrained('facebook_pages')->onDelete('cascade');
            
            // Add shipping support
            $table->decimal('subtotal', 10, 2)->after('total_amount')->default(0);
            $table->decimal('shipping_charge', 10, 2)->after('subtotal')->default(0);
            $table->string('shipping_zone', 50)->after('shipping_charge')->nullable(); // 'inside_dhaka', 'outside_dhaka'
            
            // Add order configuration
            $table->decimal('minimum_order_amount', 10, 2)->after('shipping_zone')->nullable();
            $table->decimal('maximum_order_amount', 10, 2)->after('minimum_order_amount')->nullable();
            
            // Add Facebook integration
            $table->string('facebook_user_id')->after('customer_id')->nullable();
            
            // Add invoice and tracking
            $table->string('invoice_number', 50)->after('order_number')->nullable();
            $table->timestamp('confirmed_at')->after('status')->nullable();
            $table->timestamp('shipped_at')->after('confirmed_at')->nullable();
            $table->timestamp('delivered_at')->after('shipped_at')->nullable();
            
            // Add indexes
            $table->index(['facebook_page_id']);
            $table->index(['facebook_user_id']);
            $table->index(['shipping_zone']);
            $table->index(['confirmed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['facebook_page_id']);
            $table->dropColumn([
                'facebook_page_id',
                'subtotal', 
                'shipping_charge',
                'shipping_zone',
                'minimum_order_amount',
                'maximum_order_amount',
                'facebook_user_id',
                'invoice_number',
                'confirmed_at',
                'shipped_at', 
                'delivered_at'
            ]);
        });
    }
};
