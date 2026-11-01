<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unique(
                ['order_no', 'product_id', 'color', 'tension'],
                'order_items_order_product_color_tension_unique'
            );
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique('order_items_order_no_product_id_color_unique');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unique(
                ['order_no', 'product_id', 'color'],
                'order_items_order_no_product_id_color_unique'
            );
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique('order_items_order_product_color_tension_unique');
        });
    }
};
