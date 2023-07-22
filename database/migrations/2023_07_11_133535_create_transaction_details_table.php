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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->string('transaction_type');
            $table->string('unit');
            $table->unsignedBigInteger('quantity');
            $table->float('price_per_unit', 8, 2)->nullable();
            $table->float('cost_per_unit', 8, 2)->nullable();
            $table->float('total_cost', 8, 2)->nullable();
            $table->float('total_price', 8, 2)->nullable();
            $table->float('remaining_balance', 8, 2);
            $table->string('product_id')->references('id')->on('products');
            $table->string('unit_id')->references('id')->on('unit');
            $table->string('branch_id')->references('id')->on('branch');
            $table->string('company_id')->references('id')->on('company');
            $table->string('supplier')->references('id')->on('suppliers');
            $table->boolean('stock');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
