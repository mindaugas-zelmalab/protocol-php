<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\Exception\TxConstructException;
use ForwardBlock\Protocol\Exception\TxEncodeException;
use ForwardBlock\Protocol\KeyPair\PrivateKey;
use ForwardBlock\Protocol\KeyPair\PrivateKey\Signature;
use ForwardBlock\Protocol\KeyPair\PublicKey;
use ForwardBlock\Protocol\Math\UInts;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Validator;

/**
 * Class AbstractTxConstructor
 * @package ForwardBlock\Protocol\Transactions
 */
abstract class AbstractTxConstructor extends AbstractTx
{
    /** @var PublicKey|null */
    protected ?PublicKey $senderPubKey = null;
    /** @var PublicKey|null */
    protected ?PublicKey $recipientPubKey = null;
    /** @var AbstractTxFlag */
    protected AbstractTxFlag $txFlag;

    /**
     * AbstractTxConstructor constructor.
     * @param AbstractProtocolChain $protocol
     * @param int $v
     * @param AbstractTxFlag $flag
     */
    protected function __construct(AbstractProtocolChain $protocol, int $v, AbstractTxFlag $flag)
    {
        $this->protocol = $protocol;
        $this->version = $v;
        $this->txFlag = $flag;
        $this->flag = $flag->id();
        $this->timeStamp = time();
    }

    /**
     * @return void
     */
    abstract protected function beforeSerialize(): void;

    /**
     * @param PublicKey $sender
     * @param int $nonce
     * @return $this
     * @throws TxConstructException
     */
    public function sender(PublicKey $sender, int $nonce): self
    {
        if ($nonce < 0 || $nonce > 0xffffffff) {
            throw TxConstructException::Prop("nonce", "Sender nonce is out of range");
        }

        $this->senderPubKey = $sender;
        $this->nonce = $nonce;
        return $this;
    }

    /**
     * @param string $memo
     * @return $this
     * @throws TxConstructException
     */
    public function memo(string $memo): self
    {
        try {
            $memo = Validator::validatedMemo($memo);
        } catch (\Exception $e) {
            throw TxConstructException::Prop("memo", $e->getMessage());
        }

        $this->memo = $memo;
        return $this;
    }

    /**
     * @param int $ts
     * @return $this
     * @throws TxConstructException
     */
    public function timeStamp(int $ts): self
    {
        if (!Validator::isValidEpoch($ts)) {
            throw TxConstructException::Prop("timeStamp", "Invalid timestamp/epoch");
        }

        $this->timeStamp = $ts;
        return $this;
    }

    /**
     * @param Signature $sign
     * @return $this
     * @throws TxConstructException
     */
    public function addSignature(Signature $sign): self
    {
        if (count($this->signs) >= 5) {
            throw TxConstructException::Prop("signs", "Cannot add more than 5 signatures");
        }

        $this->signs[] = $sign;
        return $this;
    }

    public function signTransaction(PrivateKey $pK): self
    {

    }

    /**
     * @param string|null $chainId
     * @return Binary
     * @throws TxEncodeException
     */
    public function hashPreImage(?string $chainId = null): Binary
    {
        if ($chainId) {
            if (!Validator::isValidChainId($chainId)) {
                throw new TxEncodeException('Cannot generate hashPreImage; Invalid chain identifier');
            }
        }

        if (!$chainId) {
            $chainId = $this->protocol->config()->chainId;
        }

        $raw = $this->serialize(false)->copy()
            ->prepend(hex2bin($chainId));

        return $this->protocol->hash256($raw)->readOnly(true);
    }

    /**
     * @param bool $includeSignatures
     * @return Binary
     * @throws TxEncodeException
     */
    public function serialize(bool $includeSignatures): Binary
    {
        $this->beforeSerialize(); // Callback

        // Start new Binary Buffer
        $ser = new Binary();

        // Step 1
        $ser->append(UInts::Encode_UInt1LE($this->version));

        // Step 2
        $ser->append(UInts::Encode_UInt2LE($this->flag));

        // Step 3
        if ($this->senderPubKey) {
            $ser->append("\1");
            $ser->append(hex2bin($this->senderPubKey->getHash160()));
        } else {
            $ser->append("\0");
        }

        // Step 4
        $ser->append(UInts::Encode_UInt4LE($this->nonce));

        // Step 5
        if ($this->recipientPubKey) {
            $ser->append("\1");
            $ser->append(hex2bin($this->recipientPubKey->getHash160()));
        } else {
            $ser->append("\0");
        }

        // Step 6
        if ($this->memo) {
            var_dump(bin2hex(UInts::Encode_UInt1LE(strlen($this->memo))));
            $ser->append(UInts::Encode_UInt1LE(strlen($this->memo)));
            $ser->append($this->memo);
        } else {
            $ser->append("\0");
        }

        // Step 7
        $transfersCount = count($this->transfers);
        if ($transfersCount > 10) {
            throw new TxEncodeException('Transaction cannot have more than 10 asset transfers');
        }

        $ser->append(UInts::Encode_UInt1LE($transfersCount));
        if ($transfersCount) {
            if (!$this->recipient) {
                throw new TxEncodeException('Transaction with no recipient cannot have transfers');
            }

            foreach ($this->transfers as $asset => $amount) {
                $ser->append(UInts::Encode_UInt8LE($amount));
                if ($asset) {
                    $ser->append("\1");
                    $ser->append($asset);
                } else {
                    $ser->append("\0");
                }
            }
        }

        // Step 8
        if ($this->data && $this->data->sizeInBytes) {
            if ($this->data->sizeInBytes > AbstractProtocolChain::MAX_ARBITRARY_DATA) {
                throw new TxEncodeException(sprintf(
                    'Transaction arbitrary data of size %d bytes exceeds limit of %d bytes',
                    $this->data->sizeInBytes,
                    AbstractProtocolChain::MAX_ARBITRARY_DATA
                ));
            }

            $ser->append(UInts::Encode_UInt2LE($this->data->sizeInBytes));
            $ser->append($this->data->value(0, $this->data->sizeInBytes));
        } else {
            $ser->append("\0\0");
        }

        // Step 9
        if ($includeSignatures) {
            $signsCount = count($this->signs);
            if ($signsCount > 5) {
                throw new TxEncodeException('Transaction cannot have more than 5 signatures');
            }

            $ser->append(UInts::Encode_UInt1LE($signsCount));
            if ($signsCount) {
                /** @var Signature $signed */
                foreach ($this->signs as $signed) {
                    $ser->append($signed->r());
                    $ser->append($signed->s());
                    $ser->append(UInts::Encode_UInt1LE($signed->v()));
                }
            }
        } else {
            $ser->append("\0");
        }

        // Step 10
        $ser->append(UInts::Encode_UInt8LE($this->fee));

        // Step 11
        $ser->append(UInts::Encode_UInt4LE($this->timeStamp));

        // Set Buffer into ReadOnly state
        $ser->readOnly(true);
        return $ser;
    }
}
