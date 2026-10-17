<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Database\Seeders\Support\SeedHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreditDebitCardSeeder extends Seeder
{
    public function run(): void
    {
        SeedHelper::seed(1003);

        $used = [];
        $rows = [];

        foreach (DB::table('users')->orderBy('id')->pluck('id') as $index => $userId) {
            // demo user มีบัตรเสมอ ส่วนคนอื่นมีบัตร ~65%
            if ($index !== 0 && !SeedHelper::chance(65)) {
                continue;
            }

            $count = SeedHelper::weighted([1 => 70, 2 => 30]);

            for ($i = 0; $i < $count; $i++) {
                do {
                    $cardNo = SeedHelper::cardNumber();
                } while (isset($used[$cardNo]));
                $used[$cardNo] = true;

                $rows[] = [
                    'user_id' => $userId,
                    'card_no' => $cardNo,
                    // หมดอายุอีก 4–60 เดือนข้างหน้า (บัตรยังใช้ได้ตอนสั่งซื้อย้อนหลัง)
                    'expiry_date' => Carbon::today()->addMonths(mt_rand(4, 60))->endOfMonth()->toDateString(),
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('credit_debit_cards')->insert($chunk);
        }
    }
}
