<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use ForwardBlock\Protocol\AbstractProtocolChain;

/**
 * Class AbstractTxFactory
 * @package ForwardBlock\Protocol\Transactions
 */
abstract class AbstractTxFactory
{
    /** @var AbstractProtocolChain */
    protected AbstractProtocolChain $p;

    /**
     * AbstractTxFactory constructor.
     * @param AbstractProtocolChain $p
     */
    public function __construct(AbstractProtocolChain $p)
    {
        $this->p = $p;
    }
}
