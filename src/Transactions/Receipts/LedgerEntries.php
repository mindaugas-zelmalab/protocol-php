<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions\Receipts;

use ForwardBlock\Protocol\Math\UInts;

/**
 * Class LedgerEntries
 * @package ForwardBlock\Protocol\Transactions\Receipts
 */
class LedgerEntries
{
    /** @var array */
    private array $batches = [];
    /** @var int */
    private int $batchCount = 0;
    /** @var int */
    private int $leCount = 0;

    /**
     * @param LedgerEntry ...$entries
     */
    public function addBatch(LedgerEntry ...$entries)
    {
        $this->batches[] = $entries;
        $this->batchCount++;
        $this->leCount = $this->leCount + count($entries);
    }

    /**
     * @return array
     */
    public function batches(): array
    {
        return $this->batches;
    }

    /**
     * @return int
     */
    public function batchCount(): int
    {
        return $this->batchCount;
    }

    /**
     * @return int
     */
    public function entriesCount(): int
    {
        return $this->leCount;
    }

    /**
     * @return string
     */
    public function serializedBatches(): string
    {
        $ser = UInts::Encode_UInt1LE($this->batchCount);
        foreach ($this->batches as $batch) {
            $batchCount = count($batch);
            $ser .= UInts::Encode_UInt1LE($batchCount);
            /** @var LedgerEntry $entry */
            foreach ($batch as $entry) {
                $ser .= $entry->serializeRawBytes();
            }
        }

        return $ser;
    }
}
