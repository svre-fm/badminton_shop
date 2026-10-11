<?php

namespace App\Http\Controllers;

use App\Models\products; // เรียกใช้ Model 'products' ของคุณ
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $typeConfigs = [
        'RACKET' => [
            'table'    => 'badminton_racket',
            'relation' => 'badmintonRacket',
            'with'     => ['badmintonRacket'],
            'fields'   => [   // คอลัมน์ในฐานข้อมูล => ชื่อพารามิเตอร์ใน URL
                'balance_point' => 'balance_point',
                'shaft'         => 'shaft',
                'flexibility'   => 'flexibility',
                'weight'        => 'weight',
                'grip_size'     => 'grip_size',
            ],
        ],
        'STRING' => [
            'table'    => 'badminton_string',
            'relation' => 'badmintonString',
            'with'     => ['badmintonString'],
            'fields'   => [
                'thickness'             => 'thickness',
                'string_characteristic' => 'string_characteristic',
            ],
        ],
        'SHUTTLECOCK' => [
            'table'    => 'shuttlecock',
            'relation' => 'shuttlecock',
            'with'     => ['shuttlecock'],
            'fields'   => [
                'type'         => 'shuttle_type',   // ไม่ชนกับ ?type= ของหมวดสินค้า
                'speed_rating' => 'speed_rating',
            ],
        ],
        'GRIP' => [
            'table'    => 'grip',
            'relation' => 'grip',
            'with'     => ['grip'],
            'fields'   => [
                'type'     => 'grip_type',
                'material' => 'material',
            ],
        ],
    ];

    public function index(Request $request)
    {
        $selectedType = $request->query('type') ? strtoupper($request->query('type')) : null;
        
        if (!isset($this->typeConfigs[$selectedType])) { $selectedType = null; }

        // เรียกใช้ products Model
        $query = products::query();

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->filled('brand')) {
            $query->whereIn('brand', (array) $request->brand);
        }
        if ($request->filled('color')) {
            $query->whereHas('variants', fn ($q) => $q->whereIn('color', (array) $request->color));
        }

        // ----------------------------------------------------
        // B. กรองข้อมูลสเปคตารางย่อยตามประเภทสินค้า
        // ----------------------------------------------------
        if ($selectedType && isset($this->typeConfigs[$selectedType])) {
            $config = $this->typeConfigs[$selectedType];

            $query->where('product_type', $selectedType);
            $query->with($config['with']);

            $query->whereHas($config['relation'], function ($q) use ($request, $config) {
                foreach ($config['fields'] as $column => $param) {
                    if ($request->filled($param)) {
                        $q->whereIn($column, (array) $request->input($param));
                    }
                }
            });
        } else {
            $query->with(['badmintonRacket', 'badmintonString', 'shuttlecock', 'grip']);
        }

        $query->with(['variants' => function ($q) use ($request) {
            if ($request->filled('color')) {
                $q->whereIn('color', (array) $request->color);
            }
        }]);

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
                break;
        }

        // เรียงตาม primaryKey 'product_id'
        $products = $query->orderByDesc('created_at')->orderByDesc('product_id')->paginate(1000)->withQueryString();

        $totalInCategory = products::query()
            ->when($selectedType && isset($this->typeConfigs[$selectedType]),
                fn ($q) => $q->where('product_type', $selectedType))
            ->count();
        
        //Showing <b>{{ $products->total() }}</b> result from total <b>{{ $totalInCategory }}</b> เรียกใน blade

        $facets = ($selectedType && isset($this->typeConfigs[$selectedType])) ? $this->buildFacets($selectedType) : [];
        
        $commonFacets = $this->buildCommonFacets($selectedType);

        return view('products.index', compact('products', 'selectedType', 'totalInCategory', 'facets', 'commonFacets'));
    }

    private function buildFacets(string $type): array
    {
        $config = $this->typeConfigs[$type];
        $facets = [];

        foreach ($config['fields'] as $column => $param) {
            $facets[$param] = \DB::table($config['table'])
                ->whereNotNull($column)
                ->select($column, \DB::raw('COUNT(*) as n'))
                ->groupBy($column)
                ->orderBy($column)
                ->pluck('n', $column);   // ['Head-heavy' => 12, 'Even-balance' => 8, ...]
        }

        return $facets;
    }

    private function buildCommonFacets(?string $type): array
    {
        $brandQuery = products::query();
        $colorQuery = \DB::table('product_variant')
            ->join('product', 'product.product_id', '=', 'product_variant.product_id');

        // ถ้ามีหมวดที่เลือก ให้นับเฉพาะหมวดนั้น
        if ($type && isset($this->typeConfigs[$type])) {
            $brandQuery->where('product_type', $type);
            $colorQuery->where('product.product_type', $type);
        }

        return [
            'brands' => $brandQuery
                ->select('brand', \DB::raw('COUNT(*) as n'))
                ->groupBy('brand')->orderBy('brand')
                ->pluck('n', 'brand'),

            'colors' => $colorQuery
                ->select('color', \DB::raw('COUNT(DISTINCT product_variant.product_id) as n'))
                ->groupBy('color')->orderBy('color')
                ->pluck('n', 'color'),
        ];
    }
}
