<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Exception\TxEncodeException;
use ForwardBlock\Protocol\Math\UInts;
use ForwardBlock\Protocol\Transactions\Receipts\LedgerEntries;
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
    /** @var LedgerEntries */
    protected LedgerEntries $ledgerEntries;

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
        $this->ledgerEntries = new LedgerEntries();

        // Generate ledger entries here
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
     * @return LedgerEntries
     */
    public function LedgerEntries(): LedgerEntries
    {
        return $this->ledgerEntries;
    }

    /**
     * @return Binary
     * @throws TxEncodeException
     */
    public function serialize(): Binary
    {
        if (!is_int($this->status)) {
            throw new TxEncodeException('TxReceipt status not set');
        }

        $ser = new Binary();
        $ser->append(UInts::Encode_UInt2LE($this->status));

        if ($this->data->sizeInBytes > 0xff) {
            throw new TxEncodeException('TxReceipt data cannot exceed 255 bytes');
        }

        $ser->append(UInts::Encode_UInt1LE($this->data->sizeInBytes));
        $ser->append($this->data);
        $ser->append($this->ledgerEntries->serializedBatches());

        return $ser->readOnly(true);
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
        $this->ledgerEntries->addBatch(...$entries);
    }
}
