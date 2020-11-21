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
     * @param int $blockHeight
     */
    abstract public function generateLedgerEntries(int $blockHeight): void;
}
