<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\Exception\TxConstructException;
use ForwardBlock\Protocol\KeyPair\PrivateKey;
use ForwardBlock\Protocol\KeyPair\PrivateKey\Signature;
use ForwardBlock\Protocol\KeyPair\PublicKey;
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
     * @param AbstractProtocolChain $p
     * @param int $ver
     * @param AbstractTxFlag $flag
     */
    protected function __construct(AbstractProtocolChain $p, int $ver, AbstractTxFlag $flag)
    {
        parent::__construct($p);
        $this->version = $ver;
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

    public function serialize(bool $includeSignatures): Binary
    {
        // Sender and Recipient Hash160
        if ($this->senderPubKey) {
            $this->sender = hex2bin($this->senderPubKey->getHash160());
        }

        if ($this->recipientPubKey) {
            $this->recipient = hex2bin($this->recipientPubKey->getHash160());
        }

        // beforeSerialize Callback
        $this->beforeSerialize();

        // Serialize Tx
        return parent::serialize($includeSignatures);
    }
}
