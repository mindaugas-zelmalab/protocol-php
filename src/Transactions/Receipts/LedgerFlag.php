<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions\Receipts;

use ForwardBlock\Protocol\Math\UInts;

/**
 * Class LedgerFlag
 * @package ForwardBlock\Protocol\Transactions\Receipts
 */
class LedgerFlag
{
    /** @var int */
    private int $dec;
    /** @var bool */
    private bool $isCredit;

    /**
     * LedgerFlag constructor.
     * @param int $dec
     * @param bool $isCredit
     */
    public function __construct(int $dec, bool $isCredit)
    {
        if ($dec < 0 || $dec > 0xffff) {
            throw new \OutOfRangeException('Ledger flag cannot exceed 2 bytes');
        }

        if (isset($this->flags[$dec])) {
            throw new \DomainException('Cannot override existing ledger flag');
        }

        $this->dec = $dec;
        $this->isCredit = $isCredit;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->dump();
    }

    /**
     * @return array
     */
    public function dump(): array
    {
        return [
            "dec" => $this->dec,
            "hex" => bin2hex(UInts::Encode_UInt2LE($this->dec)),
            "isCredit" => $this->isCredit,
        ];
    }

    /**
     * @return int
     */
    public function dec(): int
    {
        return $this->dec;
    }

    /**
     * @return bool
     */
    public function isCredit(): bool
    {
        return $this->isCredit;
    }

    /**
     * @return bool
     */
    public function isDebit(): bool
    {
        return !$this->isCredit;
    }
}
