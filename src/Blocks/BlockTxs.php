<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Blocks;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Transactions\Transaction;

/**
 * Class BlockTxs
 * @package ForwardBlock\Protocol\Blocks
 */
class BlockTxs implements \Iterator, \Countable
{
    /** @var AbstractProtocolChain */
    private AbstractProtocolChain $p;
    /** @var int */
    private int $count;
    /** @var array */
    private array $txs;

    /**
     * BlockTxs constructor.
     * @param AbstractProtocolChain $p
     */
    public function __construct(AbstractProtocolChain $p)
    {
        $this->p = $p;
        $this->count = 0;
        $this->txs = [];
    }

    /**
     * @param Transaction $tx
     */
    public function append(Transaction $tx): void
    {
        $this->txs[bin2hex($tx->hash()->raw())] = $tx;
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
        $allTxHashes = array_keys($this->txs);
        foreach ($allTxHashes as $txHash) {
            $bytes[] = hex2bin($txHash);
        }

        return $this->p->hash256(new Binary(implode("", $bytes)))->readOnly(true);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->txs;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return Transaction
     */
    public function current(): Transaction
    {
        return current($this->txs);
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return key($this->txs);
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->txs);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->txs);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->txs) !== null;
    }
}
