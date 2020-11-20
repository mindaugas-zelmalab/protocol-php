<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\Exception\ProtocolConfigException;
use ForwardBlock\Protocol\KeyPair\KeyPairFactory;
use FurqanSiddiqui\ECDSA\Curves\Secp256k1;

/**
 * Class Protocol
 * @package ForwardBlock\Protocol
 */
class Protocol implements ProtocolConstants
{
    /** @var Config */
    private Config $config;
    /** @var KeyPairFactory */
    private KeyPairFactory $kpF;

    /**
     * Protocol constructor.
     * @param array $config
     * @throws ProtocolConfigException
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
        $this->kpF = new KeyPairFactory($this);
    }

    /**
     * @return Secp256k1
     */
    public function secp256k1(): Secp256k1
    {
        return Secp256k1::getInstance();
    }

    /**
     * @return Config
     */
    public function config(): Config
    {
        return $this->config;
    }

    /**
     * @return KeyPairFactory
     */
    public function keyPair(): KeyPairFactory
    {
        return $this->kpF;
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
