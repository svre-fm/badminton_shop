<?php

namespace Tests\Feature;

use App\Models\products;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * ทดสอบ ProductController (index + show)
 *
 * - ใช้ DatabaseTransactions: ข้อมูลที่ insert ในแต่ละเทสต์จะถูก rollback ทิ้งเอง
 * - ชื่อตารางดึงจาก Model/relation จริง (products, badminton_rackets, ...) ไม่ hardcode
 * - ข้อมูลทดลองใช้ชื่อแบรนด์/สีที่ไม่ซ้ำกับข้อมูลจริง (TestBrandA, TestWhite) และทุกการยืนยัน
 *   กรองเฉพาะ id ที่เทสต์สร้างเอง จึงไม่พังเมื่อตารางมีข้อมูลจริงเพิ่มขึ้น
 * - ปิด foreign key ชั่วคราวระหว่างเทสต์ เพราะ reviews/order_items อ้างอิง orders ที่ไม่ได้สร้าง
 */
class ProductControllerTest extends TestCase
{
    use DatabaseTransactions;

    private int $racketA;   // TestBrandA | 100 | Head Heavy   | 3U | max_tension 30 | Black(5), Yellow(0)
    private int $racketB;   // TestBrandA | 300 | Even Balance | 4U | max_tension 28 | Black(3), Pink(2)
    private int $racketC;   // TestBrandB | 500 | Head Heavy   | 4U | max_tension 26 | Blue(4)
    private int $shuttle;
    private int $grip;
    private int $string;

    /** @var int[] */
    private array $created = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();                       // ไม่ต้องมี public/build/manifest.json
        Schema::disableForeignKeyConstraints();
        $this->seedProducts();
    }

    protected function tearDown(): void
    {
        Schema::enableForeignKeyConstraints();
        parent::tearDown();
    }

    // =====================================================================
    // index: กรองตามหมวด
    // =====================================================================

    public function test_index_with_type_lists_only_that_category(): void
    {
        $res = $this->index(['type' => 'racket'])->assertOk();

        $this->assertEqualsCanonicalizing(
            [$this->racketA, $this->racketB, $this->racketC],
            $this->mine($res)
        );
        $this->assertSame('RACKET', $res->viewData('selectedType'));
        $this->assertTrue($res->viewData('products')->every(fn ($p) => $p->product_type === 'RACKET'));
    }

    public function test_index_without_type_lists_every_category(): void
    {
        $res = $this->index([])->assertOk();

        $this->assertEqualsCanonicalizing($this->created, $this->mine($res));
        $this->assertNull($res->viewData('selectedType'));
    }

    public function test_index_with_unknown_type_does_not_crash_or_filter(): void
    {
        $res = $this->index(['type' => 'abc'])->assertOk();

        $this->assertEqualsCanonicalizing($this->created, $this->mine($res));
    }

    public function test_index_page_renders_cards_filters_and_empty_favorites_for_guest(): void
    {
        $res = $this->index(['type' => 'racket', 'balance_point' => ['Head Heavy']])->assertOk();

        $res->assertSee('Test Racket A')
            ->assertSee('Test Racket C')
            ->assertDontSee('Test Racket B')     // Even Balance ถูกกรองออก
            ->assertSee('Balance Point')          // ชื่อกลุ่มตัวกรอง
            ->assertSee('Applied Filters');       // ป้ายตัวกรองที่เลือกอยู่

        $res->assertViewHas('favoriteIds', []);
    }

    // =====================================================================
    // index: ตัวกรองสเปค
    // =====================================================================

    public function test_index_filters_by_balance_point(): void
    {
        $res = $this->index(['type' => 'racket', 'balance_point' => ['Head Heavy']]);

        $this->assertEqualsCanonicalizing([$this->racketA, $this->racketC], $this->mine($res));
    }

    public function test_index_filters_multiple_values_in_same_group_with_or(): void
    {
        $res = $this->index(['type' => 'racket', 'balance_point' => ['Head Heavy', 'Even Balance']]);

        $this->assertEqualsCanonicalizing(
            [$this->racketA, $this->racketB, $this->racketC],
            $this->mine($res)
        );
    }

    public function test_index_combines_different_spec_groups_with_and(): void
    {
        $res = $this->index(['type' => 'racket', 'balance_point' => ['Head Heavy'], 'weight' => ['4U']]);

        $this->assertSame([$this->racketC], $this->mine($res));
    }

    public function test_index_filters_by_shaft_flexibility_and_grip_size(): void
    {
        $res = $this->index(['type' => 'racket', 'shaft' => ['Graphite']]);
        $this->assertSame([$this->racketB], $this->mine($res));

        $res = $this->index(['type' => 'racket', 'flexibility' => ['Stiff', 'Soft']]);
        $this->assertEqualsCanonicalizing([$this->racketA, $this->racketC], $this->mine($res));

        $res = $this->index(['type' => 'racket', 'grip_size' => ['G5']]);
        $this->assertSame([$this->racketB], $this->mine($res));
    }

    public function test_shuttlecock_type_param_does_not_collide_with_category_param(): void
    {
        // ?type=shuttlecock อย่างเดียวต้องไม่ถูกตีความเป็นตัวกรอง type ในตารางย่อย
        $res = $this->index(['type' => 'shuttlecock'])->assertOk();
        $this->assertSame([$this->shuttle], $this->mine($res));

        $res = $this->index(['type' => 'shuttlecock', 'shuttle_type' => ['Feather']])->assertOk();
        $this->assertSame([$this->shuttle], $this->mine($res));

        $res = $this->index(['type' => 'shuttlecock', 'shuttle_type' => ['Plastic']])->assertOk();
        $this->assertSame([], $this->mine($res));
    }

    public function test_grip_filters_by_grip_type_and_material(): void
    {
        $res = $this->index(['type' => 'grip', 'grip_type' => ['Overgrip'], 'material' => ['Towel']])->assertOk();
        $this->assertSame([$this->grip], $this->mine($res));

        $res = $this->index(['type' => 'grip', 'material' => ['Leather']])->assertOk();
        $this->assertSame([], $this->mine($res));
    }

    public function test_string_filters_by_thickness(): void
    {
        $res = $this->index(['type' => 'string', 'thickness' => ['0.65']])->assertOk();

        $this->assertSame([$this->string], $this->mine($res));
    }

    // =====================================================================
    // index: ตัวกรองทั่วไป (ราคา / แบรนด์ / สี)
    // =====================================================================

    public function test_index_filters_by_price_range(): void
    {
        $res = $this->index(['type' => 'racket', 'min_price' => 200, 'max_price' => 400]);

        $this->assertSame([$this->racketB], $this->mine($res));
    }

    public function test_index_price_filter_works_with_only_min_or_only_max(): void
    {
        $res = $this->index(['type' => 'racket', 'min_price' => 200]);
        $this->assertEqualsCanonicalizing([$this->racketB, $this->racketC], $this->mine($res));

        $res = $this->index(['type' => 'racket', 'max_price' => 200]);
        $this->assertSame([$this->racketA], $this->mine($res));
    }

    public function test_index_filters_by_brand(): void
    {
        $res = $this->index(['type' => 'racket', 'brand' => ['TestBrandB']]);

        $this->assertSame([$this->racketC], $this->mine($res));
    }

    public function test_index_filters_by_color(): void
    {
        $res = $this->index(['type' => 'racket', 'color' => ['Black']]);
        $this->assertEqualsCanonicalizing([$this->racketA, $this->racketB], $this->mine($res));

        $res = $this->index(['type' => 'racket', 'color' => ['Pink', 'Blue']]);
        $this->assertEqualsCanonicalizing([$this->racketB, $this->racketC], $this->mine($res));
    }

    public function test_index_color_filter_also_limits_loaded_variants(): void
    {
        $res = $this->index(['type' => 'racket', 'color' => ['Black']]);

        $b = $res->viewData('products')->firstWhere('product_id', $this->racketB);
        $this->assertSame(['Black'], $b->variants->pluck('color')->all());
    }

    public function test_index_combines_price_color_and_spec_filters(): void
    {
        $res = $this->index([
            'type'          => 'racket',
            'balance_point' => ['Head Heavy'],
            'min_price'     => 50,
            'max_price'     => 150,
            'color'         => ['Black'],
        ]);

        $this->assertSame([$this->racketA], $this->mine($res));

        // เปลี่ยนสีเป็น Blue → A ไม่มีสีนี้ ต้องไม่เหลือผลลัพธ์
        $res = $this->index([
            'type' => 'racket', 'balance_point' => ['Head Heavy'], 'color' => ['Blue'], 'max_price' => 150,
        ]);
        $this->assertSame([], $this->mine($res));
    }

    // =====================================================================
    // index: เรียงลำดับ
    // =====================================================================

    public function test_index_sorts_by_price_low(): void
    {
        $res = $this->index(['type' => 'racket', 'sort' => 'price_low']);

        $this->assertSame([$this->racketA, $this->racketB, $this->racketC], $this->mine($res));
    }

    public function test_index_sorts_by_price_high(): void
    {
        $res = $this->index(['type' => 'racket', 'sort' => 'price_high']);

        $this->assertSame([$this->racketC, $this->racketB, $this->racketA], $this->mine($res));
    }

    public function test_index_sorts_by_latest_by_default(): void
    {
        // สร้างทีหลังสุด = ใหม่สุด → C, B, A
        $res = $this->index(['type' => 'racket']);
        $this->assertSame([$this->racketC, $this->racketB, $this->racketA], $this->mine($res));

        $res = $this->index(['type' => 'racket', 'sort' => 'latest']);
        $this->assertSame([$this->racketC, $this->racketB, $this->racketA], $this->mine($res));

        // ค่า sort ที่ไม่รู้จักต้องตกไปใช้ latest
        $res = $this->index(['type' => 'racket', 'sort' => 'nonsense']);
        $this->assertSame([$this->racketC, $this->racketB, $this->racketA], $this->mine($res));
    }

    public function test_index_sorts_by_top_sale(): void
    {
        $orderItems = $this->relatedTable('orderItems');
        $this->skipUnlessColumns($orderItems, ['order_no', 'id', 'product_id', 'color', 'quantity', 'unit_price', 'tension']);

        // order_no เป็นข้อความ (เช่น ORD20260108-0001) และ id ซ้ำกับข้อมูลจริงไม่ได้ จึงใช้เลขสูง ๆ
        $this->insertRow($orderItems, ['order_no' => 'ORD-TEST-0001', 'id' => 900001, 'product_id' => $this->racketA, 'color' => 'Black', 'quantity' => 2, 'unit_price' => 100, 'tension' => 25]);
        $this->insertRow($orderItems, ['order_no' => 'ORD-TEST-0001', 'id' => 900002, 'product_id' => $this->racketB, 'color' => 'Black', 'quantity' => 10, 'unit_price' => 300, 'tension' => 24]);
        // racketC ไม่เคยขาย → ต้องอยู่ท้ายสุด

        $res = $this->index(['type' => 'racket', 'sort' => 'top_sale'])->assertOk();

        $this->assertSame([$this->racketB, $this->racketA, $this->racketC], $this->mine($res));
    }

    // =====================================================================
    // index: จำนวน (facets / total)
    // =====================================================================

    public function test_index_total_matches_filtered_count_and_category_total(): void
    {
        $res = $this->index(['type' => 'racket', 'balance_point' => ['Head Heavy']]);

        $this->assertGreaterThanOrEqual(2, $res->viewData('products')->total());
        $this->assertGreaterThanOrEqual(3, $res->viewData('totalInCategory'));
        $this->assertGreaterThanOrEqual(
            $res->viewData('products')->total(),
            $res->viewData('totalInCategory')
        );
    }

    public function test_spec_facets_count_by_category_and_ignore_other_filters(): void
    {
        $res = $this->index(['type' => 'racket']);
        $facets = $res->viewData('facets');

        $this->assertGreaterThanOrEqual(2, $this->facetCount($facets['balance_point'], 'Head Heavy'));
        $this->assertGreaterThanOrEqual(1, $this->facetCount($facets['balance_point'], 'Even Balance'));
        $this->assertGreaterThanOrEqual(1, $this->facetCount($facets['weight'], '3U'));
        $this->assertGreaterThanOrEqual(2, $this->facetCount($facets['weight'], '4U'));
        $this->assertGreaterThanOrEqual(1, $this->facetCount($facets['shaft'], 'Graphite'));
        $this->assertGreaterThanOrEqual(1, $this->facetCount($facets['flexibility'], 'Soft'));
        $this->assertGreaterThanOrEqual(2, $this->facetCount($facets['grip_size'], 'G4'));

        // ใส่ตัวกรองสี/ราคา ตัวเลขในแถบกรองต้องไม่เปลี่ยน (นับทั้งหมวด)
        $filtered = $this->index(['type' => 'racket', 'color' => ['Pink'], 'max_price' => 150]);
        $this->assertEquals($facets['balance_point'], $filtered->viewData('facets')['balance_point']);
    }

    public function test_facets_use_shuttle_type_and_grip_type_param_names(): void
    {
        $facets = $this->index(['type' => 'shuttlecock'])->viewData('facets');
        $this->assertArrayHasKey('shuttle_type', $facets);
        $this->assertArrayHasKey('speed_rating', $facets);
        $this->assertGreaterThanOrEqual(1, $this->facetCount($facets['shuttle_type'], 'Feather'));

        $facets = $this->index(['type' => 'grip'])->viewData('facets');
        $this->assertArrayHasKey('grip_type', $facets);
        $this->assertGreaterThanOrEqual(1, $this->facetCount($facets['grip_type'], 'Overgrip'));
    }

    public function test_facets_are_empty_when_no_category_selected(): void
    {
        $this->assertSame([], $this->index([])->viewData('facets'));
    }

    public function test_common_facets_count_brands_and_colors_within_category(): void
    {
        $common = $this->index(['type' => 'racket'])->viewData('commonFacets');

        $this->assertGreaterThanOrEqual(2, $this->facetCount($common['brands'], 'TestBrandA'));
        $this->assertGreaterThanOrEqual(1, $this->facetCount($common['brands'], 'TestBrandB'));
        // Black อยู่ในไม้ 2 ชิ้น (A, B) → นับเป็นสินค้า ไม่ใช่นับแถว variant
        $this->assertGreaterThanOrEqual(2, $this->facetCount($common['colors'], 'Black'));
        $this->assertGreaterThanOrEqual(1, $this->facetCount($common['colors'], 'Pink'));
        // TestWhite เป็นของลูกขนไก่ ต้องไม่ถูกนับในหมวดไม้
        $this->assertSame(0, $this->facetCount($common['colors'], 'TestWhite'));
    }

    // =====================================================================
    // show
    // =====================================================================

    public function test_show_displays_product_with_variants(): void
    {
        $res = $this->get("/products/{$this->racketA}")
            ->assertOk()
            ->assertSee('Test Racket A')
            ->assertSee('Select color Yellow (out of stock)')
            ->assertSee('Max 30 lbs');   // ตัวเลือก Tension ของไม้แบดอิงจาก badminton_rackets.max_tension

        $product = $res->viewData('product');
        $this->assertSame($this->racketA, (int) $product->product_id);
        $this->assertEquals(['Black' => 5, 'Yellow' => 0], $product->variants->pluck('stock', 'color')->all());
        $this->assertSame('Head Heavy', $product->badmintonRacket->balance_point);
    }

    public function test_show_calculates_rating_summary_and_lists_reviews(): void
    {
        $reviews = $this->relatedTable('reviews');
        $this->skipUnlessColumns($reviews, ['customer_id', 'no', 'product_id', 'order_no', 'star', 'date', 'comment']);

        $user = User::factory()->create();
        $base = ['customer_id' => $user->id, 'product_id' => $this->racketA, 'order_no' => 'ORD-TEST-0001', 'date' => now()->toDateString()];

        // reviews.no ซ้ำกับข้อมูลจริงไม่ได้ จึงใช้เลขสูง ๆ
        $this->insertRow($reviews, $base + ['no' => 900001, 'star' => 5, 'comment' => 'Great racket']);
        $this->insertRow($reviews, $base + ['no' => 900002, 'star' => 3, 'comment' => 'Okay racket']);

        $res = $this->get("/products/{$this->racketA}")->assertOk();

        $res->assertViewHas('product', fn ($p) => round((float) $p->reviews_avg_star, 1) === 4.0);
        $res->assertViewHas('stars', fn ($s) => (int) $s[5] === 1
            && (int) $s[4] === 0
            && (int) $s[3] === 1
            && (int) $s[2] === 0
            && (int) $s[1] === 0);
        $res->assertViewHas('reviews', fn ($r) => $r->count() === 2);
        $res->assertSee('Great racket')->assertSee('Okay racket');
    }

    public function test_show_handles_product_without_reviews(): void
    {
        $res = $this->get("/products/{$this->racketB}")->assertOk()->assertSee('No reviews yet');

        $res->assertViewHas('product', fn ($p) => $p->reviews_avg_star === null);
        $res->assertViewHas('reviews', fn ($r) => $r->isEmpty());
        $res->assertViewHas('stars', fn ($s) => $s->sum() === 0 && $s->keys()->all() === [5, 4, 3, 2, 1]);
        $res->assertViewHas('isFavorite', false);
    }

    public function test_show_works_for_non_racket_products(): void
    {
        $this->get("/products/{$this->shuttle}")
            ->assertOk()
            ->assertSee('Test Shuttlecock')
            ->assertDontSee('String tension')   // ไม่ใช่ไม้แบด ต้องไม่มีตัวเลือก Tension
            ->assertViewHas('product', fn ($p) => $p->shuttlecock !== null && $p->shuttlecock->type === 'Feather');
    }

    public function test_show_returns_404_for_missing_product(): void
    {
        $this->get('/products/99999999')->assertNotFound();
    }

    // =====================================================================
    // helpers
    // =====================================================================

    /** เรียก /products พร้อม query string (รองรับ array เช่น color[]=Black) */
    private function index(array $query)
    {
        return $this->get('/products?' . http_build_query($query));
    }

    /** id ของสินค้าที่เทสต์สร้างเอง ซึ่งอยู่ในผลลัพธ์ (คงลำดับตามผลลัพธ์) */
    private function mine($response): array
    {
        $listed = $response->viewData('products')->pluck('product_id')->all();

        return array_values(array_filter($listed, fn ($id) => in_array((int) $id, $this->created, true)));
    }

    /** อ่านจำนวนของตัวเลือกใน facet โดยไม่สนตัวพิมพ์เล็ก/ใหญ่ (MySQL GROUP BY ไม่สนตัวพิมพ์) */
    private function facetCount(iterable $facet, string $key): int
    {
        foreach ($facet as $name => $count) {
            if (strcasecmp((string) $name, $key) === 0) {
                return (int) $count;
            }
        }

        return 0;
    }

    /** ชื่อตารางของ relation บน Model products (เช่น variants, reviews, orderItems) */
    private function relatedTable(string $relation): string
    {
        return (new products)->{$relation}()->getRelated()->getTable();
    }

    private function productTable(): string
    {
        return (new products)->getTable();
    }

    /** insert แถวเดียว และเติม created_at/updated_at ให้เองถ้าตารางมีคอลัมน์นี้ */
    private function insertRow(string $table, array $row): void
    {
        DB::table($table)->insert($this->withTimestamps($table, $row));
    }

    private function withTimestamps(string $table, array $row): array
    {
        foreach (['created_at', 'updated_at'] as $column) {
            if (! array_key_exists($column, $row) && Schema::hasColumn($table, $column)) {
                $row[$column] = now();
            }
        }

        return $row;
    }

    /** ใช้ตอน seed: ถ้าตารางจริงไม่มีคอลัมน์ที่ต้องใช้ ให้ fail พร้อมบอกว่าขาดอะไร */
    private function assertTableHasColumns(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            $this->fail("ไม่พบตาราง `{$table}` ในฐานข้อมูล `" . DB::connection()->getDatabaseName() . "` — ต้องสร้างตารางในฐานข้อมูลทดสอบก่อน");
        }

        $missing = array_diff($columns, Schema::getColumnListing($table));

        if ($missing !== []) {
            $this->fail("ตาราง `{$table}` ไม่มีคอลัมน์: " . implode(', ', $missing) . ' — แก้ seedProducts() ให้ตรงกับตารางจริง');
        }
    }

    /** ใช้กับเทสต์เสริม (รีวิว/ยอดขาย): ถ้าโครงสร้างตารางต่างไป ให้ข้ามพร้อมบอกเหตุผล */
    private function skipUnlessColumns(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            $this->markTestSkipped("ไม่พบตาราง `{$table}` ในฐานข้อมูล `" . DB::connection()->getDatabaseName() . '`');
        }

        $missing = array_diff($columns, Schema::getColumnListing($table));

        if ($missing !== []) {
            $this->markTestSkipped("ตาราง `{$table}` ไม่มีคอลัมน์: " . implode(', ', $missing));
        }
    }

    /**
     * สร้างสินค้า 1 ชิ้น + แถวในตารางย่อย + variants แล้วคืน product_id
     *
     * @param array<string,mixed>  $attrs     คอลัมน์ของตารางสินค้าหลัก
     * @param string               $relation  ชื่อ relation ของตารางย่อย เช่น badmintonRacket
     * @param array<string,mixed>  $spec      คอลัมน์สเปคของตารางย่อย
     * @param array<string,int>    $variants  สี => สต็อก
     */
    private function createProduct(array $attrs, string $relation, array $spec, array $variants): int
    {
        $table = $this->productTable();
        $subTable = $this->relatedTable($relation);
        $variantTable = $this->relatedTable('variants');

        $attrs += ['detail' => 'Test detail', 'image' => 'imgs/test/test.jpg'];

        $this->assertTableHasColumns($table, array_keys($attrs));
        $this->assertTableHasColumns($subTable, array_merge(['product_id'], array_keys($spec)));
        $this->assertTableHasColumns($variantTable, ['product_id', 'color', 'stock']);

        $id = DB::table($table)->insertGetId($this->withTimestamps($table, $attrs), 'product_id');

        $this->insertRow($subTable, ['product_id' => $id] + $spec);

        foreach ($variants as $color => $stock) {
            $this->insertRow($variantTable, ['product_id' => $id, 'color' => $color, 'stock' => $stock]);
        }

        $this->created[] = $id;

        return $id;
    }

    private function seedProducts(): void
    {
        // ไม้แบด: badminton_rackets (balance_point, shaft, flexibility, weight, grip_size, max_tension)
        $this->racketA = $this->createProduct(
            ['name' => 'Test Racket A', 'brand' => 'TestBrandA', 'price' => 100, 'product_type' => 'RACKET'],
            'badmintonRacket',
            ['balance_point' => 'Head Heavy', 'shaft' => 'Carbon Fiber', 'flexibility' => 'Stiff', 'weight' => '3U', 'grip_size' => 'G4', 'max_tension' => 30],
            ['Black' => 5, 'Yellow' => 0],
        );

        $this->racketB = $this->createProduct(
            ['name' => 'Test Racket B', 'brand' => 'TestBrandA', 'price' => 300, 'product_type' => 'RACKET'],
            'badmintonRacket',
            ['balance_point' => 'Even Balance', 'shaft' => 'Graphite', 'flexibility' => 'Medium', 'weight' => '4U', 'grip_size' => 'G5', 'max_tension' => 28],
            ['Black' => 3, 'Pink' => 2],
        );

        $this->racketC = $this->createProduct(
            ['name' => 'Test Racket C', 'brand' => 'TestBrandB', 'price' => 500, 'product_type' => 'RACKET'],
            'badmintonRacket',
            ['balance_point' => 'Head Heavy', 'shaft' => 'Full Carbon', 'flexibility' => 'Soft', 'weight' => '4U', 'grip_size' => 'G4', 'max_tension' => 26],
            ['Blue' => 4],
        );

        $this->shuttle = $this->createProduct(
            ['name' => 'Test Shuttlecock', 'brand' => 'TestBrandA', 'price' => 50, 'product_type' => 'SHUTTLECOCK'],
            'shuttlecock',
            ['type' => 'Feather', 'speed_rating' => '77'],
            ['TestWhite' => 10],
        );

        $this->grip = $this->createProduct(
            ['name' => 'Test Grip', 'brand' => 'TestBrandA', 'price' => 20, 'product_type' => 'GRIP'],
            'grip',
            ['type' => 'Overgrip', 'material' => 'Towel'],
            ['Black' => 10],
        );

        $this->string = $this->createProduct(
            ['name' => 'Test String', 'brand' => 'TestBrandA', 'price' => 40, 'product_type' => 'STRING'],
            'badmintonString',
            ['thickness' => '0.65', 'string_characteristic' => 'Control'],
            ['Blue' => 10],
        );
    }
}