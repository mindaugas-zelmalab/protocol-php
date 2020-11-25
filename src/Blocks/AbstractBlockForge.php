<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Blocks;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Exception\BlockEncodeException;
use ForwardBlock\Protocol\Exception\BlockForgeException;
use ForwardBlock\Protocol\KeyPair\PrivateKey\Signature;
use ForwardBlock\Protocol\KeyPair\PublicKey;
use ForwardBlock\Protocol\Transactions\AbstractCheckedTx;
use ForwardBlock\Protocol\Validator;

/**
 * Class AbstractBlockForge
 * @package ForwardBlock\Protocol\Blocks
 */
abstract class AbstractBlockForge extends AbstractBlock
{
    /** @var PublicKey|null */
    protected ?PublicKey $forgerPubKey = null;
    /** @var array */
    protected array $bodyTxs = [];

    /**
     * BlockForge constructor.
     * @param AbstractProtocolChain $p
     * @param string $prevBlock
     * @param int $ver
     * @param int $epoch
     * @throws BlockForgeException
     */
    public function __construct(AbstractProtocolChain $p, string $prevBlock, int $ver, int $epoch)
    {
        parent::__construct($p);
        if (strlen($prevBlock) !== 32) {
            throw new BlockForgeException('PrevBlockHash must be precisely 32 bytes');
        }

        if ($p < 0 || $p > 0xff) {
            throw new BlockForgeException('Invalid block version');
        }

        if (!Validator::isValidEpoch($epoch)) {
            throw new BlockForgeException('Invalid time stamp');
        }

        $this->prevBlockId = $prevBlock;
        $this->version = $ver;
        $this->timeStamp = $epoch;

        // onConstructCallback for i.e. first default/reward transaction
        $this->onConstructCallback();
    }

    /**
     * Callback method ideally to create first default/reward transaction
     */
    abstract public function onConstructCallback(): void;

    /**
     * @param PublicKey $publicKey
     * @return $this
     */
    public function forger(PublicKey $publicKey): self
    {
        $this->forgerPubKey = $publicKey;
        return $this;
    }

    /**
     * @param Signature $sign
     * @return $this
     * @throws BlockForgeException
     */
    public function addSignature(Signature $sign): self
    {
        if (count($this->signs) >= 5) {
            throw new BlockForgeException('Cannot add more then 5 signatures');
        }

        $this->signs[] = $sign;
        return $this;
    }

    /**
     * @param AbstractCheckedTx $tx
     * @throws BlockForgeException
     */
    public function appendTx(AbstractCheckedTx $tx): void
    {
        if ($tx->rawReceipt()->isFinalised()) {
            throw new BlockForgeException('Tx with final receipt cannot be appended');
        }

        $this->bodyTxs[] = $tx;
    }

    /**
     * @return void
     */
    abstract protected function generateFinalReceipts(): void;

    /**
     * @param bool $includeSignatures
     * @return Binary
     * @throws \ForwardBlock\Protocol\Exception\BlockEncodeException
     */
    public function serialize(bool $includeSignatures): Binary
    {
        if (!$this->forgerPubKey) {
            throw new BlockEncodeException('Block forger public key is required');
        }

        $this->forger = hex2bin($this->forgerPubKey->getHash160());

        // Final receipts
        $this->generateFinalReceipts();

        // Merkle
        $this->merkleTx = $this->txs->merkleRoot()->raw();
        $this->merkleTxReceipts = $this->txsReceipts->merkleRoot()->raw();
        return parent::serialize($includeSignatures);
    }
}
