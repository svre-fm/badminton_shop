<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Database\Seeders\Support\SeedHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    private const COMMENTS = [
        'RACKET' => [
            'good' => ['ไม้สมดุลดี ตีสมแมชเบามือ คุ้มราคามาก', 'น้ำหนักกำลังดี สแมชได้หนักแน่น แนะนำเลยครับ', 'วัสดุแน่น งานประกอบเรียบร้อย ส่งไวด้วย'],
            'mid' => ['ไม้โอเค แต่ค่อนข้างหนักหัวไปหน่อยสำหรับผม', 'คุณภาพตามราคา ใช้ได้ทั่วไป'],
            'bad' => ['ได้ของมาแล้วรู้สึกไม่เหมาะกับสไตล์การเล่น', 'ด้ามจับไม่ค่อยพอดีมือ ผิดหวังนิดหน่อย'],
        ],
        'STRING' => [
            'good' => ['เอ็นเด้งดี คอนโทรลง่าย ทนกว่าที่คิด', 'เสียงตีกังวาน ตีสนุก จะซื้อซ้ำแน่นอน'],
            'mid' => ['เอ็นใช้ได้ แต่ขาดค่อนข้างไวเมื่อขึ้นเท็นชั่นสูง', 'โอเคตามราคา ไม่ได้โดดเด่นมาก'],
            'bad' => ['ขาดเร็วมาก ใช้ได้ไม่กี่ครั้ง', 'ไม่ตรงกับที่คาดหวัง ความเด้งน้อย'],
        ],
        'GRIP' => [
            'good' => ['กริปแห้ง ไม่ลื่นเวลาเหงื่อออก ใช้ดีมาก', 'พันง่าย นุ่มมือ ราคาไม่แพง'],
            'mid' => ['ใช้ได้ แต่เริ่มลื่นเร็วกว่าที่คิดนิดหน่อย'],
            'bad' => ['บางไปหน่อยและลื่นเร็ว ไม่ค่อยถูกใจ'],
        ],
        'SHUTTLECOCK' => [
            'good' => ['ลูกบินนิ่ง ทนมาก ตีได้หลายเกม', 'ความเร็วคงที่ ขนไม่หลุดง่าย คุ้มค่า'],
            'mid' => ['ลูกโอเค แต่บางลูกทิศทางไม่ค่อยนิ่ง'],
            'bad' => ['ขนหักเร็ว ตีไม่กี่เกมก็เสียหาย', 'ลูกบินไม่นิ่ง ไม่ค่อยประทับใจ'],
        ],
    ];

    public function run(): void
    {
        SeedHelper::seed(1009);

        $today = Carbon::today();

        // เฉพาะ order ที่จัดส่งสำเร็จ รวมรายการสินค้า (รวมสีที่ซ้ำสินค้าเดียวกันให้เหลือรีวิวเดียว)
        $lines = DB::table('orders')
            ->join('order_items', 'order_items.order_no', '=', 'orders.order_no')
            ->join('products', 'products.product_id', '=', 'order_items.product_id')
            ->where('orders.status', SeedHelper::STATUS_DELIVERED)
            ->whereNotNull('orders.customer_id')
            ->orderBy('orders.order_no')
            ->get([
                'orders.order_no',
                'orders.customer_id',
                'orders.order_date',
                'order_items.product_id',
                'products.product_type',
            ]);

        $seen = [];
        $rows = [];

        foreach ($lines as $line) {
            $key = "{$line->customer_id}|{$line->product_id}|{$line->order_no}";
            if (isset($seen[$key])) {
                continue; // unique (customer_id, product_id, order_no)
            }
            $seen[$key] = true;

            if (!SeedHelper::chance(60)) {
                continue; // ไม่ใช่ทุกคนรีวิว
            }

            $star = SeedHelper::weighted([5 => 45, 4 => 30, 3 => 15, 2 => 6, 1 => 4]);
            $tone = $star >= 4 ? 'good' : ($star === 3 ? 'mid' : 'bad');

            $date = Carbon::parse($line->order_date)->addDays(mt_rand(5, 20));
            if ($date->greaterThan($today)) {
                $date = $today->copy();
            }

            $rows[] = [
                'customer_id' => $line->customer_id,
                'product_id' => $line->product_id,
                'order_no' => $line->order_no,
                'star' => $star,
                'date' => $date->toDateString(),
                'comment' => SeedHelper::chance(85)
                    ? SeedHelper::pick(self::COMMENTS[$line->product_type][$tone])
                    : null,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('reviews')->insert($chunk);
        }
    }
}
