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
        Schema::create('pricelist', function (Blueprint $table) {
            $table->id();
            $table->string('pricelist_name');
            $table->unsignedBigInteger('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedBigInteger('supplier_id')->references('id')->on('suppliers')->onDelete('cascade')->nullable();
            $table->unsignedBigInteger('branch_id')->references('id')->on('branch')->onDelete('cascade');
            $table->boolean('is_default');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricelist');
    }
};
