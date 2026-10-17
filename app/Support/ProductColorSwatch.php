<?php

namespace App\Support;

class ProductColorSwatch
{
    private const PALETTE = [
        'aqua' => '#00ffff',
        'black' => '#000000',
        'blue' => '#0000ff',
        'cherry' => '#8b1e3f',
        'green' => '#008000',
        'light blue' => '#add8e6',
        'orange' => '#ffa500',
        'pink' => '#ffc0cb',
        'red' => '#ff0000',
        'white' => '#ffffff',
        'yellow' => '#ffff00',
    ];

    public static function palette(): array
    {
        return self::PALETTE;
    }

    public static function cssBackground(?string $color): string
    {
        $colors = collect(explode('/', (string) $color))
            ->map(fn (string $name) => self::PALETTE[mb_strtolower(trim($name))] ?? '#808080')
            ->values();

        if ($colors->count() === 1) {
            return $colors->first();
        }

        $segment = 100 / $colors->count();
        $stops = $colors->map(fn (string $hex, int $index) => sprintf(
            '%s %s%% %s%%',
            $hex,
            $index * $segment,
            ($index + 1) * $segment,
        ));

        return 'linear-gradient(90deg, '.$stops->implode(', ').')';
    }
}
