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
        Schema::create('customer_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->enum('message_type', ['incoming', 'outgoing', 'automated']);
            $table->text('message_content')->nullable();
            $table->json('attachments')->nullable();
            $table->json('message_data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->integer('response_time')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id']);
            $table->index(['client_id']);
            $table->index(['message_type']);
            $table->index(['created_at']);
            $table->index(['is_read']);
            $table->fullText(['message_content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_messages');
    }
};
