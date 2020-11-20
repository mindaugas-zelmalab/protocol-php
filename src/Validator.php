<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol;

use Comely\DataTypes\Buffer\Base16;
use Comely\DataTypes\Buffer\Binary;

/**
 * Class Validator
 * @package ForwardBlock\Protocol
 */
class Validator
{
    /**
     * @param $pubKey
     * @param bool|null $compressed
     */
    public static function checkPublicKey($pubKey, ?bool $compressed = null): void
    {
        $pubKeyStr = null;
        if ($pubKey instanceof Base16) {
            $pubKeyStr = $pubKey->binary()->value();
        } elseif ($pubKey instanceof Binary) {
            $pubKeyStr = $pubKey->value();
        } elseif (is_string($pubKey)) {
            $pubKeyStr = $pubKey;
        }

        if (!$pubKeyStr) {
            throw new \InvalidArgumentException(sprintf('Invalid public key arg type, got "%s"', gettype($pubKeyStr)));
        }

        $prefixes = ["\x02", "\x03", "\x04"];
        $length = [33, 65];
        $type = null;
        if (is_bool($compressed)) {
            $prefixes = $compressed ? ["\x02", "\x03"] : ["\x04"];
            $length = $compressed ? [33] : [65];
            $type = $compressed ? "compressed " : "uncompressed ";
        }

        $pubKeyLen = strlen($pubKeyStr);
        if (!in_array($pubKeyLen, $length)) {
            throw new \LengthException(sprintf('Invalid %spublic key length, got %d bytes', $type, $pubKeyLen));
        }

        if (!in_array($pubKeyStr[0], $prefixes)) {
            throw new \DomainException(sprintf('Invalid %spublic key prefix', $type));
        }
    }

    /**
     * @param $str
     * @param int $size
     * @return bool
     */
    public static function isBase16Int($str, int $size = 1): bool
    {
        if (is_string($str)) {
            if (preg_match('/^[a-f0-9]{' . $size * 2 . '}$/i', $str)) {
                return true;
            }
        }

        return false;
    }
}
