<?php

namespace Database\Seeders\Support;

use Carbon\Carbon;

/**
 * ตัวช่วยสำหรับ seeder (ไม่พึ่ง Faker) — ใช้ mt_rand ที่ seed ไว้
 * ทำให้รันซ้ำแล้วได้ข้อมูลชุดเดิมทุกครั้ง
 */
final class SeedHelper
{
    /** จำนวน user ทั้งหมด (รวม demo user 1 คน) */
    public const USER_COUNT = 50;

    public const DEMO_EMAIL = 'customer@example.com';
    public const DEMO_PASSWORD = 'password';

    /** สถานะ order ที่ใช้ในระบบ (ปรับให้ตรงกับแอปได้ที่นี่ที่เดียว) */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    /** วิธีชำระเงิน */
    public const METHOD_CARD = 'CREDIT_CARD';
    public const METHOD_PROMPTPAY = 'PROMPTPAY';
    public const METHOD_TRANSFER = 'BANK_TRANSFER';

    private const FIRST_NAMES = [
        ['สมชาย', 'Somchai'], ['สมหญิง', 'Somying'], ['ธนากร', 'Thanakorn'],
        ['กฤษฎา', 'Kritsada'], ['ศิริพร', 'Siriporn'], ['วรรณา', 'Wanna'],
        ['ณัฐพล', 'Nattapon'], ['ปิยะ', 'Piya'], ['อารีรัตน์', 'Areerat'],
        ['พงศกร', 'Phongsakorn'], ['จิราพร', 'Jiraporn'], ['ชัยวัฒน์', 'Chaiwat'],
        ['สุภาพร', 'Supaporn'], ['นภัสสร', 'Napatsorn'], ['อนุชา', 'Anucha'],
        ['รัชนี', 'Ratchanee'], ['วีรยุทธ', 'Weerayut'], ['กานต์', 'Kan'],
        ['พิมพ์ชนก', 'Pimchanok'], ['ภูริทัต', 'Phuritat'],
    ];

    private const LAST_NAMES = [
        ['ใจดี', 'Jaidee'], ['รักษาสัตย์', 'Raksasat'], ['แสงทอง', 'Saengthong'],
        ['ศรีสุข', 'Srisuk'], ['วงศ์ใหญ่', 'Wongyai'], ['พิทักษ์', 'Pitak'],
        ['เจริญผล', 'Charoenphon'], ['บุญมา', 'Boonma'], ['มั่นคง', 'Mankhong'],
        ['สุวรรณ', 'Suwan'], ['ทองคำ', 'Thongkham'], ['ประเสริฐ', 'Prasert'],
        ['คงเจริญ', 'Khongcharoen'], ['อินทร์แก้ว', 'Inkaew'], ['ชัยมงคล', 'Chaimongkol'],
        ['สุขสวัสดิ์', 'Suksawat'], ['เพชรรัตน์', 'Phetrat'], ['นาคประเสริฐ', 'Nakprasert'],
    ];

    private const AREAS = [
        ['แขวงจตุจักร', 'เขตจตุจักร', 'กรุงเทพมหานคร', '10900'],
        ['แขวงบางนา', 'เขตบางนา', 'กรุงเทพมหานคร', '10260'],
        ['แขวงห้วยขวาง', 'เขตห้วยขวาง', 'กรุงเทพมหานคร', '10310'],
        ['แขวงลาดพร้าว', 'เขตลาดพร้าว', 'กรุงเทพมหานคร', '10230'],
        ['ตำบลสุเทพ', 'อำเภอเมืองเชียงใหม่', 'เชียงใหม่', '50200'],
        ['ตำบลช้างคลาน', 'อำเภอเมืองเชียงใหม่', 'เชียงใหม่', '50100'],
        ['ตำบลในเมือง', 'อำเภอเมืองขอนแก่น', 'ขอนแก่น', '40000'],
        ['ตำบลหาดใหญ่', 'อำเภอหาดใหญ่', 'สงขลา', '90110'],
        ['ตำบลบางพูด', 'อำเภอปากเกร็ด', 'นนทบุรี', '11120'],
        ['ตำบลในเมือง', 'อำเภอเมืองนครราชสีมา', 'นครราชสีมา', '30000'],
        ['ตำบลเสม็ด', 'อำเภอเมืองชลบุรี', 'ชลบุรี', '20000'],
        ['ตำบลบางปลา', 'อำเภอบางพลี', 'สมุทรปราการ', '10540'],
    ];

    private const ROADS = [
        'สุขุมวิท', 'ลาดพร้าว', 'พหลโยธิน', 'รัชดาภิเษก', 'เพชรบุรี',
        'นิมมานเหมินท์', 'มิตรภาพ', 'เจริญกรุง', 'ศรีนครินทร์', 'ห้วยแก้ว',
    ];

    public static function seed(int $seed): void
    {
        mt_srand($seed);
    }

    public static function pick(array $items)
    {
        return $items[mt_rand(0, count($items) - 1)];
    }

    /** สุ่ม $n รายการแบบไม่ซ้ำ */
    public static function pickMany(array $items, int $n): array
    {
        $items = array_values($items);
        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        return array_slice($items, 0, max(0, $n));
    }

    public static function chance(int $percent): bool
    {
        return mt_rand(1, 100) <= $percent;
    }

    public static function racketTension(int $maxTension): int
    {
        if ($maxTension < 18) {
            throw new \InvalidArgumentException('Racket maximum tension must be at least 18 lbs.');
        }

        return mt_rand(18, $maxTension);
    }

    /** สุ่มตามน้ำหนัก เช่น [5 => 45, 4 => 30] คืนค่า key */
    public static function weighted(array $weights)
    {
        $roll = mt_rand(1, (int) array_sum($weights));
        foreach ($weights as $key => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $key;
            }
        }

        return array_key_first($weights);
    }

    /** @return array{th: string, first_en: string, last_en: string} */
    public static function person(): array
    {
        [$firstTh, $firstEn] = self::pick(self::FIRST_NAMES);
        [$lastTh, $lastEn] = self::pick(self::LAST_NAMES);

        return [
            'th' => "{$firstTh} {$lastTh}",
            'first_en' => strtolower($firstEn),
            'last_en' => strtolower($lastEn),
        ];
    }

    public static function phone(): string
    {
        return '0' . self::pick([6, 8, 9]) . str_pad((string) mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    public static function address(): string
    {
        [$sub, $district, $province, $zip] = self::pick(self::AREAS);
        $house = mt_rand(1, 999) . (self::chance(30) ? '/' . mt_rand(1, 99) : '');

        return "{$house} ซอย" . mt_rand(1, 60) . ' ถนน' . self::pick(self::ROADS)
            . " {$sub} {$district} {$province} {$zip}";
    }

    /** เลขบัตรสุ่มที่ผ่าน Luhn (ไม่ใช่เลขบัตรจริง) */
    public static function cardNumber(): string
    {
        $number = self::pick(['4', '5']);
        while (strlen($number) < 15) {
            $number .= mt_rand(0, 9);
        }

        $sum = 0;
        for ($i = 0; $i < 15; $i++) {
            $digit = (int) $number[14 - $i];
            if ($i % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return $number . ((10 - $sum % 10) % 10);
    }

    public static function randomTime(Carbon $date): Carbon
    {
        return $date->copy()->setTime(mt_rand(8, 22), mt_rand(0, 59), mt_rand(0, 59));
    }
}
