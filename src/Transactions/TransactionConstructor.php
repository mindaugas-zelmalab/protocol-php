<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;

/**
 * Class TransactionConstructor
 * @package ForwardBlock\Protocol\Transactions
 */
class TransactionConstructor extends AbstractTxConstructor
{
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
