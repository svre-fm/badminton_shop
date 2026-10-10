<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\products; // ใช้ Model 'products' ของคุณ

class ProductTest extends TestCase
{
    /**
     * ทดสอบการดึงข้อมูลและ Filter สเปคไม้แบดมินตัน
     */
    public function test_can_filter_rackets_by_balance_point(): void
    {
        // --------------------------------------------------
        // Arrange: สร้างข้อมูลจำลองลง DB สำหรับเคสนี้โดยเฉพาะ
        // --------------------------------------------------
        
        // ไม้แบดที่ตรงเงื่อนไข (Head-heavy)
        $racket1 = products::create([
            'name' => 'Yonex Astrox 88D',
            'brand' => 'YONEX',
            'price' => 500,
            'image' => 'racket1.jpg',
            'product_type' => 'RACKET'
        ]);
        badminton_rackets::create([
            'product_id' => $racket1->product_id,
            'balance_point' => 'Head-heavy',
            'weight' => '4U'
        ]);

        // ไม้แบดที่ไม่ตรงเงื่อนไข (Even-balance)
        $racket2 = products::create([
            'name' => 'Victor Arcsaber 11',
            'brand' => 'VICTOR',
            'price' => 450,
            'image' => 'racket2.jpg',
            'product_type' => 'RACKET'
        ]);
        badminton_rackets::create([
            'product_id' => $racket2->product_id,
            'balance_point' => 'Even-balance',
            'weight' => '3U'
        ]);

        // --------------------------------------------------
        // Act: ยิง Request กรองเฉพาะ Head-heavy
        // --------------------------------------------------
        $response = $this->getJson('/products?type=RACKET&balance_point=Head-heavy');

        // --------------------------------------------------
        // Assert: ตรวจสอบผลลัพธ์
        // --------------------------------------------------
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data') // ต้องเจอแค่ 1 รายการ
                 ->assertJsonPath('data.0.name', 'Yonex Astrox 88D'); // ต้องเป็นชิ้นที่ตรงเงื่อนไข
    }
}
