<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Exception;

use ForwardBlock\Protocol\Transactions\AbstractPreparedTx;
use Throwable;

/**
 * Class TxDecodeException
 * @package ForwardBlock\Protocol\Exception
 */
class TxDecodeException extends TransactionsException
{
    /** @var array|null */
    private ?array $incompleteTx;

    /**
     * TxDecodeException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param AbstractPreparedTx|null $tx
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, ?AbstractPreparedTx $tx = null)
    {
        parent::__construct($message, $code, $previous);

        if($tx) {
            $this->incompleteTx = $tx->array();
        }
    }

    /**
     * @param AbstractPreparedTx $tx
     * @param string $error
     * @param int|null $code
     * @param Throwable|null $prev
     * @return static
     */
    public static function Incomplete(AbstractPreparedTx $tx, string $error, int $code = 0, ?Throwable $prev = null): self
    {
        return new self($error, $code, $prev, $tx);
    }

    /**
     * @return array|null
     */
    public function incompleteTx(): ?array
    {
        return $this->incompleteTx;
    }
}
