<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Exception;

/**
 * Class TxConstructException
 * @package ForwardBlock\Protocol\Exception
 */
class TxConstructException extends TransactionsException
{
    /** @var string|null */
    protected ?string $prop = null;

    /**
     * @param string $prop
     * @param string $msg
     * @param int $code
     * @return static
     */
    public static function Prop(string $prop, string $msg, int $code = 0): self
    {
        $ex = new self($msg, $code);
        $ex->setProp($prop);
        return $ex;
    }

    /**
     * @param string $prop
     */
    public function setProp(string $prop): void
    {
        $this->prop = $prop;
    }

    /**
     * @return string|null
     */
    public function getProp(): ?string
    {
        return $this->prop;
    }
}
