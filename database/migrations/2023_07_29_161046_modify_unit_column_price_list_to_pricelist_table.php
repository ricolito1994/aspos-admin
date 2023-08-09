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
        Schema::table('unit', function (Blueprint $table) {
            //
            //$table->dropColumn('price_list_id');
            //$table->unsignedBigInteger('price_list_id')->references('id')->on('unit')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricelist', function (Blueprint $table) {
            //
            //$table->unsignedBigInteger('price_list_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }
};
