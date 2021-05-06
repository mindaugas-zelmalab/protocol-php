<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Transactions\Traits\CustomDataTrait;
use ForwardBlock\Protocol\Transactions\Traits\RecipientTrait;
use ForwardBlock\Protocol\Transactions\Traits\TransferObjectsTrait;

/**
 * Class TransactionConstructor
 * @package ForwardBlock\Protocol\Transactions
 */
class TransactionConstructor extends AbstractTxConstructor
{
    use RecipientTrait;
    use CustomDataTrait;
    use TransferObjectsTrait;

    /**
     * TransactionConstructor constructor.
     * @param AbstractProtocolChain $p
     * @param int $ver
     * @param AbstractTxFlag $flag
     * @param int $epoch
     * @throws \ForwardBlock\Protocol\Exception\TxConstructException
     */
    public function __construct(AbstractProtocolChain $p, int $ver, AbstractTxFlag $flag, int $epoch)
    {
        parent::__construct($p, $ver, $flag, $epoch);
    }

    public function beforeSerialize(): void
    {
    }
}
