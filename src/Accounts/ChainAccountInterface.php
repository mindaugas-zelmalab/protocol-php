<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Accounts;

/**
 * Interface ChainAccountInterface
 * @package ForwardBlock\Protocol\Accounts
 */
interface ChainAccountInterface
{
    /**
     * @return array
     */
    public function getAllPublicKeys(): array;

    /**
     * @return bool
     */
    public function canForgeBlocks(): bool;
}
