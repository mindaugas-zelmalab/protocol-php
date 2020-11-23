<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Transactions\Receipts\LedgerEntry;
use ForwardBlock\Protocol\Transactions\Receipts\LedgerFlag;

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
     * @noinspection PhpUnusedParameterInspection
     */
    public function __construct(AbstractProtocolChain $p, Transaction $tx, int $blockHeightContext)
    {
        $this->p = $p;
        $this->tx = $tx;
        $this->data = new Binary();
        $this->ledgerEntries = [];
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
     * @param LedgerFlag $lF
     * @param string $hash160
     * @param int $amount
     * @param string|null $assetId
     * @return LedgerEntry
     */
    protected function createLedgerEntry(LedgerFlag $lF, string $hash160, int $amount, ?string $assetId = null): LedgerEntry
    {
        return new LedgerEntry($this->p, $this, $lF, $hash160, $amount, $assetId);
    }

    /**
     * @param LedgerEntry ...$entries
     */
    protected function registerLedgerEntriesBatch(LedgerEntry ...$entries): void
    {
        $this->ledgerEntries[] = $entries;
    }
}
