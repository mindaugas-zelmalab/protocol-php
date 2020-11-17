<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol;

/**
 * Class Base58Check
 * @package ForwardBlock\Protocol
 */
class Base58Check extends \FurqanSiddiqui\Base58\Base58Check
{
    public const CHARSET = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
    public const CHECKSUM_BYTES = 4;

    /** @var self|null */
    private static ?self $instance = null;

    /**
     * @return Base58Check
     */
    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new Base58Check();
            self::$instance->charset(self::CHARSET)
                ->checksum(self::CHECKSUM_BYTES, null);
        }

        return self::$instance;
    }
}
