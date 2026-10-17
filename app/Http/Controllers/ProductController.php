<?php

namespace App\Http\Controllers;

use App\Models\products;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    private array $slugToType = [
        'badminton-racket' => 'RACKET',
        'badminton-string' => 'STRING',
        'grip'             => 'GRIP',
        'shuttlecock'      => 'SHUTTLECOCK',
    ];

    /**
     * สเปคที่หน้า filter (Alpine) อ่านจาก product[key]
     * ต้องตรงชื่อคอลัมน์ในตารางย่อยของเพื่อน
     */
    private array $typeConfigs = [
        'RACKET' => [
            'relation' => 'badmintonRacket',
            'fields'   => [
                'balance_point' => ['key' => 'balance_point', 'label' => 'Balance point'],
                'shaft'         => ['key' => 'shaft', 'label' => 'Shaft'],
                'flexibility'   => ['key' => 'flexibility', 'label' => 'Flexibility'],
                'weight'        => ['key' => 'weight', 'label' => 'Weight'],
                'grip_size'     => ['key' => 'grip_size', 'label' => 'Grip size'],
            ],
        ],
        'STRING' => [
            'relation' => 'badmintonString',
            'fields'   => [
                'thickness'             => ['key' => 'thickness', 'label' => 'Thickness'],
                'string_characteristic' => ['key' => 'string_characteristic', 'label' => 'Characteristic', 'multi' => true],
            ],
        ],
        'SHUTTLECOCK' => [
            'relation' => 'shuttlecock',
            'fields'   => [
                'type'         => ['key' => 'type', 'label' => 'Type'],
                'speed_rating' => ['key' => 'speed_rating', 'label' => 'Speed'],
            ],
        ],
        'GRIP' => [
            'relation' => 'grip',
            'fields'   => [
                'type'     => ['key' => 'type', 'label' => 'Type'],
                'material' => ['key' => 'material', 'label' => 'Material'],
            ],
        ],
    ];

    public function index(Request $request, ?string $category = null): View
    {
        if (! $category || ! isset($this->slugToType[$category])) {
            $category = 'badminton-racket';
        }

        $type = $this->slugToType[$category];
        $config = $this->typeConfigs[$type];
        $relation = $config['relation'];

        $rows = products::query()
            ->where('product_type', $type)
            ->with([$relation, 'variants'])
            ->withAvg('reviews', 'star')
            ->withSum('orderItems', 'quantity')
            ->orderByDesc('created_at')
            ->orderByDesc('product_id')
            ->get();

        $products = $rows->map(fn ($p) => $this->toListItem($p, $type))->values();

        $brands = $products->pluck('brand')->filter()->unique()->sort()->values()->all();

        $prices = $products->pluck('price')->filter();
        $price_range = [
            'min' => $prices->isNotEmpty() ? (float) floor($prices->min()) : 0,
            'max' => $prices->isNotEmpty() ? (float) ceil($prices->max()) : 0,
        ];
        $price_gap = 50.0;

        $filter_groups = [
            $type => $this->buildFilterGroups($type, $rows),
        ];

        // ชื่อตัวแปรต้องตรงกับ shop/index.blade.php ทุกตัว
        return view('shop.index', compact(
            'products',
            'brands',
            'filter_groups',
            'price_range',
            'price_gap'
        ));
    }

    public function show(string $category, int $product_id): View
    {
        $product = products::query()
            ->where('product_id', $product_id)
            ->with(['variants', 'reviews.customer'])
            ->withAvg('reviews', 'star')
            ->first();

        if (! $product) {
            return view('shop.product', [
                'product'     => null,
                'reviews'     => collect(),
                'max_tension' => null,
            ]);
        }

        $type = $product->product_type;
        if (isset($this->typeConfigs[$type])) {
            $product->load($this->typeConfigs[$type]['relation']);
        }

        $reviews = $product->reviews
            ->sortByDesc(fn ($r) => [(string) $r->date, $r->no ?? 0])
            ->values()
            ->map(fn ($r) => [
                'star'     => (int) $r->star,
                'date'     => $r->date instanceof \Carbon\Carbon
                    ? $r->date->format('Y-m-d')
                    : (string) $r->date,
                'comment'  => $r->comment,
                'customer' => [
                    'name' => data_get($r, 'customer.name')
                        ?? data_get($r, 'customer.email')
                        ?? 'Customer',
                ],
            ]);

        $max_tension = null;
        if ($type === 'RACKET' && $product->badmintonRacket) {
            $max_tension = (int) ($product->badmintonRacket->max_tension ?? 0);
            if ($max_tension < 18) {
                $max_tension = null;
            }
        }

        return view('shop.product', [
            'product'     => $this->toDetailItem($product),
            'reviews'     => $reviews,
            'max_tension' => $max_tension,
        ]);
    }

    private function toListItem(products $p, string $type): array
    {
        $subtype = $this->subtypeOf($p, $type);

        $item = [
            'product_id'       => $p->product_id,
            'name'             => $p->name,
            'brand'            => $p->brand,
            'price'            => (float) $p->price,
            'image'            => $this->imageUrl($p->image),
            'detail'           => $p->detail,
            'product_type'     => $p->product_type,
            'rating'           => $p->reviews_avg_star !== null
                ? round((float) $p->reviews_avg_star, 1)
                : null,
            'sales_count'      => (int) ($p->order_items_sum_quantity ?? 0),
            'product_variants' => $p->variants->map(fn ($v) => [
                'color' => $v->color,
                'stock' => $v->stock,
            ])->values()->all(),
            // Alpine filter สีใช้ product.color เป็น array
            'color'            => $p->variants
                ->pluck('color')
                ->flatMap(fn ($c) => array_map('trim', explode('/', (string) $c)))
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ];

        if ($subtype && isset($this->typeConfigs[$type])) {
            foreach ($this->typeConfigs[$type]['fields'] as $column => $meta) {
                $item[$meta['key']] = $subtype->{$column} ?? null;
            }
        }

        return $item;
    }

    private function toDetailItem(products $p): array
    {
        $image = $this->imageUrl($p->image);

        return [
            'product_id'       => $p->product_id,
            'name'             => $p->name,
            'brand'            => $p->brand,
            'price'            => (float) $p->price,
            'image'            => $image,
            'images'           => $image ? [$image] : [],
            'detail'           => $p->detail,
            'product_type'     => $p->product_type,
            'rating'           => $p->reviews_avg_star !== null
                ? round((float) $p->reviews_avg_star, 1)
                : null,
            'product_variants' => $p->variants->map(fn ($v) => [
                'color' => $v->color,
                'stock' => $v->stock,
            ])->values()->all(),
        ];
    }

    private function subtypeOf(products $p, string $type)
    {
        return match ($type) {
            'RACKET'      => $p->badmintonRacket,
            'STRING'      => $p->badmintonString,
            'GRIP'        => $p->grip,
            'SHUTTLECOCK' => $p->shuttlecock,
            default       => null,
        };
    }

    private function buildFilterGroups(string $type, $rows): array
    {
        $groups = [];

        // สี
        $colorOptions = [];
        foreach ($rows as $p) {
            foreach ($p->variants as $v) {
                foreach (explode('/', (string) $v->color) as $token) {
                    $token = trim($token);
                    if ($token !== '') {
                        $colorOptions[$token] = $token;
                    }
                }
            }
        }
        if ($colorOptions) {
            ksort($colorOptions);
            $groups[] = [
                'key'     => 'color',
                'label'   => 'Color',
                'type'    => 'color',
                'options' => $colorOptions,
                'open'    => true,
            ];
        }

        foreach ($this->typeConfigs[$type]['fields'] as $column => $meta) {
            $options = [];
            foreach ($rows as $p) {
                $subtype = $this->subtypeOf($p, $type);
                if (! $subtype || $subtype->{$column} === null || $subtype->{$column} === '') {
                    continue;
                }
                $raw = (string) $subtype->{$column};
                if (! empty($meta['multi'])) {
                    foreach (preg_split('/\s*;\s*/', $raw) as $token) {
                        $token = trim($token);
                        if ($token !== '') {
                            $options[$token] = $token;
                        }
                    }
                } else {
                    $options[$raw] = $raw;
                }
            }
            if ($options) {
                ksort($options);
                $groups[] = [
                    'key'     => $meta['key'],
                    'label'   => $meta['label'],
                    'type'    => 'checkbox',
                    'options' => $options,
                    'open'    => true,
                ];
            }
        }

        return $groups;
    }

    /**
     * seeder ของกอฟเก็บ path แบบ imgs/...
     * ทำให้เปิดจาก public ได้
     */
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
}