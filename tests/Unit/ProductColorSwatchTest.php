<?php

namespace Tests\Unit;

use App\Support\ProductColorSwatch;
use PHPUnit\Framework\TestCase;

class ProductColorSwatchTest extends TestCase
{
    public function test_single_color_uses_a_solid_swatch(): void
    {
        $this->assertSame('#000000', ProductColorSwatch::cssBackground('Black'));
    }

    public function test_slash_separated_colors_share_one_swatch_in_equal_segments(): void
    {
        $this->assertSame(
            'linear-gradient(90deg, #000000 0% 50%, #8b1e3f 50% 100%)',
            ProductColorSwatch::cssBackground('Black/Cherry'),
        );
    }

    public function test_unknown_color_names_use_a_neutral_swatch(): void
    {
        $this->assertSame('#808080', ProductColorSwatch::cssBackground('Unlisted'));
    }
}
