<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions\Types;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\Exception\TxConstructException;
use ForwardBlock\Protocol\KeyPair\PublicKey;
use ForwardBlock\Protocol\Math\UInts;
use ForwardBlock\Protocol\Protocol;
use ForwardBlock\Protocol\Transactions\AbstractTxConstructor;
use ForwardBlock\Protocol\Transactions\Traits\TransferObjectsTrait;
use ForwardBlock\Protocol\Transactions\TxFlags;

/**
 * Class RegisterTx
 * @package ForwardBlock\Protocol\Transactions\Types
 */
class RegisterTx extends AbstractTxConstructor
{
    /** @var PublicKey */
    private PublicKey $pubKey;
    /** @var PublicKey|null */
    private ?PublicKey $referrer = null;
    /** @var array */
    private array $multiSig = [];

    use TransferObjectsTrait;

    /**
     * RegisterTx constructor.
     * @param Protocol $protocol
     * @param PublicKey $new
     */
    public function __construct(Protocol $protocol, PublicKey $new)
    {
        parent::__construct($protocol, 1, TxFlags::REGISTER);
        $this->pubKey = $new;
    }

    /**
     * @param PublicKey $referrer
     * @return $this
     */
    public function setReferrer(PublicKey $referrer): self
    {
        $this->referrer = $referrer;
        return $this;
    }

    /**
     * @param PublicKey ...$keys
     * @return $this
     * @throws TxConstructException
     */
    public function setMultiSigKeys(PublicKey ...$keys): self
    {
        if (count($keys) > 5) {
            throw TxConstructException::Prop("account.multiSig", "Cannot add more than 5 public keys");
        }

        $this->multiSig = $keys;
        return $this;
    }

    /**
     * @return void
     */
    protected function beforeSerialize(): void
    {
        $data = new Binary();

        // Append new account's public key
        $data->append($this->pubKey->compressed()->binary());

        // Append referrer's public key
        $referrer = $this->referrer ?? $this->sender;
        if ($referrer) {
            $data->append($referrer->compressed()->binary());
        } else {
            $data->append(str_repeat("\0", 33));
        }

        // MultiSig?
        $multiSigCount = count($this->multiSig);
        $data->append(UInts::Encode_UInt1LE($multiSigCount));

        if ($multiSigCount) {
            /** @var PublicKey $pubKey */
            foreach ($this->multiSig as $pubKey) {
                $data->append($pubKey->compressed()->binary()->raw());
            }
        }

        $this->data = $data->readOnly(true);
    }
}
