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
            $table->unsignedBigInteger('discount_type')->nullable();
            $table->float('discount_percent', 8, 2)->nullable();
            $table->float('vat', 8, 2)->nullable();
            $table->float('final_amt_received', 8, 2)->nullable();
            $table->float('amt_received', 8, 2)->nullable();
            $table->float('amt_released', 8, 2)->nullable();
            $table->float('change', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('discount');
            $table->dropColumn('vat');
            $table->dropColumn('amt_released');
            $table->dropColumn('amt_paid');
            $table->dropColumn('change');
        });
    }
};
