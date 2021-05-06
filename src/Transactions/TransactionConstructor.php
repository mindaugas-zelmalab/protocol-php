<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;

/**
 * Class TransactionConstructor
 * @package ForwardBlock\Protocol\Transactions
 */
class TransactionConstructor extends AbstractTxConstructor
{
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

    /**
     * @param Binary $data
     * @return $this
     */
    public function setData(Binary $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function beforeSerialize(): void
    {
    }
}
