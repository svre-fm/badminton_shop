<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Database\Seeders\Support\SeedHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartItemSeeder extends Seeder
{
    public function run(): void
    {
        SeedHelper::seed(1005);

        // เฉพาะ variant ที่มีของ เพื่อให้ตะกร้าดูสมเหตุสมผล
        $variants = DB::table('productvariants')->where('stock', '>', 0)->get()->all();
        $rows = [];

        foreach (DB::table('users')->orderBy('id')->pluck('id') as $index => $userId) {
            // demo user มีของในตะกร้าเสมอ ส่วนคนอื่น ~40%
            if ($index !== 0 && !SeedHelper::chance(40)) {
                continue;
            }

            $count = SeedHelper::weighted([1 => 40, 2 => 30, 3 => 20, 4 => 10]);

            foreach (SeedHelper::pickMany($variants, min($count, count($variants))) as $variant) {
                $at = Carbon::now()->subDays(mt_rand(0, 30))->subMinutes(mt_rand(0, 1439))->toDateTimeString();

                $rows[] = [
                    'customer_id' => $userId,
                    'product_id' => $variant->product_id,
                    'color' => $variant->color,
                    'quantity' => mt_rand(1, min(3, $variant->stock)),
                    'created_at' => $at,
                    'updated_at' => $at,
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('cart_items')->insert($chunk);
        }
    }
}