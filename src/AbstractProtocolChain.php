<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol;

use Comely\DataTypes\Buffer\Binary;
use Comely\Utils\OOP\OOP;
use ForwardBlock\Protocol\Accounts\AccountsProto;
use ForwardBlock\Protocol\Exception\ProtocolConfigException;
use ForwardBlock\Protocol\KeyPair\KeyPairFactory;
use ForwardBlock\Protocol\Transactions\AbstractTxFlag;
use ForwardBlock\Protocol\Transactions\TxFlags;
use FurqanSiddiqui\ECDSA\Curves\Secp256k1;

/**
 * Class AbstractProtocolChain
 * @package ForwardBlock\Protocol
 */
abstract class AbstractProtocolChain implements ProtocolConstants
{
    /** @var bool */
    protected bool $debug = false;
    /** @var Config */
    protected Config $config;
    /** @var KeyPairFactory */
    protected KeyPairFactory $kpF;
    /** @var TxFlags */
    protected TxFlags $txFlags;
    /** @var AccountsProto */
    protected AccountsProto $aP;

    /**
     * Protocol constructor.
     * @param array $config
     * @throws ProtocolConfigException
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
        $this->kpF = new KeyPairFactory($this);
        $this->txFlags = new TxFlags($this);
        $this->aP = new AccountsProto($this);

        // Register TX flags
        $this->registerTxFlags($this->txFlags);
    }

    /**
     * @param TxFlags $flags
     */
    abstract protected function registerTxFlags(TxFlags $flags): void;

    /**
     * @param AbstractTxFlag $f
     * @param int $blockHeightContext
     * @return bool
     */
    abstract protected function isEnabledTxFlag(AbstractTxFlag $f, int $blockHeightContext): bool;

    /**
     * @return string
     */
    public function chainName(): string
    {
        return OOP::baseClassName(get_called_class());
    }

    /**
     * @return TxFlags
     */
    public function txFlags(): TxFlags
    {
        return $this->txFlags;
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
     * @return AccountsProto
     */
    public function accounts(): AccountsProto
    {
        return $this->aP;
    }

    /**
     * @param bool $debug
     * @return $this
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param Binary $bin
     * @return Binary
     */
    public function hash256(Binary $bin): Binary
    {
        return $bin->hash()->digest("sha256", 2);
    }
}
