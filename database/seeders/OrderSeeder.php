<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Database\Seeders\Support\SeedHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * สร้างเฉพาะหัว order (total_price ใส่ 0 ไว้ก่อน)
 * แล้วให้ OrderItemSeeder คำนวณยอดรวมจากรายการสินค้าและอัปเดตกลับ
 */
class OrderSeeder extends Seeder
{
    public function run(): void
    {
        SeedHelper::seed(1006);

        $addresses = DB::table('delivery_addresses')->get()->groupBy('user_id');
        $today = Carbon::today();
        $rows = [];
        $demoStatuses = [
            SeedHelper::STATUS_TO_SHIP,
            SeedHelper::STATUS_TO_RECIEVE,
            SeedHelper::STATUS_COMPLETE,
            SeedHelper::STATUS_CANCELLED,
        ];

        foreach (DB::table('users')->orderBy('id')->pluck('id') as $index => $userId) {
            $userAddresses = $addresses->get($userId);
            if (! $userAddresses || $userAddresses->isEmpty()) {
                continue;
            }

            $count = SeedHelper::weighted([0 => 20, 1 => 25, 2 => 25, 3 => 15, 4 => 10, 5 => 5]);
            if ($index === 0) {
                $count = max($count, count($demoStatuses)); // demo user ให้มีครบทุกสถานะ
            }

            for ($i = 0; $i < $count; $i++) {
                $status = $index === 0 && $i < count($demoStatuses)
                    ? $demoStatuses[$i]
                    : $this->randomStatus();
                $age = $this->ageForStatus($status);
                $date = $today->copy()->subDays($age);
                $address = SeedHelper::pick($userAddresses->all());

                $rows[] = [
                    'order_no' => 'ORD-'.$date->format('Ymd').'-'.strtoupper(Str::random(10)),
                    'customer_id' => $userId,
                    'status' => $status,
                    'order_date' => SeedHelper::randomTime($date)->toDateTimeString(),
                    'delivery_id' => $address->delivery_id,
                    'total_price' => 0,
                ];
            }
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('orders')->insert($chunk);
        }
    }

    private function randomStatus(): string
    {
        return SeedHelper::weighted([
            SeedHelper::STATUS_TO_SHIP => 40,
            SeedHelper::STATUS_TO_RECIEVE => 30,
            SeedHelper::STATUS_COMPLETE => 18,
            SeedHelper::STATUS_CANCELLED => 12,
        ]);
    }

    private function ageForStatus(string $status): int
    {
        return match ($status) {
            SeedHelper::STATUS_TO_SHIP => mt_rand(1, 5),
            SeedHelper::STATUS_TO_RECIEVE => mt_rand(2, 10),
            SeedHelper::STATUS_COMPLETE => mt_rand(6, 280),
            SeedHelper::STATUS_CANCELLED => mt_rand(1, 280),
            default => throw new \InvalidArgumentException("Unsupported order status: {$status}"),
        };
    }
}
