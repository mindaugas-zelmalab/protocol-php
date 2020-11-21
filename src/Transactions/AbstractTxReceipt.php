<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;

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

    /** @var int */
    protected int $status;
    /** @var Binary */
    protected Binary $data;
    /** @var array */
    protected array $ledgerEntries;

    /**
     * TxReceiptGenerator constructor.
     * @param AbstractProtocolChain $p
     * @param Transaction $tx
     */
    public function __construct(AbstractProtocolChain $p, Transaction $tx)
    {
        $this->p = $p;
        $this->tx = $tx;
    }

    /**
     * @return void
     */
    abstract protected function generateLedgerEntries(): void;
}
