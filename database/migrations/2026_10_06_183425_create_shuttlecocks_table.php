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
        Schema::create('shuttlecocks', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->primary();
            $table->string('type', 50);
            $table->string('speed_rating', 20);

            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shuttlecocks');
    }
};
