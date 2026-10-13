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
        Schema::create('badminton_rackets', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->primary();
            $table->string('balance_point',30);
            $table->string('shaft',30);
            $table->string('flexibility',30);
            $table->string('weight',20);
            $table->string('grip_size',10);
            $table->integer('max_tension');
            
            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badminton_rackets');
    }
};
