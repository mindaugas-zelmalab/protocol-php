<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use ForwardBlock\Protocol\Protocol;
use ForwardBlock\Protocol\Transactions\Receipts\AbstractTxReceipt;
use ForwardBlock\Protocol\Validator;

/**
 * Class AbstractTxFlag
 * @package ForwardBlock\Protocol\Transactions
 */
abstract class AbstractTxFlag
{
    /** @var Protocol */
    protected Protocol $p;
    /** @var int */
    protected int $id;
    /** @var string */
    protected string $name;
    /** @var bool */
    protected bool $isEnabled;

    /**
     * AbstractTxFlag constructor.
     * @param Protocol $p
     * @param int $id
     * @param string $name
     * @param bool $status
     */
    public function __construct(Protocol $p, int $id, string $name, bool $status)
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
        $this->isEnabled = $status;
    }

    /**
     * @return $this
     */
    public function enabled(): self
    {
        $this->isEnabled = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disabled(): self
    {
        $this->isEnabled = false;
        return $this;
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
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @return AbstractTxConstructor
     */
    abstract public function create(): AbstractTxConstructor;

    /**
     * @return AbstractTxReceipt
     */
    abstract public function receipt(): AbstractTxReceipt;
}
