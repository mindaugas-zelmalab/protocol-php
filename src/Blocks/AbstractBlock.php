<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Blocks;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Transactions\CheckedTx;

/**
 * Class AbstractBlock
 * @package ForwardBlock\Protocol\Blocks
 */
abstract class AbstractBlock
{
    /** @var AbstractProtocolChain */
    protected AbstractProtocolChain $p;

    /** @var int */
    protected int $version;
    /** @var int */
    protected int $timeStamp;
    /** @var string */
    protected string $prevBlockId;
    /** @var int */
    protected int $txCount = 0;
    /** @var int */
    protected int $totalIn = 0;
    /** @var int */
    protected int $totalOut = 0;
    /** @var int */
    protected int $totalFee = 0;
    /** @var string|null */
    protected ?string $forger = null;
    /** @var array */
    protected array $signs = [];
    /** @var int */
    protected int $reward = 0;
    /** @var string|null */
    protected ?string $merkleTx = null;
    /** @var string|null */
    protected ?string $merkleTxReceipts = null;
    /** @var int */
    protected int $bodySize;

    /** @var BlockTxs */
    protected BlockTxs $txs;
    /** @var BlockTxReceipts */
    protected BlockTxReceipts $txsReceipts;

    /**
     * AbstractBlock constructor.
     * @param AbstractProtocolChain $p
     */
    public function __construct(AbstractProtocolChain $p)
    {
        $this->p = $p;
        $this->txs = new BlockTxs($p);
        $this->txsReceipts = new BlockTxReceipts($p);
    }

    /**
     * @param CheckedTx $tx
     */
    protected function appendCheckedTx(CheckedTx $tx): void
    {
        $this->txs->append($tx->tx());
    }

    public function serialize(): Binary
    {

    }
}
