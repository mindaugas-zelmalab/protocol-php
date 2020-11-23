<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Blocks;

use ForwardBlock\Protocol\Transactions\Transaction;

/**
 * Class BlockTxs
 * @package ForwardBlock\Protocol\Blocks
 */
class BlockTxs extends AbstractMerkleMap
{
    /**
     * @param Transaction $tx
     */
    public function append(Transaction $tx): void
    {
        $this->append2Tree($tx->hash()->raw(), $tx);
    }

    /**
     * @param int $dec
     * @return Transaction
     */
    public function index(int $dec): Transaction
    {
        return parent::index($dec);
    }

    /**
     * @return Transaction
     */
    public function current(): Transaction
    {
        return parent::current();
    }
}
