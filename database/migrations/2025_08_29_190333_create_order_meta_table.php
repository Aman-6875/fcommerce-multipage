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
        Schema::create('order_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('product_name'); // Store name at time of order for historical accuracy
            $table->string('product_sku')->nullable(); // Store SKU at time of order
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2); // Price at time of order
            $table->decimal('total_price', 10, 2); // quantity * unit_price
            $table->json('product_snapshot')->nullable(); // Full product details at time of order
            $table->timestamps();

            // Indexes for better performance
            $table->index(['order_id', 'product_id']);
            $table->index('product_id'); // For product-based reports
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_meta');
    }
};