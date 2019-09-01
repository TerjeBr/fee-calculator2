<?php

declare(strict_types=1);

namespace Lendable\Interview\Interpolation\Tests\Service\Fee\FeeTable;

use Lendable\Interview\Interpolation\Service\Fee\FeeTable\CsvFeeTable;
use Lendable\Interview\Interpolation\Service\Fee\FeeTable\FeeTableEntry;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CsvFeeTableTest extends WebTestCase
{
    private static $feeTable;

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        self::$feeTable = self::$container->get(CsvFeeTable::class);
    }

    /**
     * @dataProvider provideFeeTableData
     */
    public function testFeeTable(int $term, float $amount, array $expected)
    {
        $actual = self::$feeTable->findTwoNearest($term, $amount);

        $this->assertEquals(count($expected), count($actual), 'Wrong size of returned array');

        foreach ($actual as $entry) {
            if (!$entry instanceof FeeTableEntry) {
                $this->fail('Each entry must be an instance of ' . FeeTableEntry::class);
            }
            [$amount, $fee] = array_shift($expected);
            $this->assertEquals($amount, $entry->amount, 'Wrong amount');
            $this->assertEquals($fee, $entry->fee, 'Wrong fee');
        }
    }

    public function provideFeeTableData()
    {
        return [
            [12, 1000, [[1000, 50]]],
            [12, 1212.33, [[1000, 50], [2000, 90]]],
            [12, 3000, [[3000, 90]]],
            [12, 3023.11, [[3000, 90], [4000, 115]]],
            [12, 20000, [[20000, 400]]],
            [24, 1000, [[1000, 70]]],
            [24, 6434.21, [[6000, 240], [7000, 280]]],
            [24, 10000, [[10000, 400]]],
            [24, 15874.53, [[15000, 600], [16000, 640]]],
            [24, 20000, [[20000, 800]]],
        ];
    }
}
