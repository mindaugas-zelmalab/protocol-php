<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

/**
 * Interface TxFlags
 * @package ForwardBlock\Protocol\Transactions
 */
interface TxFlags
{
    /** @var int */
    public const FORGE = 0x0a;
    /** @var int */
    public const REGISTER = 0x64;
    /** @var int */
    public const TRANSFER = 0xc8;
    /** @var int */
    public const BURN = 0xc9;
    /** @var int */
    public const ACCOUNT_LOCK = 0x0190;
    /** @var int */
    public const ACCOUNT_UNLOCK = 0x0191;
    /** @var int */
    public const ACCOUNT_UPGRADE = 0x0192;
}
