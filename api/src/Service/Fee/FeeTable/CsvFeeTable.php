<?php

declare(strict_types=1);

namespace Lendable\Interview\Interpolation\Service\Fee\FeeTable;


class CsvFeeTable implements FeeTableInterface
{
    private $feeTableFile;
    private $feeTable;

    public function __construct(string $feeTableFile)
    {
        $this->feeTableFile = $feeTableFile;
    }

    public function findTwoNearest(int $term, float $amount): array
    {
        if (!$this->feeTable) {
            $this->readFeeTable();
        }

        $amountTable = $this->feeTable[$term] ?? false;
        if (!$amountTable) {
            throw new \RuntimeException("Invalid term $term");
        }

        $firstEntry = null;
        /** @var FeeTableEntry $entry */
        foreach ($amountTable as $entry)
        {
            if ($entry->amount == $amount) {
                return [$entry];
            }

            if ($entry->amount < $amount) {
                $firstEntry = $entry;
            } else {
                return [$firstEntry, $entry];
            }
        }

        throw new \RuntimeException('Amount is bigger than the last entry in the table');
    }

    private function readFeeTable()
    {
        $this->feeTable = [];
        foreach ($this->loadCsv() as $record) {
            [$term, $amount, $fee] = $record;
            $term = (int)$term;
            $amount = (float)$amount;
            $fee = (float)$fee;

            $entry = new FeeTableEntry();
            $entry->amount = $amount;
            $entry->fee = $fee;
            $this->feeTable[$term][$amount] = $entry;
        }

        // Make sure the tables are sorted for each term
        foreach ($this->feeTable as & $amountTable)
        {
            ksort($amountTable);
        }
    }

    private function loadCsv(): iterable
    {
        $handle = fopen($this->feeTableFile, 'r');
        if (!$handle) {
            throw new \RuntimeException("File {$this->feeTableFile} not found");
        }

        fgetcsv($handle); // Header line at top

        do {
            $record = fgetcsv($handle);
            if ($record) {
                yield $record;
            } else {
                break;
            }
        } while (true);

        fclose($handle);
    }
}
