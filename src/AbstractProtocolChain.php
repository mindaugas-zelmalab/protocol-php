<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol;

use Comely\DataTypes\Buffer\Binary;
use Comely\Utils\OOP\OOP;
use ForwardBlock\Protocol\Accounts\AccountsProto;
use ForwardBlock\Protocol\Exception\ProtocolConfigException;
use ForwardBlock\Protocol\KeyPair\KeyPairFactory;
use ForwardBlock\Protocol\KeyPair\PrivateKey\Signature;
use ForwardBlock\Protocol\Math\UInts;
use ForwardBlock\Protocol\Transactions\AbstractTxFlag;
use ForwardBlock\Protocol\Transactions\TxFlags;
use FurqanSiddiqui\ECDSA\Curves\Secp256k1;

/**
 * Class AbstractProtocolChain
 * @package ForwardBlock\Protocol
 */
abstract class AbstractProtocolChain implements ProtocolConstants
{
    /** @var string */
    protected const PROTOCOL_VERSION = "0.0.1";

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
    /** @var int|null */
    private ?int $versionId;

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
    abstract public function isEnabledTxFlag(AbstractTxFlag $f, int $blockHeightContext): bool;

    /**
     * @param int $blockHeightContext
     * @return int
     */
    abstract public function getForkId(int $blockHeightContext): int;

    /**
     * @return string
     */
    public function chainName(): string
    {
        return OOP::baseClassName(get_called_class());
    }

    /**
     * @return string
     */
    public function version(): string
    {
        return static::PROTOCOL_VERSION;
    }

    /**
     * @return int
     */
    public function versionId(): int
    {
        if (isset($this->versionId)) {
            return $this->versionId;
        }

        preg_match_all('/[0-9]+/', $this->version(), $matches);
        $matches = $matches[0];
        $v1 = $matches[0] ?? 0;
        $v2 = $matches[1] ?? 0;
        $v3 = $matches[2] ?? 0;

        $this->versionId = $v1 * 10000 + $v2 * 100 + $v3;
        return $this->versionId;
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
     * @param \Throwable $t
     */
    public function debugError(\Throwable $t): void
    {
        if ($this->debug) {
            trigger_error(sprintf('[%s][#%s] %s', get_class($t), $t->getCode(), $t->getMessage()));
        }
    }

    /**
     * @param Binary $bin
     * @return Binary
     */
    public function hash256(Binary $bin): Binary
    {
        return $bin->hash()->digest("sha256", 2);
    }

    /**
     * @param Signature ...$signs
     * @return Binary
     */
    public function serializeSignatures(Signature ...$signs): Binary
    {
        $ser = new Binary();
        foreach ($signs as $sign) {
            $ser->append($sign->r()->binary()->raw());
            $ser->append($sign->s()->binary()->raw());
            $ser->append(UInts::Encode_UInt1LE($sign->v()));
        }

        return $ser;
    }
}
