<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions\Receipts;

/**
 * Class LedgerFlags
 * @package ForwardBlock\Protocol\Transactions\Receipts
 */
class LedgerFlags
{
    /** @var array */
    private array $flags = [];
    /** @var int */
    private int $count = 0;

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @param int $dec
     * @param bool $isCredit
     * @param bool $isFeeFlag
     * @return $this
     */
    public function append(int $dec, bool $isCredit, bool $isFeeFlag = false): self
    {
        if ($dec < 0 || $dec > 0xffff) {
            throw new \OutOfRangeException('Ledger flag cannot exceed 2 bytes');
        }

        if (isset($this->flags[$dec])) {
            throw new \DomainException('Cannot override existing ledger flag');
        }

        if ($isFeeFlag && $isCredit) {
            throw new \UnexpectedValueException('Fee flag cannot be of type credit');
        }

        $this->count++;
        $this->flags[$dec] = [
            "isCredit" => $isCredit,
            "isFee" => $isFeeFlag,
        ];

        return $this;
    }

    /**
     * @param int $dec
     * @return bool
     */
    public function has(int $dec): bool
    {
        return isset($this->flags[$dec]);
    }

    /**
     * @param int $dec
     * @return bool|null
     */
    public function isCredit(int $dec): ?bool
    {
        return $this->flags[$dec]["isCredit"] ?? null;
    }

    /**
     * @param int $dec
     * @return bool|null
     */
    public function isFeeFlag(int $dec): ?bool
    {
        return $this->flags[$dec]["isFee"] ?? null;
    }
}
