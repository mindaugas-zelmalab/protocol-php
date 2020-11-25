<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\KeyPair;

use Comely\DataTypes\Buffer\Base16;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Exception\SignMessageException;
use ForwardBlock\Protocol\KeyPair\PrivateKey\Signature;
use FurqanSiddiqui\BIP32\Extend\ExtendedKeyInterface;

/**
 * Class PrivateKey
 * @package ForwardBlock\Protocol\KeyPair
 */
class PrivateKey extends \FurqanSiddiqui\BIP32\KeyPair\PrivateKey
{
    /** @var AbstractProtocolChain */
    protected AbstractProtocolChain $p;

    /**
     * PrivateKey constructor.
     * @param AbstractProtocolChain $p
     * @param Base16 $entropy
     * @param ExtendedKeyInterface|null $extendedKey
     */
    public function __construct(AbstractProtocolChain $p, Base16 $entropy, ?ExtendedKeyInterface $extendedKey = null)
    {
        $this->p = $p;
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
            $this->publicKey = PublicKey::fromPrivateKey($this->p, $this);
        }

        return $this->publicKey;
    }

    /**
     * @param Base16 $msgHash
     * @return Signature
     * @throws SignMessageException
     */
    public function sign(Base16 $msgHash): Signature
    {
        if ($msgHash->sizeInBytes !== 64) {
            throw new SignMessageException('This method must be used to sign 32 bytes msg hash');
        }

        try {
            $curve = $this->p->secp256k1();
            $signed = $curve->sign($this->privateKey, $msgHash);
            $v = $this->p->secp256k1()->findRecoveryId($this->publicKey()->getEllipticCurvePubKeyObj(), $signed, $msgHash, true);
            return new Signature($signed->r(), $signed->s(), $v);
        } catch (\Exception $e) {
            throw new SignMessageException(sprintf('Failed to sign message; [%s] %s', get_class($e), $e->getMessage()));
        }
    }
}
