<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol;

/**
 * Class Validator
 * @package ForwardBlock\Protocol
 */
class Validator
{
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
