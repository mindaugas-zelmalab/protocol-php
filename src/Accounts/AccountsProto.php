<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Accounts;

use Comely\DataTypes\Buffer\Base16;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Base58Check;
use ForwardBlock\Protocol\Exception\VerifySignaturesException;
use ForwardBlock\Protocol\KeyPair\PrivateKey\Signature;
use ForwardBlock\Protocol\KeyPair\PublicKey;
use ForwardBlock\Protocol\Math\UInts;

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
     * @throws VerifySignaturesException
     */
    public function verifyAllSignatures(ChainAccountInterface $acc, Base16 $msgHash, Signature ...$signatures): int
    {
        $secp256k1 = $this->p->secp256k1();
        $publicKeys = $acc->getAllPublicKeys();
        $verifiedPubKeys = [];

        // Check each signature
        $verified = 0;
        foreach ($signatures as $signature) {
            /**
             * @var int $pubIn
             * @var PublicKey $pubKey
             */
            foreach ($publicKeys as $pubIn => $pubKey) {
                try {
                    $pubKeyCompressed = strtolower($pubKey->compressed()->hexits(false));
                    $pub = $secp256k1->recoverPublicKeyFromSignature($signature, $msgHash, $signature->v());
                    if ($pub->getCompressed()->hexits(false) === $pubKeyCompressed) {
                        if (in_array($pubKeyCompressed, $verifiedPubKeys)) {
                            throw new VerifySignaturesException(
                                'Repeating public key in verified signatures',
                                VerifySignaturesException::REPEATED_PUB_KEY
                            );
                        }

                        $verifiedPubKeys[] = $pubKeyCompressed;
                        $verified++;
                        break;
                    }
                } catch (VerifySignaturesException $e) {
                    throw $e;
                } catch (\Exception $e) {
                }
            }
        }

        return $verified;
    }

    /**
     * @param string $hash160
     * @param int|null $prefix
     * @param string|null $fancyPrefix
     * @return string
     */
    public function hash160ToAddress(string $hash160, ?int $prefix = null, ?string $fancyPrefix = null): string
    {
        if (!preg_match('/^[a-f0-9]{40}$/i', $hash160)) {
            throw new \InvalidArgumentException('Invalid hash160 value');
        }

        $protocolConfig = $this->p->config();
        if (!is_int($prefix) || $prefix < 0) {
            $prefix = $protocolConfig->accountsPrefix;
        }

        if ($prefix > 0) {
            $hash160 = bin2hex(UInts::Encode_UInt1LE($prefix)) . $hash160;
        }

        $base58Check = Base58Check::getInstance();
        $addr = $base58Check->encode($hash160)->value();

        if (!$fancyPrefix) {
            $fancyPrefix = $protocolConfig->fancyPrefix;
        }

        if ($fancyPrefix) {
            $addr = $protocolConfig->fancyPrefix . $addr;
        }

        return $addr;
    }
}
