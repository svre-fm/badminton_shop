<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\delivery_address;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{


    /**
     * ดึงข้อมูลที่อยู่จัดส่งทั้งหมดของลูกค้าที่ล็อกอินอยู่
     */
    public function index()
    {
        // ดึงเฉพาะที่อยู่ของลูกค้าที่ล็อกอิน (Auth::user())
        // ถ้าระบบไม่ได้ใช้ระบบ Auth มาตรฐาน ให้ใช้เป็นการรับ $customer_id ผ่าน URL ก็ได้ครับ
        $addresses = Auth::user()->Delivery_address()->get();

        return view('addresses.index', compact('addresses'));
    }

    /**
     * แสดงฟอร์มสร้างที่อยู่จัดส่งใหม่
     */
    public function create()
    {
        return view('addresses.create');
    }

    /**
     * บันทึกข้อมูลที่อยู่จัดส่งใหม่ลงฐานข้อมูล
     */
    public function store(Request $request)
    {
        // ตรวจสอบข้อมูลจากฟอร์ม (ตามโครงสร้าง DB: name, phone, address [not null])
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        // สร้างที่อยู่ใหม่ โดยให้เชื่อมโยง (foreign key) กับผู้ใช้ที่ล็อกอินอยู่โดยอัตโนมัติ
        Auth::user()->Delivery_address()->create($validated);

        return redirect()->route('checkout');
    }

    /**
     * แสดงฟอร์มแก้ไขที่อยู่จัดส่ง
     */
    public function edit(string $id)
    {
        // ค้นหาที่อยู่จาก ID โดยต้องมั่นใจว่าเป็นที่อยู่ของลูกค้าคนนี้เท่านั้น
        $address = Auth::user()->Delivery_address()->findOrFail($id);
        return view('shop.address-form', compact('address'));
    }

    /**
     * อัปเดตข้อมูลที่อยู่จัดส่งในฐานข้อมูล
     */
    public function update(Request $request, string $id)
    {
        // ค้นหาที่อยู่ของลูกค้าที่ล็อกอินอยู่
        $address = Auth::user()->Delivery_address()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        // อัปเดตข้อมูล
        $address->update($validated);

        return redirect()->route('addresses.index')
            ->with('status', 'Address updated successfully!');
    }

    /**
     * ลบที่อยู่จัดส่งออกจากฐานข้อมูล
     */
    public function destroy(string $id)
    {
        // ค้นหาและตรวจสอบสิทธิ์ก่อนลบ
        $address = Auth::user()->Delivery_address()->findOrFail($id);

        $address->delete();

        return redirect()->route('checkout')
            ->with('status', 'Address deleted successfully!');
    }

}