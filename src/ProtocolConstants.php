<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol;

use FurqanSiddiqui\BIP32\ECDSA\Curves;

/**
 * Interface ProtocolConstants
 * @package ForwardBlock\Protocol
 */
interface ProtocolConstants
{
    /** @var string implemented protocol version */
    public const VERSION = "0.0.10";
    /** @var int (Major * 10000 + Minor * 100 + Release) */
    public const VERSION_ID = 10;

    /** @var int Number of digits after decimal; Warning: Uint are always 64bit */
    public const SCALE = 8; //  Default 8
    /** @var int Maximum size of a serialized transaction */
    public const MAX_TRANSACTION_SIZE = 0xffff;
    /** @var int Maximum arbitrary data storable in a transaction */
    public const MAX_ARBITRARY_DATA = 0xEA60; // 60KB
    /** @var int Maximum size of a block */
    public const MAX_BLOCK_SIZE = 0x0F4240; // 1MB
    /** @var int Maximum length of transaction memo */
    public const MAX_TX_MEMO_LEN = 16; // 16 bytes
    /** @var int ECC curve Secp256k1 */
    public const ECDSA_CURVE = Curves::SECP256K1;
}
