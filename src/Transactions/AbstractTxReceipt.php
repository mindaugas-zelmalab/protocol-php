<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Exception\TxDecodeException;
use ForwardBlock\Protocol\Exception\TxEncodeException;
use ForwardBlock\Protocol\Math\UInts;
use ForwardBlock\Protocol\ProtocolConstants;
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
     * @param AbstractProtocolChain $p
     * @param Transaction $tx
     * @param int $blockHeightContext
     * @param Binary $encoded
     * @return static
     * @throws TxDecodeException
     */
    public static function Decode(AbstractProtocolChain $p, Transaction $tx, int $blockHeightContext, Binary $encoded): self
    {
        $receipt = new static($p, $tx, $blockHeightContext);
        $read = $encoded->read();
        $read->throwUnderflowEx();

        // Step 1
        $status = UInts::Decode_UInt2LE($read->first(2));
        $receipt->setStatus($status);

        // Step 2
        $dataLen = UInts::Decode_UInt1LE($read->next(1));
        if ($dataLen > 0) { // Step 2.1
            $receipt->data()->append($read->next($dataLen));
        }

        // Step 3
        $leBatches = UInts::Decode_UInt1LE($read->next(1));
        if ($leBatches > 0) {
            for ($lB = 0; $lB < $leBatches; $lB++) {
                $leC = UInts::Decode_UInt1LE($read->next(1));
                $leBatch = [];
                if ($leC < 0 || $leC > ProtocolConstants::MAX_LEDGER_ENTRIES) {
                    throw new TxDecodeException(
                        sprintf('Receipt batch contains %d ledger entries, allowed are 1 to %d', $leC, ProtocolConstants::MAX_LEDGER_ENTRIES)
                    );
                }

                for ($leN = 0; $leN < $leC; $leN++) {
                    $hash160 = $read->next(20); // Account Hash160 bytes
                    $flag = $p->txFlags()->ledgerFlags()->get(UInts::Decode_UInt2LE($read->next(2)));
                    $amount = UInts::Decode_UInt8LE($read->next(8));
                    $asset = trim($read->next(8));
                    if (!$asset) { // Native token?
                        $asset = null;
                    }

                    $status = $read->next(1);
                    if (!in_array($status, ["\0", "\1"])) {
                        throw new TxDecodeException(sprintf('Invalid ledger entry # %d status byte', $leN + 1));
                    }

                    $leBatch[] = $receipt->createLedgerEntry($flag, $hash160, $amount, $asset);
                }

                $receipt->registerLedgerEntriesBatch(...$leBatch);
            }
        }

        return $receipt;
    }

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
    public function ledgerEntries(): LedgerEntries
    {
        return $this->ledgerEntries;
    }

    /**
     * @return Binary
     */
    public function ledgerEntriesHash(): Binary
    {
        $serBatch = $this->ledgerEntries->serializedBatches();
        return $this->p->hash256(new Binary($this->tx->hash()->raw() . $serBatch))->readOnly(true);
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
    public function createLedgerEntry(LedgerFlag $lF, string $hash160, int $amount, ?string $assetId = null): LedgerEntry
    {
        return new LedgerEntry($this->p, $this, $lF, $hash160, $amount, $assetId);
    }

    /**
     * @param LedgerEntry ...$entries
     */
    public function registerLedgerEntriesBatch(LedgerEntry ...$entries): void
    {
        $this->ledgerEntries->addBatch(...$entries);
    }
}
