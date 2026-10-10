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
        Schema::create('racket_tensions', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->string('tension',20);
            $table->primary(['product_id', 'tension']);

            $table->foreign('product_id')
            ->references('product_id')
            ->on('badminton_rackets')
            ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('racket_tensions');
    }
};
