<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions\Receipts;

use ForwardBlock\Protocol\Protocol;
use ForwardBlock\Protocol\Transactions\AbstractTxReceipt;

/**
 * Class LedgerEntry
 * @package ForwardBlock\Protocol\Transactions\Receipts
 */
class LedgerEntry
{
    /** @var Protocol */
    private Protocol $p;
    /** @var AbstractTxReceipt */
    private AbstractTxReceipt $txR;

    /** @var string */
    private string $hash160;
    /** @var int */
    private int $flag;
    /** @var int */
    private int $amount;
    /** @var string|null */
    private ?string $asset = null;
    /** @var bool */
    private bool $appliedSuccess = false;

    public function __construct(Protocol $p, AbstractTxReceipt $txR)
    {
        $this->p = $p;
        $this->txR = $txR;


    }
}
