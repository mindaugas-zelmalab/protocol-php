<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\KeyPair;

use Comely\DataTypes\Buffer\Base16;
use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\Base58Check;
use ForwardBlock\Protocol\Exception\KeyPairException;
use ForwardBlock\Protocol\Math\UInts;
use ForwardBlock\Protocol\Protocol;
use FurqanSiddiqui\BIP32\ECDSA\Curves;
use FurqanSiddiqui\BIP32\Extend\PrivateKeyInterface;
use FurqanSiddiqui\ECDSA\ECC\EllipticCurveInterface;

/**
 * Class PublicKey
 * @package ForwardBlock\Protocol\KeyPair
 */
class PublicKey extends \FurqanSiddiqui\BIP32\KeyPair\PublicKey
{
    /** @var Protocol */
    private Protocol $protocol;
    /** @var string|null */
    private ?string $hash160 = null;
    /** @var string|null */
    private ?string $address = null;

    /**
     * PublicKey constructor.
     * @param Protocol $protocol
     * @param PrivateKeyInterface|null $privateKey
     * @param EllipticCurveInterface|null $curve
     * @param Base16|null $publicKey
     * @param bool|null $pubKeyArgIsCompressed
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     */
    public function __construct(Protocol $protocol, ?PrivateKeyInterface $privateKey, ?EllipticCurveInterface $curve = null, ?Base16 $publicKey = null, ?bool $pubKeyArgIsCompressed = null)
    {
        $this->protocol = $protocol;
        parent::__construct($privateKey, $curve, $publicKey, $pubKeyArgIsCompressed);
    }


    /**
     * @param Protocol $protocol
     * @param PrivateKey $pK
     * @return static
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     */
    public static function fromPrivateKey(Protocol $protocol, PrivateKey $pK): self
    {
        return new self($protocol, $pK);
    }

    /**
     * @param Protocol $protocol
     * @param Binary $pub
     * @return static
     * @throws KeyPairException
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     */
    public static function fromPublicKey(Protocol $protocol, Binary $pub): self
    {
        $prefix = $pub->value(0, 1);
        if (!in_array($prefix, ["\x02", "\x03", "\x04"])) {
            throw new KeyPairException('Invalid public key prefix');
        }

        $isCompressed = $prefix === "\x04" ? false : true;
        return new self($protocol, null, Curves::getInstanceOf(Protocol::ECDSA_CURVE), $pub->base16(), $isCompressed);
    }

    /**
     * @return string
     */
    public function getHash160(): string
    {
        if (!$this->hash160) {
            $hash160 = $this->compressed()->clone();
            $hash160 = $hash160->binary()->hash()->sha256()
                ->hash()->ripeMd160();

            $this->hash160 = $hash160->base16()->hexits(false);
        }

        return $this->hash160;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        if ($this->address) {
            return $this->address;
        }

        $bytes = $this->getHash160();
        $protocolConfig = $this->protocol->config();
        $prefix = $protocolConfig->accountsPrefix;
        if ($prefix > 0) {
            $bytes = bin2hex(UInts::Encode_UInt1LE($prefix)) . $bytes;
        }

        $base58Check = Base58Check::getInstance();
        $addr = $base58Check->encode($bytes)->value();
        if ($protocolConfig->fancyPrefixLen) {
            $addr = $protocolConfig->fancyPrefix . $addr;
        }

        $this->address = $addr;
        return $this->address;
    }
}
