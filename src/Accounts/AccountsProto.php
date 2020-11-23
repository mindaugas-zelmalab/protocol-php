<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Accounts;

use Comely\DataTypes\Buffer\Base16;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\KeyPair\PrivateKey\Signature;
use ForwardBlock\Protocol\KeyPair\PublicKey;

/**
 * Class AccountsProto
 * @package ForwardBlock\Protocol\Accounts
 */
class AccountsProto
{
    /** @var AbstractProtocolChain */
    private AbstractProtocolChain $p;

    /**
     * AccountsProto constructor.
     * @param AbstractProtocolChain $p
     */
    public function __construct(AbstractProtocolChain $p)
    {
        $this->p = $p;
    }

    /**
     * @param ChainAccountInterface $acc
     * @return int
     */
    public function sigRequiredCount(ChainAccountInterface $acc): int
    {
        switch (count($acc->getAllPublicKeys())) {
            case 5:
            case 4:
                $required = 3;
                break;
            case 3:
            case 2:
                $required = 2;
                break;
            default:
                $required = 1;
                break;
        }

        return $required;
    }

    /**
     * @param ChainAccountInterface $acc
     * @param Base16 $msgHash
     * @param Signature ...$signatures
     * @return int
     */
    public function verifyAllSignatures(ChainAccountInterface $acc, Base16 $msgHash, Signature ...$signatures): int
    {
        $secp256k1 = $this->p->secp256k1();
        $publicKeys = $acc->getAllPublicKeys();

        // Check each signature
        $verified = 0;
        foreach ($signatures as $signature) {
            /**
             * @var int $pubIn
             * @var PublicKey $pubKey
             */
            foreach ($publicKeys as $pubIn => $pubKey) {
                try {
                    $pub = $secp256k1->recoverPublicKeyFromSignature($signature, $msgHash, $signature->v());
                    if ($pub->getCompressed()->hexits(false) === $pubKey->compressed()->hexits(false)) {
                        $verified++;
                    }
                } catch (\Exception $e) {
                }
            }
        }

        return $verified;
    }
}
