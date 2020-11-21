<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use ForwardBlock\Protocol\Protocol;

/**
 * Class TxFactory
 * @package ForwardBlock\Protocol\Transactions
 */
class TxFactory
{
    /** @var Protocol */
    private Protocol $protocol;
    /** @var \Closure|null */
    private ?\Closure $txReceiptFeeLogs = null;

    /**
     * TxFactory constructor.
     * @param Protocol $protocol
     */
    public function __construct(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @param \Closure $callback
     */
    public function txReceiptFeeLogsCallback(\Closure $callback): void
    {
        $this->txReceiptFeeLogs = $callback;
    }


}
