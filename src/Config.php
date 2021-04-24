<?php
/** @noinspection ALL */
declare(strict_types=1);

namespace ForwardBlock\Protocol;

use Comely\DataTypes\Integers;
use ForwardBlock\Protocol\Exception\ProtocolConfigException;

/**
 * Class Config
 * @package ForwardBlock\Protocol
 * @property-read string $chainId
 * @property-read int $forkId
 * @property-read int $accountsPrefix
 * @property-read string $fancyPrefix
 * @property-read int $fancyPrefixLen
 * @property-read int $wifPrefix
 */
class Config
{
    /** @var string */
    private string $chainId;
    /** @var int */
    private int $forkId;
    /** @var int */
    private int $accountsPrefix;
    /** @var string */
    private string $fancyPrefix;
    /** @var int */
    private int $fancyPrefixLen;
    /** @var int */
    private int $wifPrefix;

    /**
     * Protocol constructor.
     * @param array $args
     * @throws ProtocolConfigException
     */
    public function __construct(array $args)
    {
        // Chain ID
        $chainId = $args["chainId"];
        if (!Validator::isValidChainId($chainId)) {
            throw new ProtocolConfigException('Invalid chain identifier');
        }

        $this->chainId = $chainId;

        // Fork Id
        $forkId = $args["forkId"];
        if (!is_int($forkId) || $forkId < 0 || $forkId > 0xff) {
            throw new ProtocolConfigException('Invalid fork id');
        }

        $this->forkId = $forkId;

        // Account Prefix
        $accPrefix = $args["accountsPrefix"];
        if (!is_int($accPrefix) || $accPrefix < 0x00 || $accPrefix > 0xff) {
            throw new ProtocolConfigException('Invalid accounts prefix');
        }

        $this->accountsPrefix = $accPrefix;

        // Fancy Prefix
        $fancy = $args["fancyPrefix"];
        if (!is_string($fancy) || !preg_match('/^[a-z0-9]{0,4}$/i', $fancy)) {
            throw new ProtocolConfigException('Invalid accounts fancy prefix');
        }

        $this->fancyPrefix = $fancy;
        $this->fancyPrefixLen = strlen($fancy);

        // WIF
        $wifPrefix = $args["wifPrefix"];
        if (!is_int($wifPrefix) || !Integers::Range($wifPrefix, 0, 0xff)) {
            throw new ProtocolConfigException('Invalid WIF prefix');
        }

        $this->wifPrefix = $wifPrefix;
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
