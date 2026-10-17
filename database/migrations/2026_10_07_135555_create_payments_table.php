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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('order_no')->unique();
            $table->string('card_no', 20)->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('method', 30);
            $table->timestamps();
            $table->foreign('order_no')->references('order_no')->on('orders');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign(['user_id', 'card_no'])
                ->references(['user_id', 'card_no'])
                ->on('credit_debit_cards')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
