<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Blocks;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;

/**
 * Class AbstractMerkleMap
 * @package ForwardBlock\Protocol\Blocks
 */
abstract class AbstractMerkleMap implements \Iterator, \Countable
{
    /** @var AbstractProtocolChain */
    protected AbstractProtocolChain $p;
    /** @var int */
    protected int $count;
    /** @var array */
    protected array $tree;
    /** @var array */
    protected array $indexMap;
    /** @var array */
    protected array $rawHashes;

    /**
     * BlockTxs constructor.
     * @param AbstractProtocolChain $p
     */
    public function __construct(AbstractProtocolChain $p)
    {
        $this->p = $p;
        $this->count = 0;
        $this->tree = [];
        $this->indexMap = [];
        $this->rawHashes = [];
    }

    /**
     * @param string $rawHash
     * @param object $obj
     */
    protected function append2Tree(string $rawHash, object $obj): void
    {
        $hexKey = bin2hex($rawHash);
        $this->tree[$hexKey] = $obj;
        $this->indexMap[] = $hexKey;
        $this->rawHashes[] = $rawHash;
        $this->count++;
    }

    /**
     * @param int $dec
     * @return mixed
     */
    public function index(int $dec): object
    {
        return $this->tree[$this->indexMap[$dec]];
    }

    /**
     * @return mixed
     */
    public function current(): object
    {
        return current($this->tree);
    }

    /**
     * @return Binary
     */
    public function merkleRoot(): Binary
    {
        if (!$this->count) {
            return new Binary(str_repeat("\0", 32));
        }

        return $this->p->hash256(new Binary(implode("", $this->rawHashes)))->readOnly(true);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->tree;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return key($this->tree);
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->tree);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->tree);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->tree) !== null;
    }
}
