<?php

namespace Database\Seeders;

use Database\Seeders\Support\SeedHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        SeedHelper::seed(1007);

        $variants = DB::table('productvariants')
            ->join('products', 'products.product_id', '=', 'productvariants.product_id')
            ->leftJoin('badminton_rackets', 'badminton_rackets.product_id', '=', 'products.product_id')
            ->get([
                'productvariants.product_id',
                'productvariants.color',
                'products.price',
                'products.product_type',
                'badminton_rackets.max_tension',
            ])
            ->all();

        $items = [];
        $totals = [];

        foreach (DB::table('orders')->orderBy('order_no')->pluck('order_no') as $orderNo) {
            $count = SeedHelper::weighted([1 => 40, 2 => 30, 3 => 20, 4 => 10]);
            $total = 0;

            // pickMany ไม่ซ้ำ จึงไม่ชน unique (order_no, product_id, color)
            foreach (SeedHelper::pickMany($variants, min($count, count($variants))) as $variant) {
                $quantity = match ($variant->product_type) {
                    'RACKET' => mt_rand(1, 2),
                    'STRING' => mt_rand(1, 3),
                    'GRIP' => mt_rand(1, 5),
                    default => mt_rand(1, 6), // SHUTTLECOCK
                };

                $items[] = [
                    'order_no' => $orderNo,
                    'product_id' => $variant->product_id,
                    'color' => $variant->color,
                    'quantity' => $quantity,
                    'unit_price' => $variant->price, // snapshot ราคา ณ วันสั่งซื้อ
                    'tension' => $variant->product_type === 'RACKET'
                        ? SeedHelper::racketTension((int) $variant->max_tension)
                        : 0,
                ];
                $total += $quantity * (float) $variant->price;
            }

            $totals[$orderNo] = $total;
        }

        foreach (array_chunk($items, 200) as $chunk) {
            DB::table('order_items')->insert($chunk);
        }

        foreach ($totals as $orderNo => $total) {
            DB::table('orders')->where('order_no', $orderNo)->update(['total_price' => round($total, 2)]);
        }
    }
}
