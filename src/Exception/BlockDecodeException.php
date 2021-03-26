<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Exception;

use ForwardBlock\Protocol\Blocks\Block;
use Throwable;

/**
 * Class BlockDecodeException
 * @package ForwardBlock\Protocol\Exception
 */
class BlockDecodeException extends BlocksException
{
    /** @var array|null */
    private ?array $incompleteBlocks = null;

    /**
     * BlockDecodeException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param Block|null $block
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, ?Block $block = null)
    {
        parent::__construct($message, $code, $previous);
        if ($block) {
            $this->incompleteBlocks = $block->array(true);
        }
    }

    /**
     * @param Block $block
     * @param string $error
     * @param int $code
     * @param Throwable|null $prev
     * @return static
     */
    public static function Incomplete(Block $block, string $error, int $code = 0, ?Throwable $prev = null): self
    {
        return new self($error, $code, $prev, $block);
    }

    /**
     * @return array|null
     */
    public function incompleteBlock(): ?array
    {
        return $this->incompleteBlocks;
    }
}
