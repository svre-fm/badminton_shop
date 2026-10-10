<?php

namespace Database\Seeders;

use Database\Seeders\Support\SeedHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryAddressSeeder extends Seeder
{
    public function run(): void
    {
        SeedHelper::seed(1002);

        $rows = [];

        foreach (DB::table('users')->orderBy('id')->get(['id', 'name']) as $user) {
            $count = SeedHelper::weighted([1 => 60, 2 => 30, 3 => 10]);

            for ($i = 0; $i < $count; $i++) {
                // ที่อยู่แรกใช้ชื่อเจ้าของบัญชี ที่เหลือมีโอกาสเป็นชื่อคนอื่น (เช่น ส่งให้ครอบครัว)
                $name = ($i === 0 || SeedHelper::chance(60)) ? $user->name : SeedHelper::person()['th'];

                $rows[] = [
                    'user_id' => $user->id,
                    'name' => $name,
                    'phone' => SeedHelper::phone(),
                    'address' => SeedHelper::address(),
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('delivery_addresses')->insert($chunk);
        }
    }
}
