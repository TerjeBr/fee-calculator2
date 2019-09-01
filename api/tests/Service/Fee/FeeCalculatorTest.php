<?php

declare(strict_types=1);

namespace Lendable\Interview\Interpolation\Tests\Service\Fee;

use Lendable\Interview\Interpolation\Model\LoanApplication;
use Lendable\Interview\Interpolation\Service\Fee\FeeCalculator;
use Lendable\Interview\Interpolation\Service\Fee\FeeTable\CsvFeeTable;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeeCalculatorTest extends WebTestCase
{
    /**
     * The subject under test
     * We do not want to create a new one for each test
     * @var FeeCalculator
     */
    private static $feeCalculator;

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        self::$feeCalculator = self::$container->get(FeeCalculator::class);
    }

    /**
     * @dataProvider provideFeeData
     */
    public function testCalculator(int $term, float $amount, float $expectedFee)
    {
        // We crate a mock of the loan application because it could be a complex object.
        // It could also be an option to treat an object of the LoanApplication class as a value object and not mock it;
        // $application = new LoanApplication($term, $amount);
        // (especially in this simple project), but it said in the comment that it was a cut down version of a loan application.

        // Also I wanted to show here that I know about mocking dependencies of the object under test.
        $application = $this->createMock(LoanApplication::class);
        $application->method('getTerm')->willReturn($term);
        $application->method('getAmount')->willReturn($amount);

        $fee = self::$feeCalculator->calculate($application);

        $this->assertEquals($expectedFee, $fee);
    }

    public function provideFeeData()
    {
        return [
            // term, amount, fee
            [12, 1000, 50],
            [12, 1500, 70],
            [12, 2420, 90],
            [12, 2421, 94],
            [24, 1000, 70],
            [24, 2750, 115],
            [24, 7100, 285],
            [12, 10003.22, 201.78],
        ];
    }
}
