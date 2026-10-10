<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use App\Models\User;


class ProfileController extends Controller
{

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        return view('proflie.show', [
            'customer' => $request -> user(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        return view('proflie.edit', [
            'customer' => $request -> user(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $customer = $request->user();
        $validted = $request->validate([
            'name'  => 'require|string|max:100'
        ])
        return redirect() -> route('proflie.show') -> with('success' , 'Proflie updated!')
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $customer = $request->user();
        try{
            Auth::logout();
            $customer->delete();
        }catch(QueryException $e){
            Auth::login($customer);
            return back()->with([
                'message' => 'The account cannot be deleted due to existing order history in the system.'
            ])
        }

        //ล้างข้อมูลทั้งหมดในเซสชันปัจจุบันและเปลี่ยน Session ID ใหม่
        $request->session()->invalidate();
        //รีเซ็ตและสร้าง CSRF Token ตัวใหม่ (คำสั่งที่คุณสอบถาม)
        $request->session()->regenerateToken();
        //เปลี่ยนเส้นทางผู้ใช้กลับไปที่หน้าแรก
        return redirect('/')->with('success','Proflie deleted!');
    }
}
