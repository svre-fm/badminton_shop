<?php

namespace App\Http\Controllers;

use App\Models\products; // เรียกใช้ Model 'products' ของคุณ
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $typeConfigs = [
        'RACKET' => [
            'relation' => 'badmintonRacket',
            'with'     => ['badmintonRacket', 'variants'],
            'fields'   => ['balance_point', 'shaft', 'flexibility', 'weight', 'grip_size']
        ],
        'STRING' => [
            'relation' => 'badmintonString',
            'with'     => ['badmintonString', 'variants'],
            'fields'   => ['thickness', 'string_characteristic']
        ],
        'SHUTTLECOCK' => [
            'relation' => 'shuttlecock',
            'with'     => ['shuttlecock', 'variants'],
            'fields'   => ['type', 'speed_rating']
        ],
        'GRIP' => [
            'relation' => 'grip',
            'with'     => ['grip', 'variants'],
            'fields'   => ['type', 'material']
        ],
    ];

    public function index(Request $request)
    {
        $selectedType = $request->query('type') ? strtoupper($request->query('type')) : null;

        // เรียกใช้ products Model
        $query = products::query();

        // ----------------------------------------------------
        // A. กรองข้อมูลตารางหลัก (products) และสี (variants)
        // ----------------------------------------------------
        if ($request->filled('min_price') && $request->filled('max_price')) {
            $query->whereBetween('price', [$request->min_price, $request->max_price]);
        }

        if ($request->filled('brand')) {
            $brands = (array) $request->brand;
            $query->whereIn('brand', $brands);
        }

        // กรองสีผ่าน relation 'variants' ใน Model ของคุณ
        if ($request->filled('color')) {
            $colors = (array) $request->color;
            $query->whereHas('variants', function ($q) use ($colors) {
                $q->whereIn('color', $colors);
            });
        }

        // ----------------------------------------------------
        // B. กรองข้อมูลสเปคตารางย่อยตามประเภทสินค้า
        // ----------------------------------------------------
        if ($selectedType && isset($this->typeConfigs[$selectedType])) {
            $config = $this->typeConfigs[$selectedType];

            $query->where('product_type', $selectedType);
            $query->with($config['with']);

            $query->whereHas($config['relation'], function ($q) use ($request, $config) {
                foreach ($config['fields'] as $field) {
                    if ($request->filled($field)) {
                        $value = $request->input($field);

                        if (is_array($value)) {
                            $q->whereIn($field, $value);
                        } else {
                            $q->where($field, $value);
                        }
                    }
                }
            });
        } else {
            // ดึงเฉพาะ Relation ที่ตรงกับที่มีใน Model products
            $query->with(['badmintonRacket', 'badmintonString', 'shuttlecock', 'grip', 'variants']);
        }

        $sort = $request->query('sort', 'latest');

        switch ($sort) {
            case 'price_low':
                // ราคาน้อยไปมาก
                $query->orderBy('price', 'asc');
                break;

            case 'price_high':
                // ราคามากไปน้อย
                $query->orderBy('price', 'desc');
                break;

            case 'top_sale':
                // ขายดีที่สุด: ใช้ withSum คำนวณผลรวมของคอลัมน์ quantity ใน relation 'orderItems'
                $query->withSum('orderItems', 'quantity')
                      ->orderBy('order_items_sum_quantity', 'desc');
                break;

            case 'latest':
            default:
                // ใหม่ล่าสุด: เรียงตาม product_id จากมากไปน้อย
                $query->latest('product_id');
                break;
        }

        // เรียงตาม primaryKey 'product_id'
        $products = $query->paginate(12)->withQueryString();

        return view('products.index', compact('products', 'selectedType'));
    }
}
