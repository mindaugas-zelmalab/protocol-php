<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\KeyPair;

use Comely\DataTypes\Buffer\Base16;
use Comely\DataTypes\DataTypes;
use FurqanSiddiqui\Base58\Base58Check;
use FurqanSiddiqui\Base58\Result\Base58Encoded;

/**
 * Class WIF
 * @package ForwardBlock\Protocol\KeyPair
 */
class WIF
{
    /**
     * @param int $networkPrefix
     * @param string $privateKey
     * @param bool $isCompressed
     * @return Base58Encoded
     */
    public static function Encode(int $networkPrefix, string $privateKey, bool $isCompressed = true): Base58Encoded
    {
        if (!DataTypes::isBase16($privateKey)) {
            throw new \InvalidArgumentException('Private key must be a hexadecimal string');
        }

        // If the private key will correspond to a compressed public key...
        if ($isCompressed) {
            $privateKey .= "01"; // append 0x01 byte
        }

        $raw = dechex($networkPrefix) . $privateKey;
        return (new Base58Check())->encode($raw);
    }

    /**
     * @param int $networkPrefixMatch
     * @param $wif
     * @param bool $isCompressed
     * @return Base16
     */
    public static function Decode(int $networkPrefixMatch, $wif, bool $isCompressed = true): Base16
    {
        if (!$wif instanceof Base58Encoded) {
            if (!is_string($wif) || !$wif) {
                throw new \InvalidArgumentException('First argument must be a Base58Encoded buffer or a string');
            }

            $wif = new Base58Encoded($wif);
        }

        // Decode
        $privateKey = (new Base58Check())->decode($wif)->binary();
        if ($isCompressed) {
            $privateKey->substr(0, -1); // Remove last 1 byte
        }

        // Network prefix check
        if ($networkPrefixMatch) {
            $networkPrefixHex = dechex($networkPrefixMatch);
            $networkPrefixByteLen = strlen(hex2bin($networkPrefixHex));
            if (!hash_equals($privateKey->copy(0, $networkPrefixByteLen)->base16()->hexits(), $networkPrefixHex)) {
                throw new \UnexpectedValueException('Network prefix mismatch');
            }

            $privateKey->substr($networkPrefixByteLen); // Remove first N bytes (network prefix len)
        }

        return $privateKey->base16();
    }
}
