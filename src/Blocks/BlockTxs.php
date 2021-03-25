<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Blocks;

use ForwardBlock\Protocol\Transactions\AbstractPreparedTx;

/**
 * Class BlockTxs
 * @package ForwardBlock\Protocol\Blocks
 */
class BlockTxs extends AbstractMerkleMap
{
    /**
     * @param AbstractPreparedTx $tx
     */
    public function append(AbstractPreparedTx $tx): void
    {
        $this->append2Tree($tx->hash()->raw(), $tx);
    }

    /**
     * @param int $dec
     * @return AbstractPreparedTx
     */
    public function index(int $dec): AbstractPreparedTx
    {
        return parent::index($dec);
    }

    /**
     * @return AbstractPreparedTx
     */
    public function current(): AbstractPreparedTx
    {
        return parent::current();
    }
}
