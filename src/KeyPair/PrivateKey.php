<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\KeyPair;

use Comely\DataTypes\Buffer\Base16;
use ForwardBlock\Protocol\AbstractProtocolChain;
use FurqanSiddiqui\BIP32\Extend\ExtendedKeyInterface;

/**
 * Class PrivateKey
 * @package ForwardBlock\Protocol\KeyPair
 */
class PrivateKey extends \FurqanSiddiqui\BIP32\KeyPair\PrivateKey
{
    /** @var AbstractProtocolChain */
    private AbstractProtocolChain $protocol;

    /**
     * PrivateKey constructor.
     * @param AbstractProtocolChain $protocol
     * @param Base16 $entropy
     * @param ExtendedKeyInterface|null $extendedKey
     */
    public function __construct(AbstractProtocolChain $protocol, Base16 $entropy, ?ExtendedKeyInterface $extendedKey = null)
    {
        $this->protocol = $protocol;
        parent::__construct($entropy, $extendedKey);
        $this->set("curve", AbstractProtocolChain::ECDSA_CURVE);
    }

    /**
     * @return PublicKey
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     */
    public function publicKey(): PublicKey
    {
        if (!$this->publicKey) {
            $this->publicKey = PublicKey::fromPrivateKey($this->protocol, $this);
        }

        return $this->publicKey;
    }
}
