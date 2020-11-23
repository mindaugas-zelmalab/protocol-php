<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Blocks;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Transactions\AbstractTxReceipt;

/**
 * Class BlockTxReceipts
 * @package ForwardBlock\Protocol\Blocks
 */
class BlockTxReceipts implements \Iterator, \Countable
{
    /** @var AbstractProtocolChain */
    private AbstractProtocolChain $p;
    /** @var int */
    private int $count;
    /** @var array */
    private array $receipts;

    /**
     * BlockTxReceipts constructor.
     * @param AbstractProtocolChain $p
     */
    public function __construct(AbstractProtocolChain $p)
    {
        $this->p = $p;
        $this->count = 0;
        $this->receipts = [];
    }

    /**
     * @param AbstractTxReceipt $r
     * @throws \ForwardBlock\Protocol\Exception\TxEncodeException
     */
    public function append(AbstractTxReceipt $r): void
    {
        $this->receipts[bin2hex($r->getReceiptHash()->raw())] = $r;
        $this->count++;
    }

    /**
     * @return Binary
     */
    public function merkleRoot(): Binary
    {
        if (!$this->count) {
            return new Binary(str_repeat("\0", 32));
        }

        $bytes = [];
        $allReceiptHashes = array_keys($this->receipts);
        foreach ($allReceiptHashes as $hash) {
            $bytes[] = hex2bin($hash);
        }

        return $this->p->hash256(new Binary(implode("", $bytes)))->readOnly(true);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->receipts;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return AbstractTxReceipt
     */
    public function current(): AbstractTxReceipt
    {
        return current($this->receipts);
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return key($this->receipts);
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->receipts);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->receipts);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->receipts) !== null;
    }
}
