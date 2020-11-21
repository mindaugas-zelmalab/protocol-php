<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\Protocol;

/**
 * Class AbstractTxReceipt
 * @package ForwardBlock\Protocol\Transactions
 */
class AbstractTxReceipt
{
    /** @var Protocol */
    private Protocol $p;
    /** @var Transaction */
    private Transaction $tx;

    /** @var int */
    private int $status;
    /** @var Binary */
    private Binary $data;
    /** @var array */
    private array $ledgerEntries;

    /**
     * TxReceiptGenerator constructor.
     * @param Protocol $p
     * @param Transaction $tx
     */
    public function __construct(Protocol $p, Transaction $tx)
    {
        $this->p = $p;
        $this->tx = $tx;
    }

    private function generateLedgerEntries(): void
    {

    }
}
