<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions\Receipts;

use ForwardBlock\Protocol\AbstractProtocolChain;
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

    public function __construct(AbstractProtocolChain $p, AbstractTxReceipt $txR)
    {
        $this->p = $p;
        $this->txR = $txR;


    }
}
