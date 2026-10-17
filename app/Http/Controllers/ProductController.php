<?php

namespace App\Http\Controllers;

use App\Models\products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /** สถานะคำสั่งซื้อที่นับเป็น "ขายแล้ว" สำหรับอันดับสินค้าขายดี (orders.status) */
    public const SOLD_STATUS = 'complete';

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

    public function index(Request $request, ?string $category = null)
    {
        $categoryTypes = [
            'badminton-racket' => 'RACKET',
            'badminton-string' => 'STRING',
            'grip' => 'GRIP',
            'shuttlecock' => 'SHUTTLECOCK',
        ];
        $category = isset($categoryTypes[$category]) ? $category : 'badminton-racket';
        $type = $categoryTypes[$category];
        $config = $this->typeConfigs[$type];

        $rows = products::query()
            ->where('product_type', $type)
            ->with(array_merge($config['with'], ['variants']))
            ->withAvg('reviews', 'star')
            ->withSum(['orderItems as sales_count' => function ($query) {
                $query->whereHas('order', fn ($order) => $order->where('status', self::SOLD_STATUS));
            }], 'quantity')
            ->orderByDesc('created_at')
            ->orderByDesc('product_id')
            ->get();

        $productItems = $rows->map(fn (products $product) => $this->toListItem($product, $type))->values();
        $brands = $productItems->pluck('brand')->filter()->unique()->sort()->values()->all();
        $prices = $productItems->pluck('price')->filter();
        $price_range = [
            'min' => $prices->isNotEmpty() ? (float) floor($prices->min()) : 0,
            'max' => $prices->isNotEmpty() ? (float) ceil($prices->max()) : 0,
        ];
        $price_gap = min(50.0, max(0.0, $price_range['max'] - $price_range['min']));
        $filter_groups = [$type => $this->buildFilterGroups($type, $rows)];

        // หน้า shop.index ใช้ข้อมูลเป็น array เพื่อให้ Alpine กรอง/เรียงผลลัพธ์ในหน้านี้
        return view('shop.index', [
            'products' => $productItems,
            'brands' => $brands,
            'filter_groups' => $filter_groups,
            'price_range' => $price_range,
            'price_gap' => $price_gap,
        ]);
    }

    public function show(string $category, int $product_id)
    {
        $product = products::query()
            ->where('product_id', $product_id)
            ->with(['variants', 'reviews.customer'])
            ->withAvg('reviews', 'star')
            ->first();

        if (! $product) {
            return view('shop.product', [
                'product' => null,
                'reviews' => collect(),
                'max_tension' => null,
            ]);
        }

        $type = $product->product_type;
        if (isset($this->typeConfigs[$type])) {
            $product->load($this->typeConfigs[$type]['with']);
        }

        $reviews = $product->reviews
            ->sortByDesc(fn ($review) => [(string) $review->date, $review->no ?? 0])
            ->values()
            ->map(fn ($review) => [
                'star' => (int) $review->star,
                'date' => $review->date instanceof \Carbon\Carbon
                    ? $review->date->format('Y-m-d')
                    : (string) $review->date,
                'comment' => $review->comment,
                'customer' => [
                    'name' => data_get($review, 'customer.name')
                        ?? data_get($review, 'customer.email')
                        ?? 'Customer',
                ],
            ]);

        $max_tension = $type === 'RACKET'
            ? (int) ($product->badmintonRacket->max_tension ?? 0)
            : null;
        $max_tension = $max_tension >= 18 ? $max_tension : null;

        return view('shop.product', [
            'product' => $this->toDetailItem($product),
            'reviews' => $reviews,
            'max_tension' => $max_tension,
        ]);
    }

    private function toListItem(products $product, string $type): array
    {
        $subtype = $this->subtypeOf($product, $type);
        $item = [
            'product_id' => $product->product_id,
            'name' => $product->name,
            'brand' => $product->brand,
            'price' => (float) $product->price,
            'image' => $this->imageUrl($product->image),
            'detail' => $product->detail,
            'product_type' => $product->product_type,
            'rating' => $product->reviews_avg_star !== null ? round((float) $product->reviews_avg_star, 1) : null,
            'sales_count' => (int) ($product->sales_count ?? 0),
            'product_variants' => $product->variants->map(fn ($variant) => [
                'product_id' => $variant->product_id,
                'color' => $variant->color,
                'stock' => $variant->stock,
            ])->values()->all(),
        ];

        foreach ($this->typeConfigs[$type]['fields'] as $column => $key) {
            $item[$key] = $subtype?->{$column};
        }

        return $item;
    }

    private function toDetailItem(products $product): array
    {
        $image = $this->imageUrl($product->image);

        return [
            'product_id' => $product->product_id,
            'name' => $product->name,
            'brand' => $product->brand,
            'price' => (float) $product->price,
            'image' => $image,
            'images' => $image ? [$image] : [],
            'detail' => $product->detail,
            'product_type' => $product->product_type,
            'rating' => $product->reviews_avg_star !== null ? round((float) $product->reviews_avg_star, 1) : null,
            'product_variants' => $product->variants->map(fn ($variant) => [
                'color' => $variant->color,
                'stock' => $variant->stock,
            ])->values()->all(),
        ];
    }

    private function subtypeOf(products $product, string $type)
    {
        return $product->{$this->typeConfigs[$type]['relation']};
    }

    private function buildFilterGroups(string $type, $rows): array
    {
        $groups = [];
        $colors = $rows->flatMap(fn (products $product) => $product->variants)
            ->flatMap(fn ($variant) => array_map('trim', explode('/', (string) $variant->color)))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($colors->isNotEmpty()) {
            $groups[] = [
                'key' => 'color',
                'label' => 'Color',
                'type' => 'color',
                'options' => $colors->mapWithKeys(fn ($color) => [$color => $color])->all(),
                'open' => true,
            ];
        }

        $labels = [
            'balance_point' => 'Balance point',
            'shaft' => 'Shaft',
            'flexibility' => 'Flexibility',
            'weight' => 'Weight',
            'grip_size' => 'Grip size',
            'thickness' => 'Thickness',
            'string_characteristic' => 'Characteristic',
            'type' => $type === 'GRIP' ? 'Type' : 'Type',
            'shuttle_type' => 'Type',
            'grip_type' => 'Type',
            'speed_rating' => 'Speed',
            'material' => 'Material',
        ];

        foreach ($this->typeConfigs[$type]['fields'] as $column => $key) {
            $subtypeRelation = $this->typeConfigs[$type]['relation'];
            $values = $rows->map(fn (products $product) => $product->{$subtypeRelation}?->{$column})
                ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                ->flatMap(fn ($value) => in_array($column, $this->typeConfigs[$type]['multi'] ?? [], true)
                    ? array_map('trim', explode(';', $value))
                    : [$value])
                ->filter()
                ->unique()
                ->sort()
                ->values();

            if ($values->isNotEmpty()) {
                $groups[] = [
                    'key' => $key,
                    'label' => $labels[$key] ?? $key,
                    'type' => 'checkbox',
                    'options' => $values->mapWithKeys(fn ($value) => [$value => $value])->all(),
                    'open' => true,
                ];
            }
        }

        return $groups;
    }

    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset($path);
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