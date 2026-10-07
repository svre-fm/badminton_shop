<?php

use App\Models\orders;
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
            $table->string('order_no')->unique();
            $table->string('card_id',20)->nullable();
            $table->decimal('amount',10,2);
            $table->string('method',30);
            $table->timestamps();
            $table->foreign('order_no')->references('order_no')->on('orders');
            $table->foreign('card_id')->references('card_no')->on('credit_debit_cards');
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
