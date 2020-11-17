<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol;

use Comely\DataTypes\Integers;
use ForwardBlock\Protocol\Exception\ProtocolConfigException;

/**
 * Class Config
 * @package ForwardBlock\Protocol
 * @property-read int $accountsPrefix
 * @property-read string $fancyPrefix
 * @property-read int $fancyPrefixLen
 * @property-read int $wifPubPrefix
 * @property-read int $wifPrvPrefix
 */
class Config
{
    /** @var int */
    private int $accountsPrefix;
    /** @var string */
    private string $fancyPrefix;
    /** @var int */
    private int $fancyPrefixLen;
    /** @var int */
    private int $wifPubPrefix;
    /** @var int */
    private int $wifPrvPrefix;

    /**
     * Protocol constructor.
     * @param array $args
     * @throws ProtocolConfigException
     */
    public function __construct(array $args)
    {
        // Account Prefix
        $accPrefix = $args["accountsPrefix"];
        if (!is_int($accPrefix) || $accPrefix < 0x00 || $accPrefix > 0xff) {
            throw new ProtocolConfigException('Invalid accounts prefix');
        }

        $this->accountsPrefix = $accPrefix;

        // Fancy Prefix
        $fancy = $args["fancyPrefix"];
        if (!is_string($fancy) || !preg_match('/[a-z0-9]{0,4}/i', $fancy)) {
            throw new ProtocolConfigException('Invalid accounts fancy prefix');
        }

        $this->fancyPrefix = $fancy;
        $this->fancyPrefixLen = strlen($fancy);

        // WIF
        $wifPub = $args["wifPubPrefix"];
        if (!is_int($wifPub) || !Integers::Range($wifPub, 0, 0xffffffff)) {
            throw new ProtocolConfigException('Invalid public key WIF prefix');
        }

        $this->wifPubPrefix = $wifPub;

        $wifPrv = $args["wifPrvPrefix"];
        if (!is_int($wifPrv) || !Integers::Range($wifPrv, 0, 0xffffffff)) {
            throw new ProtocolConfigException('Invalid private key WIF prefix');
        }

        $this->wifPrvPrefix = $wifPrv;
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        if (!property_exists($this, $prop)) {
            throw new \OutOfBoundsException('Cannot read undefined property');
        }

        return $this->$prop;
    }
}
