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
        Schema::table('credit_debit_cards', function (Blueprint $table) {
            $table->unique('card_no', 'credit_debit_cards_card_no_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_debit_cards', function (Blueprint $table) {
            $table->dropUnique('credit_debit_cards_card_no_unique');
        });
    }
};
