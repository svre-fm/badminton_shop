<?php

namespace App\Http\Controllers;

use App\Models\credit_debit_card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cards = Auth::user()->cards()->get()
        return view('cards.index',compact('cards'))
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('cards.create')
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data => $request->validate([
            'card_no'=>'required|digits_between:13,19',
            'expiry_date'=>'required|date|after:today',
        ]);

        $card = Auth::user()->cards()->firstOrCreate(
            ['card_no' => $data['card_no']],          //เงื่อนไขค้นหา
            ['expiry_date' => $data['expiry_date']]   //ค่าที่ใส่เฉพาะตอนสร้างใหม่
        );

        if(!$card->wasRecentlyCreated){
            return back()->withInput()->withErrors(['card_no' => 'Card already exist!']);
        }

        return redirect()->route('cards.index')->with('status', 'Card added!');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $card_no)
    {
        $card = Auth::user()->cards()->where('card_no',$card_no)->firstOrFail();
        return view('cards.edit', compact('card'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $card_no)
    {
        $card = Auth::user()->cards()->where('card_no',$card_no)->firstOrFail();

        $data = $request->validate([
            'expiry_date' => 'required|date|after:today',
        ]);

        DB::table('credit_debit_card')
            ->where('user_id',Auth::id())
            ->where('card_no',$card->card_no)
            ->update($data);

        return redirect()->route('cards.index')->with('status', 'Card updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $card_no)
    {
        $card = Auth::user()->cards()->where('card_no', $card_no)->firstOrFail();
        DB::table('credit_debit_cards')
            ->where('user_id', Auth::id())
            ->where('card_no', $card->card_no)
            ->delete();

        return redirect()->route('cards.index')->with('status', 'Card deleted!');    }
}
