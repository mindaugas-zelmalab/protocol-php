<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\KeyPair\PrivateKey;

use Comely\DataTypes\Buffer\Base16;
use FurqanSiddiqui\ECDSA\Signature\SignatureInterface;

/**
 * Class Signature
 * @package ForwardBlock\Protocol\KeyPair\PrivateKey
 */
class Signature implements SignatureInterface
{
    /** @var Base16 */
    private Base16 $r;
    /** @var Base16 */
    private Base16 $s;
    /** @var int */
    private int $v;

    /**
     * Signature constructor.
     * @param Base16 $r
     * @param Base16 $s
     * @param int $v
     */
    public function __construct(Base16 $r, Base16 $s, int $v)
    {
        if ($r->sizeInBytes !== 64) {
            throw new \LengthException('Signature R must be precisely 32 bytes');
        }

        if ($s->sizeInBytes !== 64) {
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
     * @return Base16
     */
    public function r(): Base16
    {
        return $this->r;
    }

    /**
     * @return Base16
     */
    public function s(): Base16
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
