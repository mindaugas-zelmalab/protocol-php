<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions\Receipts;

use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Math\UInts;
use ForwardBlock\Protocol\Transactions\AbstractTxReceipt;

/**
 * Class LedgerEntry
 * @package ForwardBlock\Protocol\Transactions\Receipts
 */
class LedgerEntry
{
    /** @var AbstractProtocolChain */
    protected AbstractProtocolChain $p;
    /** @var AbstractTxReceipt */
    protected AbstractTxReceipt $txR;

    /** @var string */
    protected string $hash160;
    /** @var LedgerFlag */
    protected LedgerFlag $flag;
    /** @var int */
    protected int $amount;
    /** @var string|null */
    protected ?string $asset = null;
    /** @var bool */
    protected bool $applicable = false;

    /**
     * LedgerEntry constructor.
     * @param AbstractProtocolChain $p
     * @param AbstractTxReceipt $txR
     * @param LedgerFlag $lF
     * @param string $hash160
     * @param int $amount
     * @param string|null $asset
     */
    public function __construct(AbstractProtocolChain $p, AbstractTxReceipt $txR, LedgerFlag $lF, string $hash160, int $amount, ?string $asset = null)
    {
        $this->p = $p;
        $this->txR = $txR;

        if (strlen($hash160) !== 20) {
            throw new \InvalidArgumentException('Hash160 must be 20 raw bytes');
        }

        if ($amount < 0 || $amount > UInts::MAX) {
            throw new \OutOfRangeException('Invalid TxReceipt.ledgerEntry amount');
        }

        $this->flag = $lF;
        $this->hash160 = $hash160;
        $this->amount = $amount;
        $this->asset = $asset;
    }

    /**
     * @return AbstractTxReceipt
     */
    public function getReceipt(): AbstractTxReceipt
    {
        return $this->txR;
    }

    /**
     * @return string
     */
    public function account(): string
    {
        return $this->hash160;
    }

    /**
     * @return LedgerFlag
     */
    public function flag(): LedgerFlag
    {
        return $this->flag;
    }

    /**
     * @return int
     */
    public function amount(): int
    {
        return $this->amount;
    }

    /**
     * @return string|null
     */
    public function asset(): ?string
    {
        return $this->asset;
    }

    /**
     * @return void
     */
    public function markApplicable(): void
    {
        $this->applicable = true;
    }

    /**
     * @return bool
     */
    public function isApplicable(): bool
    {
        return $this->applicable;
    }

    /**
     *
     * @return string
     */
    public function serializeRawBytes(): string
    {
        $ser = $this->hash160;
        $ser .= UInts::Encode_UInt2LE($this->flag->dec());
        $ser .= UInts::Encode_UInt8LE($this->amount);
        $ser .= str_pad(strval($this->asset), 8, "\0", STR_PAD_LEFT);
        $ser .= $this->applicable ? "\1" : "\0";

        return $ser;
    }
}
