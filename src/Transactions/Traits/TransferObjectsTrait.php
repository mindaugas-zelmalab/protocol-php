<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions\Traits;

use ForwardBlock\Protocol\Exception\TxConstructException;
use ForwardBlock\Protocol\Math\UInts;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Validator;

/**
 * Trait TransferObjectsTrait
 * @package ForwardBlock\Protocol\Transactions\Traits
 */
trait TransferObjectsTrait
{
    /**
     * @param int $value
     * @param string|null $assetId
     * @return $this
     * @throws TxConstructException
     */
    public function addTransfer(int $value, ?string $assetId = null): self
    {
        if ($assetId) {
            if (!Validator::isValidAssetId($assetId)) {
                throw TxConstructException::Prop("transferObject.assetId", "Invalid asset identifier");
            }

            $reqPad = 8 - strlen($assetId);
            if ($reqPad > 0) {
                $assetId = str_pad($assetId, 8, "\0", STR_PAD_LEFT);
            }
        }

        if ($value < 0 || $value > UInts::MAX) {
            throw TxConstructException::Prop("transferObject.value", "Invalid transfer amount");
        }

        if (count($this->transfers) >= AbstractProtocolChain::MAX_TRANSFERS_PER_TX) {
            throw new TxConstructException('Transaction cannot have more then 10 asset transfers');
        }

        $this->transfers[$assetId] = $value;
        return $this;
    }
}
