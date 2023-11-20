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
        Schema::table('users', function(Blueprint $table) {
            $table->boolean('is_active')->nullable();
            $table->date('inactive_date')->nullable();
            $table->unsignedBigInteger('created_by')
                ->references('id')
                ->on('users')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('inactive_date');
            $table->dropColumn('created_by');
        });
    }
};
