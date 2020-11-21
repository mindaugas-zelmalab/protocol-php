<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;

/**
 * Class AbstractTx
 * @package ForwardBlock\Protocol\Transactions
 */
abstract class AbstractTx
{
    /** @var AbstractProtocolChain */
    protected AbstractProtocolChain $protocol;

    /** @var int */
    protected int $version;
    /** @var int */
    protected int $flag;
    /** @var string|null */
    protected ?string $sender = null;
    /** @var int */
    protected int $nonce = 0;
    /** @var string|null */
    protected ?string $recipient = null;
    /** @var string|null */
    protected ?string $memo = null;
    /** @var array */
    protected array $transfers = [];
    /** @var Binary|null */
    protected ?Binary $data = null;
    /** @var array */
    protected array $signs = [];
    /** @var int */
    protected int $fee = 0;
    /** @var int */
    protected int $timeStamp;
    /** @var int */
    protected int $feePerByte = 0;
}
