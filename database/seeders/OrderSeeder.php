<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Database\Seeders\Support\SeedHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        $perDay = [];
        $rows = [];

        foreach (DB::table('users')->orderBy('id')->pluck('id') as $index => $userId) {
            $userAddresses = $addresses->get($userId);
            if (!$userAddresses || $userAddresses->isEmpty()) {
                continue;
            }

            $count = SeedHelper::weighted([0 => 20, 1 => 25, 2 => 25, 3 => 15, 4 => 10, 5 => 5]);
            if ($index === 0) {
                $count = max($count, 4); // demo user ให้มีประวัติสั่งซื้อพอทดสอบ
            }

            for ($i = 0; $i < $count; $i++) {
                $age = mt_rand(1, 280);
                $date = $today->copy()->subDays($age);
                $key = $date->format('Ymd');
                $perDay[$key] = ($perDay[$key] ?? 0) + 1;

                $address = SeedHelper::pick($userAddresses->all());

                $rows[] = [
                    'order_no' => sprintf('ORD%s-%04d', $key, $perDay[$key]),
                    'customer_id' => $userId,
                    'status' => $this->statusFor($age),
                    'order_date' => $date->toDateString(),
                    'delivery_id' => $address->delivery_id,
                    // snapshot ที่อยู่ ณ วันสั่งซื้อ
                    'delivery_name' => $address->name,
                    'delivery_phone' => $address->phone,
                    'delivery_address' => $address->address,
                    'total_price' => 0,
                ];
            }
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('orders')->insert($chunk);
        }
    }

    /** สถานะสมเหตุสมผลตามอายุของ order (วัน) */
    private function statusFor(int $ageDays): string
    {
        if ($ageDays <= 1) {
            return SeedHelper::weighted([
                SeedHelper::STATUS_PENDING => 60,
                SeedHelper::STATUS_PAID => 30,
                SeedHelper::STATUS_CANCELLED => 10,
            ]);
        }

        if ($ageDays <= 5) {
            return SeedHelper::weighted([
                SeedHelper::STATUS_PAID => 35,
                SeedHelper::STATUS_SHIPPED => 50,
                SeedHelper::STATUS_CANCELLED => 10,
                SeedHelper::STATUS_PENDING => 5,
            ]);
        }

        return SeedHelper::weighted([
            SeedHelper::STATUS_DELIVERED => 88,
            SeedHelper::STATUS_CANCELLED => 12,
        ]);
    }
}
