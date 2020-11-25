<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Blocks;

use ForwardBlock\Protocol\Transactions\AbstractTxReceipt;

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
     * @return AbstractTxReceipt
     */
    public function index(int $dec): AbstractTxReceipt
    {
        return parent::index($dec);
    }

    /**
     * @return AbstractTxReceipt
     */
    public function current(): AbstractTxReceipt
    {
        return parent::current();
    }
}
