<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol;

use Comely\DataTypes\Buffer\Binary;

/**
 * Class Protocol
 * @package ForwardBlock\Protocol
 */
class Protocol implements ProtocolConstants
{
    /** @var self|null */
    private static ?self $instance = null;

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        if (!static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * @param Binary $bin
     * @return Binary
     */
    public static function Hash256(Binary $bin): Binary
    {
        return $bin->hash()->digest("sha256", 2);
    }

    /**
     * @param Binary $bin
     * @return Binary
     */
    public static function Hash160(Binary $bin): Binary
    {
        return $bin->hash()->sha256()
            ->hash()->ripeMd160();
    }
}
