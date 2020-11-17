<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\Exception\ProtocolConfigException;

/**
 * Class Protocol
 * @package ForwardBlock\Protocol
 */
class Protocol implements ProtocolConstants
{
    /** @var Config */
    private Config $config;

    /**
     * Protocol constructor.
     * @param array $config
     * @throws ProtocolConfigException
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    /**
     * @return Config
     */
    public function config(): Config
    {
        return $this->config;
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
