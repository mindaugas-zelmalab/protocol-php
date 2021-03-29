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
     * Must return hash160 in BASE16!
     * @return string
     */
    public function getHash160(): string;

    /**
     * @return array
     */
    public function getAllPublicKeys(): array;

    /**
     * @return bool
     */
    public function canForgeBlocks(): bool;
}
