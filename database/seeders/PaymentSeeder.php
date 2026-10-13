<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Database\Seeders\Support\SeedHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        SeedHelper::seed(1008);

        $cards = DB::table('credit_debit_cards')->get()->groupBy('user_id');

        // เฉพาะ order ที่จ่ายแล้ว (pending / cancelled ไม่มี payment)
        $orders = DB::table('orders')
            ->whereIn('status', [
                SeedHelper::STATUS_PAID,
                SeedHelper::STATUS_SHIPPED,
                SeedHelper::STATUS_DELIVERED,
            ])
            ->orderBy('order_no')
            ->get();

        $rows = [];

        foreach ($orders as $order) {
            $userCards = $order->customer_id ? $cards->get($order->customer_id) : null;
            $hasCard = $userCards && $userCards->isNotEmpty();

            $method = SeedHelper::weighted(
                $hasCard
                    ? [SeedHelper::METHOD_CARD => 45, SeedHelper::METHOD_PROMPTPAY => 35, SeedHelper::METHOD_TRANSFER => 20]
                    : [SeedHelper::METHOD_PROMPTPAY => 65, SeedHelper::METHOD_TRANSFER => 35]
            );

            $paidAt = SeedHelper::randomTime(Carbon::parse($order->order_date))->toDateTimeString();

            $rows[] = [
                'user_id' => $order->customer_id,
                'order_no' => $order->order_no,
                // ต้องเป็นบัตรของ user คนเดียวกัน (composite FK user_id + card_no)
                'card_no' => $method === SeedHelper::METHOD_CARD
                    ? SeedHelper::pick($userCards->all())->card_no
                    : null,
                'amount' => $order->total_price,
                'method' => $method,
                'created_at' => $paidAt,
                'updated_at' => $paidAt,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('payments')->insert($chunk);
        }
    }
}
