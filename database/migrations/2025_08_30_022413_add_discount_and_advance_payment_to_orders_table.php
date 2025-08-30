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
            // Check if column exists before adding
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('shipping_charge');
            }
            if (!Schema::hasColumn('orders', 'discount_type')) {
                $table->enum('discount_type', ['fixed', 'percentage'])->default('fixed')->after('discount_amount');
            }
            if (!Schema::hasColumn('orders', 'tracking_token')) {
                $table->string('tracking_token', 50)->nullable()->after('delivered_at');
                $table->index(['tracking_token']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'tracking_token')) {
                $table->dropIndex(['tracking_token']);
            }
            $table->dropColumn([
                'discount_amount', 
                'discount_type',
                'tracking_token'
            ]);
        });
    }
};
