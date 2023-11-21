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
        Schema::create('unit', function (Blueprint $table) {
            $table->id();
            $table->string('unit_name');
            $table->unsignedBigInteger('parent_quantity')->nullable();
            $table->unsignedBigInteger('price_list_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('heirarchy');
            $table->boolean('is_default');
            $table->float('price_per_unit', 8, 2);
            $table->float('cost_per_unit', 8, 2);
            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('product_id')->references('id')->on('products')->onDelete('cascade')->nullable();
            $table->unsignedBigInteger('price_list_id')->references('id')->on('pricelist')->onDelete('cascade');
            $table->unsignedBigInteger('supplier_id')->references('id')->on('suppliers')->onDelete('cascade')->nullable();
            $table->unsignedBigInteger('branch_id')->references('id')->on('branch')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit');
    }
};
