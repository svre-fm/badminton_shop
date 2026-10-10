<?php

namespace Database\Seeders;

use Database\Seeders\Support\SeedHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        SeedHelper::seed(1004);

        $productIds = DB::table('products')->pluck('product_id')->all();
        $rows = [];

        foreach (DB::table('users')->orderBy('id')->pluck('id') as $userId) {
            $count = SeedHelper::weighted([0 => 15, 1 => 15, 2 => 20, 3 => 20, 4 => 15, 6 => 10, 8 => 5]);

            foreach (SeedHelper::pickMany($productIds, min($count, count($productIds))) as $productId) {
                $rows[] = ['customer_id' => $userId, 'product_id' => $productId];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('favorites')->insert($chunk);
        }
    }
}
