<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('current_facebook_page_id')
                  ->nullable()
                  ->after('subscription_expires_at')
                  ->constrained('facebook_pages')
                  ->onDelete('set null');
            
            $table->index(['current_facebook_page_id']);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['current_facebook_page_id']);
            $table->dropIndex(['current_facebook_page_id']);
            $table->dropColumn('current_facebook_page_id');
        });
    }
};