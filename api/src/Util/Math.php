<?php

declare(strict_types=1);

namespace Lendable\Interview\Interpolation\Util;


class Math
{
    /**
     * Make an interpolation that is as accurate as possible near the endpoints.
     * Since in computers we always have rounding errors,
     * this method will make sure the error is always biggest in the middle of the
     * the two points, instead of having a "jump" near one of the endpoints.
     */
    static function interpolate(float $x, float $x1, float $x2, float $y1, float $y2): float
    {
        $distanceX1 = $x - $x1;
        $distanceX2 = $x - $x2;
        //$slope = ($y2 - $y1) / ($x2 - $x1);
        if (abs($distanceX1) < abs($distanceX2)) {
            // return $distanceX1 * $slope + $y1;
            // We get a more accurate result by computing a bigger number
            // before the division, instead of computing the slope first.
            return $distanceX1 * ($y2 - $y1) / ($x2 - $x1) + $y1;
        } else {
            // return $distanceX1 * $slope + $y2;
            return $distanceX2 * ($y2 - $y1) / ($x2 - $x1) + $y2;
        }
    }
}
