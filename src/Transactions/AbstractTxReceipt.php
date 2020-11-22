<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Transactions\Receipts\LedgerEntry;

/**
 * Class AbstractTxReceipt
 * @package ForwardBlock\Protocol\Transactions
 */
abstract class AbstractTxReceipt
{
    /** @var AbstractProtocolChain */
    protected AbstractProtocolChain $p;
    /** @var Transaction */
    protected Transaction $tx;

    /** @var int|null */
    protected ?int $status = null;
    /** @var Binary */
    protected Binary $data;
    /** @var array */
    protected array $ledgerEntries;

    /**
     * AbstractTxReceipt constructor.
     * @param AbstractProtocolChain $p
     * @param Transaction $tx
     * @param int $blockHeightContext
     */
    public function __construct(AbstractProtocolChain $p, Transaction $tx, int $blockHeightContext)
    {
        $this->p = $p;
        $this->tx = $tx;
        $this->data = new Binary();
        $this->ledgerEntries = [];

        // Call generateLedgerEntries
        $this->generateLedgerEntries($blockHeightContext);
    }

    /**
     * @return Transaction
     */
    public function getTx(): Transaction
    {
        return $this->tx;
    }

    /**
     * @return Binary
     */
    public function data(): Binary
    {
        return $this->data;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setStatus(int $code): self
    {
        $this->status = $code;
        return $this;
    }

    /**
     * @return int
     */
    public function status(): int
    {
        return $this->status ?? -1;
    }

    /**
     * @return array
     */
    public function ledgerEntries(): array
    {
        return $this->ledgerEntries;
    }

    /**
     * @param int $blockHeightContext
     */
    abstract protected function generateLedgerEntries(int $blockHeightContext): void;

    /**
     * @param string $hash160
     * @param int $flag
     * @param int $amount
     * @param string|null $assetId
     */
    protected function createLedgerEntry(string $hash160, int $flag, int $amount, ?string $assetId = null): void
    {
        $this->ledgerEntries[] = new LedgerEntry($this->p, $this, $hash160, $flag, $amount, $assetId);
    }
}
