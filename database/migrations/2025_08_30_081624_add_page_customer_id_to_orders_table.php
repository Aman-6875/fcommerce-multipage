<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'page_customer_id')) {
                $table->unsignedBigInteger('page_customer_id')->nullable()->after('customer_id');
                $table->foreign('page_customer_id')->references('id')->on('page_customers')->onDelete('set null');
                $table->index('page_customer_id');
            }
        });

        // Populate page_customer_id from existing Facebook orders
        DB::transaction(function () {
            $orders = DB::table('orders')
                ->whereNotNull('customer_id')
                ->whereNotNull('facebook_page_id')
                ->get();

            foreach ($orders as $order) {
                $pageCustomer = DB::table('page_customers')
                    ->where('customer_id', $order->customer_id)
                    ->where('facebook_page_id', $order->facebook_page_id)
                    ->first();

                if ($pageCustomer) {
                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['page_customer_id' => $pageCustomer->id]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['page_customer_id']);
            $table->dropIndex(['page_customer_id']);
            $table->dropColumn('page_customer_id');
        });
    }
};
