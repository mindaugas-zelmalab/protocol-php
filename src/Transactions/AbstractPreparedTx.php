<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Base16;
use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\Exception\TxDecodeException;
use ForwardBlock\Protocol\KeyPair\PrivateKey\Signature;
use ForwardBlock\Protocol\Math\UInts;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Validator;

/**
 * Class AbstractPreparedTx
 * @package ForwardBlock\Protocol\Transactions
 */
abstract class AbstractPreparedTx extends AbstractTx
{
    /** @var Binary */
    protected Binary $hash;
    /** @var Binary */
    protected Binary $raw;

    /**
     * @param AbstractProtocolChain $p
     * @param Binary $encoded
     * @return static
     * @throws TxDecodeException
     */
    public static function Decode(AbstractProtocolChain $p, Binary $encoded): self
    {
        return new static($p, $encoded);
    }

    /**
     * Transaction constructor.
     * @param AbstractProtocolChain $p
     * @param Binary $bytes
     * @throws TxDecodeException
     */
    protected function __construct(AbstractProtocolChain $p, Binary $bytes)
    {
        parent::__construct($p);
        $this->raw = $bytes->readOnly(true);

        if ($bytes->sizeInBytes > AbstractProtocolChain::MAX_TRANSACTION_SIZE) {
            throw new TxDecodeException(sprintf(
                'Encoded transaction of %d bytes exceeds limit of %d bytes per transaction',
                $bytes->sizeInBytes,
                AbstractProtocolChain::MAX_TRANSACTION_SIZE
            ));
        }

        // Get Transaction Hash
        $this->hash = $this->p->hash256($bytes)->readOnly(true);

        // Byte Reading
        $read = $bytes->read();
        $read->throwUnderflowEx();

        // Step 1
        $this->version = UInts::Decode_UInt1LE($read->first(1));
        switch ($this->version) {
            case 1:
                $this->decodeTxV1($read);
                break;
            default:
                throw new TxDecodeException(sprintf('Unsupported transaction version %d', $this->version));
        }

        // Decode callback
        $this->decodeCallback();
    }

    abstract protected function decodeCallback(): void;

    /**
     * @param Binary\ByteReader $read
     * @throws TxDecodeException
     */
    private function decodeTxV1(Binary\ByteReader $read): void
    {
        // Step 2
        $flag = UInts::Decode_UInt2LE($read->next(2));
        $this->flag = $flag;

        // Step 3
        $hasSender = $read->next(1);
        if ($hasSender === "\1") {
            $this->sender = $read->next(20);
        } elseif ($hasSender !== "\0") {
            throw TxDecodeException::Incomplete($this, 'Invalid "hasSender" flag byte');
        }

        // Step 4
        $nonce = UInts::Decode_UInt4LE($read->next(4));
        if ($nonce && !$this->sender) {
            throw TxDecodeException::Incomplete($this, 'Transaction has positive nonce but no sender');
        }

        $this->nonce = $nonce;

        // Step 5
        $hasRecipient = $read->next(1);
        if ($hasRecipient === "\1") {
            $this->recipient = $read->next(20);
        } elseif ($hasRecipient !== "\0") {
            throw TxDecodeException::Incomplete($this, 'Invalid "hasRecipient" flag byte');
        }

        // Step 6
        $memoLen = UInts::Decode_UInt1LE($read->next(1));
        if ($memoLen > 0) {
            if ($memoLen > AbstractProtocolChain::MAX_TX_MEMO_LEN) {
                throw TxDecodeException::Incomplete($this,
                    sprintf('Transaction memo of %d bytes exceeds max size of %d bytes', $memoLen, AbstractProtocolChain::MAX_TX_MEMO_LEN)
                );
            }

            try {
                $this->memo = Validator::validatedMemo($read->next($memoLen));
            } catch (\Exception $e) {
                throw TxDecodeException::Incomplete($this,
                    sprintf('Invalid memo (%s) %s', get_class($e), $e->getMessage())
                );
            }
        }

        // Step 7
        $transfers = UInts::Decode_UInt1LE($read->next(1));
        if ($transfers > AbstractProtocolChain::MAX_TRANSFERS_PER_TX) {
            throw TxDecodeException::Incomplete($this,
                sprintf('Transaction cannot contain more than %d transfers', AbstractProtocolChain::MAX_TRANSFERS_PER_TX)
            );
        }

        if ($transfers) {
            if (!$this->recipient) {
                throw TxDecodeException::Incomplete($this, 'Transaction has transfer(s) with no recipient');
            }

            for ($i = 0; $i < $transfers; $i++) {
                $amount = UInts::Decode_UInt8LE($read->next(8));
                if ($amount > UInts::MAX) {
                    throw TxDecodeException::Incomplete($this, sprintf('Transfer amount overflow at index %d', $i));
                }

                $hasAsset = $read->next(1);
                if ($hasAsset === "\1") {
                    $assetId = ltrim($read->next(8));
                    if (!Validator::isValidAssetId($assetId)) {
                        throw TxDecodeException::Incomplete($this, sprintf('Invalid transfer asset ID at index %d', $i));
                    }
                } elseif ($hasAsset !== "\0") {
                    throw TxDecodeException::Incomplete($this, sprintf('Invalid transfer.hasAsset flag at transfer index %d', $i));
                }

                $this->transfers[$assetId ?? null] = $amount;
            }
        }

        // Step 8
        $dataLen = UInts::Decode_UInt2LE($read->next(2));
        if ($dataLen > 0) {
            if ($dataLen > AbstractProtocolChain::MAX_ARBITRARY_DATA) {
                throw TxDecodeException::Incomplete($this, sprintf(
                    'Arbitrary data of %d bytes exceeds limit of %d bytes',
                    $dataLen,
                    AbstractProtocolChain::MAX_ARBITRARY_DATA
                ));
            }

            $this->data = (new Binary($read->next($dataLen)))->readOnly(true);
        }

        // Step 9
        $signs = UInts::Decode_UInt1LE($read->next(1));
        if ($signs > 5) {
            throw TxDecodeException::Incomplete($this, 'Transaction cannot have more than 5 signatures');
        }

        if ($signs > 0) {
            for ($i = 1; $i <= $signs; $i++) {
                try {
                    $signR = $read->next(32);
                    $signS = $read->next(32);
                    $signV = UInts::Decode_UInt1LE($read->next(1));
                    $sign = new Signature(new Base16(bin2hex($signR)), new Base16(bin2hex($signS)), $signV);
                } catch (\Exception $e) {
                    throw TxDecodeException::Incomplete($this, sprintf('Error with signature %d; (%s) %s', $i, get_class($e), $e->getMessage()));
                }

                $this->signs[] = $sign;
            }
        }

        // Step 10
        $this->fee = UInts::Decode_UInt8LE($read->next(8));

        // Step 11
        $this->timeStamp = UInts::Decode_UInt4LE($read->next(4));
        if (!Validator::isValidEpoch($this->timeStamp)) {
            throw TxDecodeException::Incomplete($this, 'Invalid timestamp');
        }

        // Check remaining bytes?
        if ($read->remaining()) {
            throw TxDecodeException::Incomplete($this, 'Transaction byte reader has excess bytes');
        }
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->array();
    }

    /**
     * Returns Array (partial/incomplete if on error too)
     * @return array
     */
    public function array(): array
    {
        $partialTx = [];
        // Base props
        $props = [
            "version",
            "flag",
            "sender",
            "nonce",
            "recipient",
            "memo",
            "transfers",
            "fee",
            "timeStamp"
        ];

        foreach ($props as $prop) {
            if (isset($this->$prop)) {
                $partialTx[$prop] = $this->$prop;
            }
        }

        // Data
        if (isset($this->data)) {
            $partialTx["data"] = $this->data->base16()->hexits(false);
        }

        // Signatures
        if (isset($this->signs) && $this->signs) {
            $partialTx["signs"] = [];

            /** @var Signature $sign */
            foreach ($this->signs as $sign) {
                $partialTx["signs"][] = $sign->array();
            }
        }

        return $partialTx;
    }

    /**
     * @return Binary
     */
    public function hash(): Binary
    {
        return $this->hash;
    }

    /**
     * @return Binary
     */
    public function raw(): Binary
    {
        return $this->raw;
    }

    /**
     * @return int
     */
    public function version(): int
    {
        return $this->version;
    }

    /**
     * @return int
     */
    public function flag(): int
    {
        return $this->flag;
    }

    /**
     * @return string|null
     */
    public function sender(): ?string
    {
        return $this->sender;
    }

    /**
     * @return int
     */
    public function nonce(): int
    {
        return $this->nonce;
    }

    /**
     * @return string|null
     */
    public function recipient(): ?string
    {
        return $this->recipient;
    }

    /**
     * @return string|null
     */
    public function memo(): ?string
    {
        return $this->memo;
    }

    /**
     * @return array
     */
    public function transfers(): array
    {
        return $this->transfers;
    }

    /**
     * @return string
     */
    public function data(): string
    {
        return $this->data->raw();
    }

    /**
     * @return array
     */
    public function signatures(): array
    {
        return $this->signs;
    }

    /**
     * @return int
     */
    public function fee(): int
    {
        return $this->fee;
    }

    /**
     * @return int
     */
    public function timeStamp(): int
    {
        return $this->timeStamp;
    }
}
