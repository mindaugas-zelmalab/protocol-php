<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\KeyPair;

use Comely\DataTypes\Buffer\Base16;
use ForwardBlock\Protocol\Exception\KeyPairException;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Validator;

/**
 * Class KeyPairFactory
 * @package ForwardBlock\Protocol\KeyPair
 */
class KeyPairFactory
{
    /** @var AbstractProtocolChain */
    private AbstractProtocolChain $protocol;

    /**
     * KeyPairFactory constructor.
     * @param AbstractProtocolChain $protocol
     */
    public function __construct(AbstractProtocolChain $protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @param Base16 $pub
     * @return PublicKey
     * @throws KeyPairException
     */
    public function publicKeyFromEntropy(Base16 $pub): PublicKey
    {
        try {
            Validator::checkPublicKey($pub);
            return new PublicKey($this->protocol, null, $this->protocol->secp256k1(), $pub, null);
        } catch (\Exception $e) {
            throw new KeyPairException(sprintf('[%s] %s', get_class($e), $e->getMessage()));
        }
    }

    /**
     * @param Base16 $prv
     * @return PrivateKey
     * @throws KeyPairException
     */
    public function privateKeyFromEntropy(Base16 $prv): PrivateKey
    {
        if ($prv->sizeInBytes !== 64) {
            throw new KeyPairException('Private key must be a 256 bit entropy');
        }

        return new PrivateKey($this->protocol, $prv);
    }
}
