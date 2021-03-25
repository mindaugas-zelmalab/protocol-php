<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\KeyPair;

use Comely\DataTypes\Buffer\Base16;
use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\Exception\KeyPairException;
use ForwardBlock\Protocol\AbstractProtocolChain;
use FurqanSiddiqui\BIP32\ECDSA\Curves;
use FurqanSiddiqui\BIP32\Extend\PrivateKeyInterface;
use FurqanSiddiqui\ECDSA\ECC\EllipticCurveInterface;

/**
 * Class PublicKey
 * @package ForwardBlock\Protocol\KeyPair
 */
class PublicKey extends \FurqanSiddiqui\BIP32\KeyPair\PublicKey
{
    /** @var AbstractProtocolChain */
    protected AbstractProtocolChain $protocol;
    /** @var string|null */
    private ?string $hash160 = null;
    /** @var string|null */
    private ?string $address = null;

    /**
     * PublicKey constructor.
     * @param AbstractProtocolChain $protocol
     * @param PrivateKeyInterface|null $privateKey
     * @param EllipticCurveInterface|null $curve
     * @param Base16|null $publicKey
     * @param bool|null $pubKeyArgIsCompressed
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     */
    public function __construct(AbstractProtocolChain $protocol, ?PrivateKeyInterface $privateKey, ?EllipticCurveInterface $curve = null, ?Base16 $publicKey = null, ?bool $pubKeyArgIsCompressed = null)
    {
        $this->protocol = $protocol;
        parent::__construct($privateKey, $curve, $publicKey, $pubKeyArgIsCompressed);
    }


    /**
     * @param AbstractProtocolChain $protocol
     * @param PrivateKey $pK
     * @return static
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     */
    public static function fromPrivateKey(AbstractProtocolChain $protocol, PrivateKey $pK): self
    {
        return new self($protocol, $pK);
    }

    /**
     * @param AbstractProtocolChain $protocol
     * @param Binary $pub
     * @return static
     * @throws KeyPairException
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     */
    public static function fromPublicKey(AbstractProtocolChain $protocol, Binary $pub): self
    {
        $prefix = $pub->value(0, 1);
        if (!in_array($prefix, ["\x02", "\x03", "\x04"])) {
            throw new KeyPairException('Invalid public key prefix');
        }

        $isCompressed = $prefix === "\x04" ? false : true;
        return new self($protocol, null, Curves::getInstanceOf(AbstractProtocolChain::ECDSA_CURVE), $pub->base16(), $isCompressed);
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

        $this->address = $this->protocol->accounts()->hash160ToAddress($this->getHash160());
        return $this->address;
    }
}
