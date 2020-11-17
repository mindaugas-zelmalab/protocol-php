<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Math;

use ForwardBlock\Protocol\Validator;

/**
 * Class UInts
 * @package ForwardBlock\Protocol\Math
 */
class UInts
{
    /** @var int buffer of 512 from PHP_INT_MIN */
    public const MIN = PHP_INT_MIN + 512;
    /** @var int buffer of 512 from PHP_INT_MAX */
    public const MAX = PHP_INT_MAX - 512;

    /**
     * @param $str
     * @param int $size
     * @return string
     */
    public static function Hex2BinInt($str, int $size = 1): string
    {
        if (!Validator::isBase16Int($str, $size)) {
            throw new \InvalidArgumentException(sprintf('Bad %d byte uint in base16 form', $size));
        }

        return hex2bin($str);
    }

    /**
     * @param int $dec
     * @return string
     */
    public static function Encode_UInt1LE(int $dec): string
    {
        if ($dec > 0xff) {
            throw new \OverflowException(sprintf('Cannot encode %d as UIntLE', $dec));
        }

        return hex2bin(str_pad(dechex($dec), 2, "0", STR_PAD_LEFT));
    }

    /**
     * @param string $bin
     * @return int
     */
    public static function Decode_UInt1LE(string $bin): int
    {
        return hexdec(bin2hex($bin));
    }

    /**
     * @param int $dec
     * @return string
     */
    public static function Encode_UInt2LE(int $dec): string
    {
        if ($dec > 0xffff) {
            throw new \OverflowException(sprintf('Cannot encode %d as UInt2LE', $dec));
        }

        return pack("v", $dec);
    }

    /**
     * @param string $bin
     * @return int
     */
    public static function Decode_UInt2LE(string $bin): int
    {
        return unpack("v", $bin)[1];
    }

    /**
     * @param int $dec
     * @return string
     */
    public static function Encode_UInt4LE(int $dec): string
    {
        if ($dec > 0xffffffff) {
            throw new \OverflowException(sprintf('Cannot encode %d as UInt2LE', $dec));
        }

        return pack("V", $dec);
    }

    /**
     * @param string $bin
     * @return int
     */
    public static function Decode_UInt4LE(string $bin): int
    {
        return unpack("V", $bin)[1];
    }

    /**
     * @param int $dec
     * @return string
     */
    public static function Encode_UInt8LE(int $dec): string
    {
        if ($dec > (PHP_INT_MAX - 1)) {
            throw new \OverflowException(sprintf('Cannot encode %d as UInt8LE', $dec));
        }

        return pack("P", $dec);
    }

    /**
     * @param string $bin
     * @return int
     */
    public static function Decode_UInt8LE(string $bin): int
    {
        return unpack("P", $bin)[1];
    }

    /**
     * @param int $n
     * @param int $bytes
     * @return bool
     */
    public static function isValidUint(int $n, int $bytes): bool
    {
        switch ($bytes) {
            case 1:
                return $n >= 0 && $n <= 0xff;
            case 2:
                return $n >= 0 && $n <= 0xffff;
            case 4:
                return $n >= 0 && $n <= 0xffffffff;
            case 8:
                return $n > 0 && $n <= PHP_INT_MAX;
            default:
                throw new \InvalidArgumentException('Invalid integer size argument');
        }
    }
}
