<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use ForwardBlock\Protocol\Exception\TxFlagException;
use ForwardBlock\Protocol\Math\UInts;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Transactions\Receipts\LedgerFlags;
use ForwardBlock\Protocol\Validator;

/**
 * Class TxFlags
 * @package ForwardBlock\Protocol\Transactions
 */
class TxFlags
{
    /** @var AbstractProtocolChain */
    private AbstractProtocolChain $p;
    /** @var array */
    private array $flags = [];
    /** @var array */
    private array $namesMap = [];
    /** @var int */
    private int $count = 0;
    /** @var LedgerFlags */
    private LedgerFlags $ledgerFlags;

    /**
     * TxFlags constructor.
     * @param AbstractProtocolChain $p
     */
    public function __construct(AbstractProtocolChain $p)
    {
        $this->p = $p;
        $this->ledgerFlags = new LedgerFlags();
    }

    /**
     * @return LedgerFlags
     */
    public function ledgerFlags(): LedgerFlags
    {
        return $this->ledgerFlags;
    }

    /**
     * @param AbstractTxFlag $flag
     * @return $this
     */
    public function append(AbstractTxFlag $flag): self
    {
        if ($this->has($flag->id())) {
            throw new \InvalidArgumentException(sprintf('Tx Flag "0x%s" is already registered', UInts::Encode_UInt1LE($flag->id())));
        }

        if ($this->hasName($flag->name())) {
            throw new \InvalidArgumentException(sprintf('Tx Flag "%s" is already registered', $flag->name()));
        }

        $this->flags[$flag->id()] = $flag;
        $this->namesMap[$flag->name()] = $flag;
        $this->count++;
        return $this;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function has(int $id): bool
    {
        return isset($this->flags[$id]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasName(string $name): bool
    {
        if (!Validator::isValidTxFlagName($name)) {
            throw new \InvalidArgumentException('Invalid tx flag name to search');
        }

        return isset($this->namesMap[strtoupper($name)]);
    }

    /**
     * @param int $id
     * @return AbstractTxFlag
     * @throws TxFlagException
     */
    public function get(int $id): AbstractTxFlag
    {
        if (!$this->has($id)) {
            throw new TxFlagException(sprintf('Cannot find TxFlag with ID %d (0x%s)', $id, bin2hex(UInts::Encode_UInt1LE($id))));
        }

        return $this->flags[$id];
    }

    /**
     * @param string $name
     * @return AbstractTxFlag
     */
    public function getWithName(string $name): AbstractTxFlag
    {
        $name = strtoupper($name);
        if (!$this->hasName($name)) {
            throw new \InvalidArgumentException(sprintf('Cannot find "%s" TxFlag', $name));
        }

        return $this->namesMap[$name];
    }
}
