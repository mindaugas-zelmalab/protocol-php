<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\KeyPair\PrivateKey;

use Comely\DataTypes\Buffer\Binary;

/**
 * Class Signature
 * @package ForwardBlock\Protocol\KeyPair\PrivateKey
 */
class Signature
{
    /** @var Binary */
    private Binary $r;
    /** @var Binary */
    private Binary $s;
    /** @var int */
    private int $v;

    /**
     * Signature constructor.
     * @param Binary $r
     * @param Binary $s
     * @param int $v
     */
    public function __construct(Binary $r, Binary $s, int $v)
    {
        if ($r->sizeInBytes !== 32) {
            throw new \LengthException('Signature R must be precisely 32 bytes');
        }

        if ($s->sizeInBytes !== 32) {
            throw new \LengthException('Signature S must be precisely 32 bytes');
        }

        if ($v < 0 || $v > 0xff) {
            throw new \OutOfRangeException('Signature V is out of range');
        }

        $this->r = $r->readOnly(true);
        $this->s = $s->readOnly(true);
        $this->v = $v;
    }

    /**
     * @return Binary
     */
    public function r(): Binary
    {
        return $this->r;
    }

    /**
     * @return Binary
     */
    public function s(): Binary
    {
        return $this->s;
    }

    /**
     * @return int
     */
    public function v(): int
    {
        return $this->v;
    }
}
