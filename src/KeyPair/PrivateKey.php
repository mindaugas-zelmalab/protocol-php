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
            return $this->signWithValidRecId($msgHash);
        } catch (\Exception $e) {
            throw new SignMessageException(sprintf('Failed to sign message; [%s] %s', get_class($e), $e->getMessage()));
        }
    }

    /**
     * @param Base16 $msgHash
     * @param Base16|null $randK
     * @param int $maxA Maximum number of attempts
     * @return Signature
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     */
    private function signWithValidRecId(Base16 $msgHash, ?Base16 $randK = null, int $maxA = 10): Signature
    {
        for ($i = 0; $i < $maxA; $i++) {
            if ($randK && $i > 0) {
                $tweakedBits = $this->tweakRandK(gmp_strval(gmp_init($randK->hexits(false), 16), 2), $i);
                $randK = new Base16(gmp_strval(gmp_init($tweakedBits, 2), 16));
            }

            $curve = $this->p->secp256k1();
            $signed = $curve->sign($this->privateKey, $msgHash, $randK);

            try {
                $v = $this->p->secp256k1()->findRecoveryId($this->publicKey()->getEllipticCurvePubKeyObj(), $signed, $msgHash, true);
            } catch (\RuntimeException $e) {
                $randK = $signed->randK();
                continue;
            }

            return new Signature($this->nullPadded32($signed->r()), $this->nullPadded32($signed->s()), $v);
        }

        throw new \UnexpectedValueException(sprintf('Failed to find signature with valid recovery Id in %d attempts', $maxA));
    }

    /**
     * @param string $bits
     * @param int $itN Iterations count
     * @return string
     */
    private function tweakRandK(string $bits, int $itN = 0): string
    {
        for ($i = 0; $i < $itN; $i++) {
            $bits = $this->tweakRandK($bits, 0);
        }

        $pos = strrpos($bits, "0");
        $bits[$pos] = "1";
        return $bits;
    }

    /**
     * @param Base16 $value
     * @return Base16
     */
    private function nullPadded32(Base16 $value): Base16
    {
        $value = $value->binary()->raw();
        $len = strlen($value);
        return new Base16(bin2hex(str_repeat("\0", (32 - $len)) . $value));
    }
}
