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
        Schema::table('conversation_states', function (Blueprint $table) {
            if (!Schema::hasColumn('conversation_states', 'page_customer_id')) {
                $table->unsignedBigInteger('page_customer_id')->nullable()->after('customer_id');
                $table->foreign('page_customer_id')->references('id')->on('page_customers')->onDelete('cascade');
                $table->index('page_customer_id');
            }
        });

        // Populate page_customer_id from existing data
        DB::transaction(function () {
            $conversations = DB::table('conversation_states')
                ->whereNotNull('customer_id')
                ->whereNull('page_customer_id')
                ->get();

            foreach ($conversations as $conversation) {
                // Find page_customer record for this customer
                $pageCustomer = DB::table('page_customers')
                    ->where('customer_id', $conversation->customer_id)
                    ->first();

                if ($pageCustomer) {
                    DB::table('conversation_states')
                        ->where('id', $conversation->id)
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
        Schema::table('conversation_states', function (Blueprint $table) {
            $table->dropForeign(['page_customer_id']);
            $table->dropIndex(['page_customer_id']);
            $table->dropColumn('page_customer_id');
        });
    }
};
