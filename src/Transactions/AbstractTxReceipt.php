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
     */
    public function __construct(AbstractProtocolChain $p, Transaction $tx)
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
     * @param int $blockHeight
     */
    abstract public function generateLedgerEntries(int $blockHeight): void;

    /**
     * @param string $hash160
     * @param int $flag
     * @param int $amount
     * @param string|null $assetId
     * @return LedgerEntry
     */
    protected function createLedgerEntry(string $hash160, int $flag, int $amount, ?string $assetId = null): LedgerEntry
    {
        return new LedgerEntry($this->p, $this, $hash160, $flag, $amount, $assetId);
    }
}
