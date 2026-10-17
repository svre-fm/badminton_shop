<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Database\Seeders\Support\SeedHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        SeedHelper::seed(1001);

        $password = Hash::make(SeedHelper::DEMO_PASSWORD); // hash ครั้งเดียวใช้ทุกคน
        $now = Carbon::now();
        $rows = [];

        // demo user สำหรับ login ทดสอบ
        $rows[] = [
            'name' => 'ลูกค้าทดสอบ',
            'email' => SeedHelper::DEMO_EMAIL,
            'password' => $password,
            'remember_token' => null,
            'created_at' => $now->copy()->subDays(300)->toDateTimeString(),
            'updated_at' => $now->copy()->subDays(300)->toDateTimeString(),
        ];

        for ($i = 1; $i < SeedHelper::USER_COUNT; $i++) {
            $person = SeedHelper::person();
            $created = $now->copy()->subDays(mt_rand(30, 360))->setTime(mt_rand(8, 22), mt_rand(0, 59));

            $rows[] = [
                'name' => $person['th'],
                'email' => "{$person['first_en']}.{$person['last_en']}{$i}@example.com",
                'password' => $password,
                'remember_token' => null,
                'created_at' => $created->toDateTimeString(),
                'updated_at' => $created->toDateTimeString(),
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('users')->insert($chunk);
        }
    }
}
