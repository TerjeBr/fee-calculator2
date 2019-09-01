<?php

declare(strict_types=1);

namespace Lendable\Interview\Interpolation\Service\Fee\FeeTable;

/**
 * We make this an interface in case the table implementation may be different.
 * F.ex. instead of being read from a csv file, it might be in a database.
 */
interface FeeTableInterface
{
    /**
     *
     * @param int $term
     * @param float $amount
     * @return FeeTableEntry[] The two nearest values in the fee table,
     *                         or only the match if the $amount is an exact match
     */
    public function findTwoNearest(int $term, float $amount): array;
}
