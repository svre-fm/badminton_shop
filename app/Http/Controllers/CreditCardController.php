<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CreditDebitCard;
use Illuminate\Support\Facades\Auth;

class CreditCardController extends Controller
{
    /**
     * ดึงข้อมูลบัตรเครดิต/เดบิตทั้งหมดของลูกค้าที่ล็อกอินอยู่
     */
    public function index()
    {
        // ดึงข้อมูลบัตรทั้งหมดที่ผูกกับ user_id (หรือ customer_id) ของผู้ใช้ปัจจุบัน
        $creditCards = Auth::user()->creditCards()->get();

        return view('credit-cards.index', compact('creditCards'));
    }

    /**
     * แสดงฟอร์มสำหรับเพิ่มข้อมูลบัตรเครดิต/เดบิตใหม่
     */
    public function create()
    {
        return view('shop.add-credit-card');
    }

    /**
     * บันทึกข้อมูลบัตรลงฐานข้อมูล
     */
    public function store(Request $request)
    {
        // expiry_date จาก input type="month" อยู่ในรูปแบบ YYYY-MM
        $validated = $request->validate([
            'card_no' => ['required', 'string', 'regex:/^[0-9]{14,19}$/'],
            'expiry_date' => 'required|date_format:Y-m',
        ]);

        // สร้างข้อมูลบัตรใหม่ โดย Eloquent จะใส่ค่า Foreign Key (user_id/customer_id) ให้ทันที
        Auth::user()->cards()->create([
            'card_no' => $validated['card_no'],
            'expiry_date' => $validated['expiry_date'].'-01',
        ]);
        return redirect()->route('checkout');

    }

    /**
     * แสดงฟอร์มสำหรับแก้ไขวันหมดอายุของบัตร
     * หมายเหตุ: ส่วนใหญ่หมายเลขบัตรจะไม่ให้แก้ หากกรอกผิดจะให้ลบแล้วเพิ่มใหม่
     * แต่เราเตรียมฟอร์มไว้สำหรับการแก้ไขวันหมดอายุได้ครับ
     */
    public function edit(string $number)
    {
        // ค้นหาบัตรจาก "หมายเลขบัตร" (ซึ่งเป็นส่วนหนึ่งของ Composite PK) และต้องเป็นของผู้ใช้นี้เท่านั้น
        $creditCard = Auth::user()->creditCards()->where('number', $number)->firstOrFail();

        return view('credit-cards.edit', compact('creditCard'));
    }


    /**
     * อัปเดตข้อมูลบัตร (เช่น วันหมดอายุ) ในฐานข้อมูล
     */
    public function update(Request $request, string $number)
    {
        // ค้นหาบัตรเพื่อให้แน่ใจว่าเป็นของลูกค้าที่กำลังล็อกอิน
        $creditCard = Auth::user()->creditCards()->where('number', $number)->firstOrFail();

        $validated = $request->validate([
            // อนุญาตให้อัปเดตเฉพาะวันหมดอายุ
            'expiry_date' => 'required|date',
        ]);

        $creditCard->update($validated);

        return redirect()->route('credit-cards.index')
            ->with('status', 'Card details updated successfully!');
    }

    /**
     * ลบข้อมูลบัตรออกจากฐานข้อมูล
     */
    public function destroy(string $cardToken)
    {
        $user = Auth::user();
        $creditCard = $user->cards()->get()->first(function ($card) use ($user, $cardToken) {
            $expectedToken = hash_hmac(
                'sha256',
                $user->id.':'.$card->card_no,
                (string) config('app.key')
            );

            return hash_equals($expectedToken, $cardToken);
        });

        abort_unless($creditCard, 404);

        $creditCard->delete();

        return redirect()->route('checkout')
            ->with('status', 'Credit/Debit card removed successfully.');
    }


}