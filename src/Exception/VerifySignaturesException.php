<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Exception;

/**
 * Class VerifySignaturesException
 * @package ForwardBlock\Protocol\Exception
 */
class VerifySignaturesException extends ProtocolException
{
    public const REPEATED_PUB_KEY = 0x0b;
}
