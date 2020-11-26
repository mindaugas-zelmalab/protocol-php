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
    /** @var bool */
    private bool $isFee;

    /**
     * LedgerFlag constructor.
     * @param int $dec
     * @param bool $isCredit
     * @param bool $isFee
     */
    public function __construct(int $dec, bool $isCredit, bool $isFee)
    {
        if ($dec < 0 || $dec > 0xffff) {
            throw new \OutOfRangeException('Ledger flag cannot exceed 2 bytes');
        }

        if (isset($this->flags[$dec])) {
            throw new \DomainException('Cannot override existing ledger flag');
        }

        if ($isFee && $isCredit) {
            throw new \UnexpectedValueException('Fee flag cannot be of type credit');
        }

        $this->dec = $dec;
        $this->isCredit = $isCredit;
        $this->isFee = $isFee;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            "dec" => $this->dec,
            "hex" => "0x" . bin2hex(UInts::Encode_UInt2LE($this->dec)),
            "type" => $this->isCredit ? "+" : "-",
            "isFee" => $this->isFee,
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
    public function isFee(): bool
    {
        return $this->isFee;
    }
}
