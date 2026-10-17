<?php

namespace App\Http\Controllers;

use App\Models\products;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()
            ->favorites()
            ->with(['variants'])
            ->withAvg('reviews', 'star')
            ->orderBy('products.product_id')
            ->get();

        return view('favorites.index', compact('favorites'));
    }

    /**
     * กดหัวใจ: ถ้ายังไม่มีในรายการโปรดให้เพิ่ม ถ้ามีแล้วให้ลบ
     * ตาราง favorites มีคอลัมน์ customer_id, product_id (customer_id = users.id)
     */
    public function toggle(Request $request, products $product): JsonResponse
    {
        $row = [
            'customer_id' => $request->user()->id,
            'product_id'  => $product->product_id,
        ];

        if (DB::table('favorites')->where($row)->exists()) {
            DB::table('favorites')->where($row)->delete();
            $favorited = false;
        } else {
            DB::table('favorites')->insert($row);
            $favorited = true;
        }

        return response()->json(['favorited' => $favorited]);
    }
}