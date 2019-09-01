<?php

declare(strict_types=1);

namespace Lendable\Interview\Interpolation\Tests\Util;

use Lendable\Interview\Interpolation\Util\Math;
use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
{
    /**
     * @dataProvider provideInterpolationData
     */
    public function testInterpolate(float $x, float $x1, float $x2, float $y1, float $y2, float $expected)
    {
        $y = Math::interpolate($x, $x1, $x2, $y1, $y2);

        $this->assertEquals($expected, $y);
    }

    public function provideInterpolationData()
    {
        return [
            // x, x1, x2, y1, y2, y
            [ 15, 10, 20, 100, 200, 150],
            [ 12, 10, 20, 100, 200, 120],
            [ 2750, 2000, 3000, 100, 120, 115],
            [ 7100, 7000, 8000, 280, 320, 284],
        ];
    }
}