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
        Schema::table('customer_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_messages', 'page_customer_id')) {
                $table->unsignedBigInteger('page_customer_id')->nullable()->after('customer_id');
                $table->foreign('page_customer_id')->references('id')->on('page_customers')->onDelete('cascade');
                $table->index('page_customer_id');
            }
        });

        // Populate page_customer_id from existing data
        DB::transaction(function () {
            $messages = DB::table('customer_messages')
                ->whereNotNull('customer_id')
                ->whereNull('page_customer_id')
                ->get();

            foreach ($messages as $message) {
                // Find page_customer record for this customer
                $pageCustomer = DB::table('page_customers')
                    ->where('customer_id', $message->customer_id)
                    ->first();

                if ($pageCustomer) {
                    DB::table('customer_messages')
                        ->where('id', $message->id)
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
        Schema::table('customer_messages', function (Blueprint $table) {
            $table->dropForeign(['page_customer_id']);
            $table->dropIndex(['page_customer_id']);
            $table->dropColumn('page_customer_id');
        });
    }
};
