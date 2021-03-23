<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Exception\TxDecodeException;
use ForwardBlock\Protocol\Validator;

/**
 * Class AbstractTxFlag
 * @package ForwardBlock\Protocol\Transactions
 */
abstract class AbstractTxFlag
{
    /** @var AbstractProtocolChain */
    protected AbstractProtocolChain $p;
    /** @var int */
    protected int $id;
    /** @var string */
    protected string $name;

    /**
     * AbstractTxFlag constructor.
     * @param AbstractProtocolChain $p
     * @param int $id
     * @param string $name
     */
    public function __construct(AbstractProtocolChain $p, int $id, string $name)
    {
        if ($id < 0 || $id > 0xffff) {
            throw new \OutOfRangeException('Invalid flag ID/decimal');
        }

        if (!Validator::isValidTxFlagName($name)) {
            throw new \InvalidArgumentException('Invalid flag name');
        }

        $this->p = $p;
        $this->id = $id;
        $this->name = strtoupper($name);
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param array $args
     * @return AbstractTxConstructor
     */
    abstract public function create(array $args): AbstractTxConstructor;

    /**
     * @param Binary $encoded
     * @return AbstractPreparedTx
     * @throws TxDecodeException
     */
    abstract public function decode(Binary $encoded): AbstractPreparedTx;

    /**
     * @param AbstractPreparedTx $tx
     * @param int $blockHeightContext
     * @return AbstractTxReceipt
     */
    abstract public function newReceipt(AbstractPreparedTx $tx, int $blockHeightContext): AbstractTxReceipt;

    /**
     * @param AbstractPreparedTx $tx
     * @param Binary $bytes
     * @param int $blockHeightContext
     * @return AbstractTxReceipt
     */
    abstract public function decodeReceipt(AbstractPreparedTx $tx, Binary $bytes, int $blockHeightContext): AbstractTxReceipt;
}
