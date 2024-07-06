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
        //
        Schema::table('transactions', function(Blueprint $table) {
            $table->boolean('is_done_pending_transaction')->nullable();
        });
        Schema::table('transaction_details', function(Blueprint $table) {
            $table->boolean('is_done_pending_transaction')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('transactions', function(Blueprint $table) {
            $table->dropColumn('is_done_pending_transaction');
        });
        Schema::table('transaction_details', function(Blueprint $table) {
            $table->dropColumn('is_done_pending_transaction');
        });
    }
};
