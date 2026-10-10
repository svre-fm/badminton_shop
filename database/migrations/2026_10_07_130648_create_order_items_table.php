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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id()->primary();                                   // PK ตัวเดียวของ order_item
            $table->string('order_no');
            $table->unsignedBigInteger('product_id');
            $table->string('color', 50);
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->integer('tension')->default(0);
            $table->foreign('order_no')->references('order_no')->on('orders')->cascadeOnDelete();
            $table->foreign(['product_id', 'color'])
                ->references(['product_id', 'color'])->on('productvariants');
            $table->unique(['order_no', 'product_id', 'color']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
