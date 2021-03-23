<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Exception\TxDecodeException;
use ForwardBlock\Protocol\Math\UInts;

/**
 * Class Transaction
 * @package ForwardBlock\Protocol\Transactions
 */
class Transaction extends AbstractPreparedTx
{
    /**
     * @param AbstractProtocolChain $p
     * @param Binary $encoded
     * @return AbstractPreparedTx
     * @throws \ForwardBlock\Protocol\Exception\TxDecodeException
     * @throws \ForwardBlock\Protocol\Exception\TxFlagException
     */
    public static function DecodeAs(AbstractProtocolChain $p, Binary $encoded): AbstractPreparedTx
    {
        $flagId = substr($encoded->raw(), 1, 2);
        if (!$flagId) {
            throw new TxDecodeException('Invalid encoded transaction bytes');
        }

        return $p->txFlags()->get(UInts::Decode_UInt2LE($flagId))->decode($encoded);
    }

    /**
     * Decode callback
     */
    public function decodeCallback(): void
    {
    }
}
