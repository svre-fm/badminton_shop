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
        Schema::create('orders', function (Blueprint $table) {
            $table->string('order_no')->primary();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('status');
            $table->date('order_date');
            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->foreign('delivery_id')->references('delivery_id')->on('delivery_addresses')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
