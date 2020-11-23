<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Blocks;

use ForwardBlock\Protocol\Transactions\AbstractTxReceipt;
use ForwardBlock\Protocol\Transactions\Transaction;

/**
 * Class BlockTxReceipts
 * @package ForwardBlock\Protocol\Blocks
 */
class BlockTxReceipts extends AbstractMerkleMap
{
    /**
     * @param AbstractTxReceipt $r
     * @throws \ForwardBlock\Protocol\Exception\TxEncodeException
     */
    public function append(AbstractTxReceipt $r): void
    {
        $this->append2Tree($r->getReceiptHash()->raw(), $r);
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
