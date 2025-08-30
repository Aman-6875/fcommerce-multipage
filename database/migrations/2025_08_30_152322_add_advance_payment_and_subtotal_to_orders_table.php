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
            // Add advance_payment column
            if (!Schema::hasColumn('orders', 'advance_payment')) {
                $table->decimal('advance_payment', 10, 2)->default(0)->after('total_amount');
            }
            
            // Add subtotal column (needed for order calculations)
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('total_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['advance_payment', 'subtotal']);
        });
    }
};
