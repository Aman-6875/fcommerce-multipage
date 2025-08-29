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
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('facebook_page_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Workflow name for client reference
            $table->text('description')->nullable();
            $table->json('definition'); // Complete workflow steps and configuration
            $table->json('supported_languages')->default('["en"]'); // ['en', 'bn']
            $table->string('default_language', 5)->default('en');
            $table->boolean('is_active')->default(true);
            $table->integer('version')->default(1); // For workflow versioning
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['client_id', 'facebook_page_id']);
            $table->index(['facebook_page_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};