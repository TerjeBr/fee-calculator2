<?php

declare(strict_types=1);

namespace Lendable\Interview\Interpolation\Service\Fee;

use Lendable\Interview\Interpolation\Model\LoanApplication;
use Lendable\Interview\Interpolation\Service\Fee\FeeTable\FeeTableEntry;
use Lendable\Interview\Interpolation\Service\Fee\FeeTable\FeeTableInterface;
use Lendable\Interview\Interpolation\Util\Math;

class FeeCalculator implements FeeCalculatorInterface
{
    private $feeTable;

    public function __construct(FeeTableInterface $feeTable)
    {
        $this->feeTable = $feeTable;
    }

    public function calculate(LoanApplication $application): float
    {
        $fee = $this->getInterpolatedFee($application);
        $amount = $application->getAmount();

        $feeAndLoan = $fee + $amount;
        $feeAndLoan = ceil($feeAndLoan/5.0)*5;
        $fee = $feeAndLoan - $amount;

        // As long as $amount has only two decimal places
        // this $fee will also have only two decimal places
        // (this is because $feeAndLoan will be an integer number)
        return $fee;
    }

    private function getInterpolatedFee(LoanApplication $application): float
    {
        $amount = $application->getAmount();
        $entries = $this->feeTable->findTwoNearest($application->getTerm(), $amount);

        /** @var FeeTableEntry $entry1 */
        $entry1 = array_shift($entries);
        /** @var FeeTableEntry $entry2 */
        $entry2 = array_shift($entries);

        if(!$entry2) {
            return $entry1->fee;
        }

        return Math::interpolate(
            $amount,
            $entry1->amount,
            $entry2->amount,
            $entry1->fee,
            $entry2->fee
        );
    }
}
