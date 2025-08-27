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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('facebook_page_id')->nullable()->after('client_id')->constrained('facebook_pages')->onDelete('cascade');
            $table->index(['client_id', 'facebook_page_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['facebook_page_id']);
            $table->dropIndex(['client_id', 'facebook_page_id', 'is_active']);
            $table->dropColumn('facebook_page_id');
        });
    }
};
