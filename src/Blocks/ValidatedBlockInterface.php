<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Blocks;

/**
 * Interface ValidatedBlockInterface
 * @package ForwardBlock\Protocol\Blocks
 */
interface ValidatedBlockInterface
{
    /**
     * @return Block
     */
    public function block(): Block;

    /**
     * @return bool
     */
    public function isValidated(): bool;
}
