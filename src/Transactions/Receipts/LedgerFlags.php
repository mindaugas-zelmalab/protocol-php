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
     * @return $this
     */
    public function append(int $dec, bool $isCredit): self
    {
        $lF = new LedgerFlag($dec, $isCredit,);
        $this->flags[$dec] = $lF;
        $this->count++;
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
     * @return LedgerFlag
     */
    public function get(int $dec): LedgerFlag
    {
        if (!$this->has($dec)) {
            throw new \OutOfBoundsException('Invalid ledger flag');
        }

        return $this->flags[$dec];
    }
}
