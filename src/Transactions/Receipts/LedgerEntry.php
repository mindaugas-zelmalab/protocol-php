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
    /** @var int */
    protected int $flag;
    /** @var int */
    protected int $amount;
    /** @var string|null */
    protected ?string $asset = null;
    /** @var bool */
    protected bool $appliedSuccess = false;

    /**
     * LedgerEntry constructor.
     * @param AbstractProtocolChain $p
     * @param AbstractTxReceipt $txR
     * @param string $hash160
     * @param int $flag
     * @param int $amount
     * @param string|null $asset
     */
    public function __construct(AbstractProtocolChain $p, AbstractTxReceipt $txR, string $hash160, int $flag, int $amount, ?string $asset = null)
    {
        $this->p = $p;
        $this->txR = $txR;

        if ($flag < 0 || $flag > 0xff) {
            throw new \OutOfBoundsException('Invalid TxReceipt.ledgerEntry flag');
        }

        if ($amount < 0 || $amount > UInts::MAX) {
            throw new \OutOfRangeException('Invalid TxReceipt.ledgerEntry amount');
        }

        $this->hash160 = $hash160;
        $this->flag = $flag;
        $this->amount = $amount;
        $this->asset = $asset;
    }

    /**
     * @return void
     */
    public function markApplied(): void
    {
        $this->appliedSuccess = true;
    }

    /**
     * @return bool
     */
    public function isApplied(): bool
    {
        return $this->appliedSuccess;
    }
}
