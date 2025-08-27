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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('image_url')->nullable();
            $table->string('product_link')->nullable(); // Facebook post or ecommerce link
            $table->string('category')->nullable();
            $table->json('tags')->nullable(); // For search and categorization
            $table->boolean('is_active')->default(true);
            $table->boolean('track_stock')->default(true);
            $table->decimal('weight', 8, 2)->nullable(); // For shipping
            $table->json('specifications')->nullable(); // Technical specs as JSON
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['client_id', 'is_active']);
            $table->index(['client_id', 'category']);
            $table->fullText(['name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
