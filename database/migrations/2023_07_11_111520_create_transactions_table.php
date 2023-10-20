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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->string('transaction_type')->nullable();
            $table->boolean('stock')->nullable();
            $table->string('item_transaction_type')->nullable();
            $table->date('transaction_date')->nullable();
            $table->longText('transaction_desc')->nullable();
            $table->float('total_price', 8, 2)->nullable();
            $table->float('total_cost', 8, 2)->nullable();
            $table->unsignedBigInteger('supplier_id')->references('id')->on('suppliers')->nullable();
            $table->unsignedBigInteger('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('branch_id')->references('id')->on('branch');
            $table->unsignedBigInteger('company_id')->references('id')->on('company');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
