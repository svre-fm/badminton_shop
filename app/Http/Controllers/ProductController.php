<?php

namespace App\Http\Controllers;

use App\Models\products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /** สถานะคำสั่งซื้อที่นับเป็น "ขายแล้ว" สำหรับอันดับสินค้าขายดี (orders.status) */
    public const SOLD_STATUS = 'Complete';

    public function home()
    {
        $completedOrderItems = fn ($query) => $query->whereHas(
            'order',
            fn ($order) => $order->where('status', self::SOLD_STATUS)
        );

        $bestSellers = products::query()
            ->whereHas('orderItems', $completedOrderItems)
            ->with(['variants'])
            ->withAvg('reviews', 'star')
            ->withSum(['orderItems as sold_quantity' => $completedOrderItems], 'quantity')
            ->orderByDesc('sold_quantity')
            ->orderByDesc('product_id')
            ->limit(15)
            ->get();

        $favoriteIds = auth()->check()
            ? DB::table('favorites')->where('customer_id', auth()->id())->pluck('product_id')->all()
            : [];

        return view('home', compact('bestSellers', 'favoriteIds'));
    }

    /**
     * ตั้งค่าตัวกรองสเปคของแต่ละหมวด
     *
     * fields : คอลัมน์ในตารางย่อย => ชื่อพารามิเตอร์ใน URL (แยกกันเพราะ "type" ชนกับหมวดสินค้า)
     * multi  : คอลัมน์ที่เก็บหลายค่าในช่องเดียว คั่นด้วย "; " เช่น
     *          "Repulsion power; medium feeling; multifilament" (ต้องแยกค่าก่อนนับ/กรอง)
     *
     * ชื่อตารางจริงไม่ได้ hardcode: ดึงจาก relation ใน Model products
     * (badminton_rackets, badminton_strings, shuttlecocks, grips)
     */
    protected $typeConfigs = [
        'RACKET' => [
            'relation' => 'badmintonRacket',
            'with'     => ['badmintonRacket'],
            'fields'   => [
                'balance_point' => 'balance_point',
                'shaft'         => 'shaft',
                'flexibility'   => 'flexibility',
                'weight'        => 'weight',
                'grip_size'     => 'grip_size',
            ],
        ],
        'STRING' => [
            'relation' => 'badmintonString',
            'with'     => ['badmintonString'],
            'fields'   => [
                'thickness'             => 'thickness',
                'string_characteristic' => 'string_characteristic',
            ],
            'multi'    => ['string_characteristic'],
        ],
        'SHUTTLECOCK' => [
            'relation' => 'shuttlecock',
            'with'     => ['shuttlecock'],
            'fields'   => [
                'type'         => 'shuttle_type',   // ไม่ชนกับ ?type= ของหมวดสินค้า
                'speed_rating' => 'speed_rating',
            ],
        ],
        'GRIP' => [
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
        $type = $request->query('type');
        $selectedType = is_string($type) && $type !== '' ? strtoupper($type) : null;
        if ($selectedType && ! isset($this->typeConfigs[$selectedType])) {
            $selectedType = null;   // หมวดที่ไม่รู้จัก = แสดงทุกหมวด
        }

        $query = products::query();

        // ----------------------------------------------------
        // A. ตัวกรองทั่วไป (ใช้ได้กับทุกหมวด)
        // ----------------------------------------------------
        if (is_numeric($request->query('min_price'))) {
            $query->where('price', '>=', $request->query('min_price'));
        }
        if (is_numeric($request->query('max_price'))) {
            $query->where('price', '<=', $request->query('max_price'));
        }

        $brands = $this->values($request, 'brand');
        if ($brands) {
            $query->whereIn('brand', $brands);
        }

        $colors = $this->values($request, 'color');
        if ($colors) {
            $query->whereHas('variants', fn ($q) => $this->whereColorIn($q, $colors));
        }

        // ----------------------------------------------------
        // B. ตัวกรองสเปคของตารางย่อยตามหมวด
        // ----------------------------------------------------
        if ($selectedType) {
            $config = $this->typeConfigs[$selectedType];

            $query->where('product_type', $selectedType);
            $query->with($config['with']);

            $query->whereHas($config['relation'], function ($q) use ($request, $config) {
                $multi = $config['multi'] ?? [];

                foreach ($config['fields'] as $column => $param) {
                    $values = $this->values($request, $param);

                    if ($values === []) {
                        continue;
                    }

                    if (in_array($column, $multi, true)) {
                        // คอลัมน์หลายค่า: เลือกหลายตัวเลือก = มีลักษณะใดลักษณะหนึ่ง (OR)
                        $q->where(function ($w) use ($column, $values) {
                            foreach ($values as $value) {
                                $w->orWhereRaw(
                                    "CONCAT('; ', `{$column}`, ';') LIKE ?",
                                    ['%; ' . $this->escapeLike($value) . ';%']
                                );
                            }
                        });
                    } else {
                        $q->whereIn($column, $values);
                    }
                }
            });
        } else {
            $query->with(array_merge(...array_column($this->typeConfigs, 'with')));
        }

        // variants โหลดครั้งเดียวทุกกรณี และแสดงเฉพาะสีที่กรองเมื่อมีการเลือกสี
        $query->with(['variants' => function ($q) use ($colors) {
            if ($colors) {
                $this->whereColorIn($q, $colors);
            }
        }]);

        // ดาวเฉลี่ยของแต่ละสินค้า → $product->reviews_avg_star
        $query->withAvg('reviews', 'star');

        // ----------------------------------------------------
        // C. เรียงลำดับ
        // ----------------------------------------------------
        switch ($request->query('sort', 'latest')) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;

            case 'price_high':
                $query->orderBy('price', 'desc');
                break;

            case 'top_sale':
                // ยอดขายรวมจำนวนชิ้น นับเฉพาะคำสั่งซื้อที่เสร็จสมบูรณ์ (orders.status = 'Complete')
                // สินค้าที่ยังไม่มียอดขายจะได้ NULL และอยู่ท้ายสุดเมื่อเรียงมากไปน้อย
                $query->withSum(['orderItems as sold_quantity' => function ($q) {
                    $q->whereHas('order', fn ($o) => $o->where('status', self::SOLD_STATUS));
                }], 'quantity')->orderByDesc('sold_quantity');
                break;

            case 'latest':
            default:
                break;
        }

        // ลำดับรองเสมอ เพื่อให้แบ่งหน้าเสถียร
        $products = $query->orderByDesc('created_at')
                          ->orderByDesc('product_id')
                          ->paginate(1000)
                          ->withQueryString();

        // Showing {{ $products->total() }} result from total {{ $totalInCategory }}
        $totalInCategory = products::query()
            ->when($selectedType, fn ($q) => $q->where('product_type', $selectedType))
            ->count();

        $facets       = $selectedType ? $this->buildFacets($selectedType) : [];
        $commonFacets = $this->buildCommonFacets($selectedType);

        // สินค้าที่ผู้ใช้ที่ล็อกอินกดหัวใจไว้ (ผู้เยี่ยมชม = ว่าง)
        $favoriteIds = auth()->check()
            ? DB::table('favorites')->where('customer_id', auth()->id())->pluck('product_id')->all()
            : [];

        return view('products.index', compact('products', 'selectedType', 'totalInCategory', 'facets', 'commonFacets', 'favoriteIds'));
    }

    public function show(products $product)
    {
        $product->load('variants');
        if (isset($this->typeConfigs[$product->product_type])) {
            $product->load($this->typeConfigs[$product->product_type]['with']);
        }
        $product->loadAvg('reviews', 'star')->loadCount('reviews');

        $starCounts = $product->reviews()
            ->select('star', DB::raw('COUNT(*) as n'))
            ->groupBy('star')
            ->pluck('n', 'star');
        $stars = collect([5, 4, 3, 2, 1])->mapWithKeys(fn ($s) => [$s => (int) ($starCounts[$s] ?? 0)]);

        // รีวิวล่าสุดก่อน (reviews.date, reviews.no)
        $reviews = $product->reviews()->with('customer')->orderByDesc('date')->orderByDesc('no')->get();

        $isFavorite = false;
        $reviewableOrders = collect();
        if (auth()->check()) {
            $isFavorite = DB::table('favorites')
                ->where('customer_id', auth()->id())
                ->where('product_id', $product->product_id)
                ->exists();

            $reviewableOrders = auth()->user()->orders()
                ->where('status', 'Complete')
                ->whereHas('items', fn ($query) => $query->where('product_id', $product->product_id))
                ->whereDoesntHave('reviews', fn ($query) => $query
                    ->where('product_id', $product->product_id)
                    ->where('customer_id', auth()->id()))
                ->orderByDesc('order_date')
                ->get(['order_no', 'order_date']);
        }

        return view('products.show', compact('product', 'stars', 'reviews', 'isFavorite', 'reviewableOrders'));
    }

    // =====================================================================
    // ตัวช่วยกรอง
    // =====================================================================

    /**
     * อ่านค่าจาก query string เป็นอาเรย์ของข้อความ โดยตัดค่าว่างและค่าที่ไม่ใช่ข้อความ/ตัวเลข
     * (เช่น color[][]=x) ออก เพื่อไม่ให้ทำให้ query พังเป็น 500
     */
    private function values(Request $request, string $key): array
    {
        return collect((array) $request->input($key))
            ->filter(fn ($v) => is_scalar($v) && trim((string) $v) !== '')
            ->map(fn ($v) => (string) $v)
            ->values()
            ->all();
    }

    /**
     * สีใน productvariants อาจเป็นสีเดียว ("Black") หรือหลายสีคั่นด้วย "/" ("Black/Orange")
     * กรอง "Black" จะได้ทั้ง Black, Black/Orange, Red/Black (เทียบทีละสี ไม่ใช่เทียบข้อความบางส่วน
     * จึงไม่ทำให้ "Blue" ไปตรงกับ "Light Blue")
     */
    private function whereColorIn($query, array $colors)
    {
        return $query->where(function ($w) use ($colors) {
            foreach ($colors as $color) {
                $w->orWhereRaw(
                    "CONCAT('/', `color`, '/') LIKE ?",
                    ['%/' . $this->escapeLike($color) . '/%']
                );
            }
        });
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }

    // =====================================================================
    // จำนวนข้างแต่ละตัวเลือก (นับทั้งหมวด ไม่สนตัวกรองอื่น)
    // =====================================================================

    private function buildFacets(string $type): array
    {
        $config = $this->typeConfigs[$type];
        $table  = (new products)->{$config['relation']}()->getRelated()->getTable();
        $multi  = $config['multi'] ?? [];
        $facets = [];

        foreach ($config['fields'] as $column => $param) {
            if (in_array($column, $multi, true)) {
                $facets[$param] = $this->countTokens(
                    DB::table($table)->whereNotNull($column)->pluck($column),
                    ';'
                );
                continue;
            }

            $facets[$param] = DB::table($table)
                ->whereNotNull($column)
                ->select($column, DB::raw('COUNT(*) as n'))
                ->groupBy($column)
                ->orderBy($column)
                ->pluck('n', $column);
        }

        return $facets;
    }

    private function buildCommonFacets(?string $type): array
    {
        $productTable = (new products)->getTable();
        $variantTable = (new products)->variants()->getRelated()->getTable();

        $brands = products::query()
            ->when($type, fn ($q) => $q->where('product_type', $type))
            ->select('brand', DB::raw('COUNT(*) as n'))
            ->groupBy('brand')
            ->orderBy('brand')
            ->pluck('n', 'brand');

        // นับสีทีละสี (แยก "Black/Orange" เป็น Black และ Orange) โดยนับเป็นจำนวนสินค้า ไม่ใช่จำนวนแถว
        $rows = DB::table($variantTable)
            ->join($productTable, "{$productTable}.product_id", '=', "{$variantTable}.product_id")
            ->when($type, fn ($q) => $q->where("{$productTable}.product_type", $type))
            ->select("{$variantTable}.product_id", "{$variantTable}.color")
            ->get();

        $seen = [];
        foreach ($rows as $row) {
            foreach (explode('/', (string) $row->color) as $token) {
                $token = trim($token);
                if ($token === '') {
                    continue;
                }
                $key = mb_strtolower($token);
                $seen[$key]['label'] ??= $token;
                $seen[$key]['products'][$row->product_id] = true;
            }
        }

        $colors = collect($seen)
            ->mapWithKeys(fn ($c) => [$c['label'] => count($c['products'])])
            ->sortKeys();

        return ['brands' => $brands, 'colors' => $colors];
    }

    /**
     * แยกข้อความที่คั่นด้วย ";" เป็นลักษณะย่อยแล้วนับ โดยไม่สนตัวพิมพ์เล็ก/ใหญ่
     * ("Repulsion power" กับ "repulsion power" นับเป็นอันเดียวกัน)
     */
    private function countTokens($rows, string $separator)
    {
        $seen = [];

        foreach ($rows as $row) {
            $tokens = collect(explode($separator, (string) $row))
                ->map(fn ($t) => trim($t))
                ->filter()
                ->unique(fn ($t) => mb_strtolower($t));

            foreach ($tokens as $token) {
                $key = mb_strtolower($token);
                $seen[$key]['label'] ??= mb_strtoupper(mb_substr($token, 0, 1)) . mb_substr($token, 1);
                $seen[$key]['n'] = ($seen[$key]['n'] ?? 0) + 1;
            }
        }

        return collect($seen)->mapWithKeys(fn ($c) => [$c['label'] => $c['n']])->sortKeys();
    }
}
