<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Blocks;

use Comely\DataTypes\Buffer\Binary;
use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Exception\BlockEncodeException;
use ForwardBlock\Protocol\Exception\TxEncodeException;
use ForwardBlock\Protocol\KeyPair\PrivateKey\Signature;
use ForwardBlock\Protocol\Math\UInts;
use ForwardBlock\Protocol\Validator;

/**
 * Class AbstractBlock
 * @package ForwardBlock\Protocol\Blocks
 */
abstract class AbstractBlock
{
    /** @var AbstractProtocolChain */
    protected AbstractProtocolChain $p;

    /** @var int */
    protected int $version;
    /** @var int */
    protected int $timeStamp;
    /** @var string */
    protected string $prevBlockId;
    /** @var int */
    protected int $txCount = 0;
    /** @var int */
    protected int $totalIn = 0;
    /** @var int */
    protected int $totalOut = 0;
    /** @var int */
    protected int $totalFee = 0;
    /** @var string|null */
    protected ?string $forger = null;
    /** @var array */
    protected array $signs = [];
    /** @var int */
    protected int $reward = 0;
    /** @var string|null */
    protected ?string $merkleTx = null;
    /** @var string|null */
    protected ?string $merkleTxReceipts = null;
    /** @var int */
    protected int $bodySize;

    /** @var BlockTxs */
    protected BlockTxs $txs;
    /** @var BlockTxReceipts */
    protected BlockTxReceipts $txsReceipts;

    /**
     * AbstractBlock constructor.
     * @param AbstractProtocolChain $p
     */
    public function __construct(AbstractProtocolChain $p)
    {
        $this->p = $p;
        $this->txs = new BlockTxs($p);
        $this->txsReceipts = new BlockTxReceipts($p);
    }

    /**
     * @param string|null $chainId
     * @param int|null $forkId
     * @return Binary
     * @throws BlockEncodeException
     */
    public function hashPreImage(?string $chainId = null, ?int $forkId = null): Binary
    {
        if ($chainId) {
            if (!Validator::isValidChainId($chainId)) {
                throw new BlockEncodeException('Cannot generate hashPreImage; Invalid chain identifier');
            }
        }

        if (!$chainId) {
            $chainId = $this->p->config()->chainId;
        }

        if (is_int($forkId)) {
            if ($forkId < 0 || $forkId > 0xff) {
                throw new BlockEncodeException('Cannot generate hashPreImage; Invalid fork id');
            }
        }

        if (!is_int($forkId)) {
            $forkId = $this->p->config()->forkId;
        }

        $raw = $this->serialize(false)->copy()
            ->prepend(hex2bin($chainId))
            ->prepend(UInts::Encode_UInt1LE($forkId));

        return $this->p->hash256($raw)->readOnly(true);
    }

    /**
     * @param bool $includeSignatures
     * @return Binary
     * @throws BlockEncodeException
     */
    public function serialize(bool $includeSignatures): Binary
    {
        $ser = new Binary();

        // Version Byte
        $ser->append(UInts::Encode_UInt1LE($this->version));

        // Step 2
        $ser->append(UInts::Encode_UInt4LE($this->timeStamp));

        // Step 3
        $ser->append($this->prevBlockId);

        // Step 4
        $ser->append(UInts::Encode_UInt2LE($this->txCount));

        // Step 5
        $ser->append(UInts::Encode_UInt8LE($this->totalIn));

        // Step 6
        $ser->append(UInts::Encode_UInt8LE($this->totalOut));

        // Step 7
        $ser->append(UInts::Encode_UInt8LE($this->totalFee));

        // Step 8
        $ser->append($this->forger);

        // Step 9
        if ($includeSignatures) {
            $signsCount = count($this->signs);
            if ($signsCount > 5) {
                throw new BlockEncodeException('Blocks cannot have more than 5 signatures');
            }

            $ser->append(UInts::Encode_UInt1LE($signsCount));
            if ($signsCount) {
                /** @var Signature $signed */
                foreach ($this->signs as $signed) {
                    $ser->append(hex2bin($signed->r()->hexits(false)));
                    $ser->append(hex2bin($signed->s()->hexits(false)));
                    $ser->append(UInts::Encode_UInt1LE($signed->v()));
                }
            }
        } else {
            $ser->append("\0");
        }

        // Step 10
        $ser->append(UInts::Encode_UInt8LE($this->reward));

        // Step 11
        $ser->append($this->merkleTx);

        // Step 12
        $ser->append($this->merkleTxReceipts);

        // Work on Body
        $bodyBuffer = new Binary();
        $txsCount = $this->txs->count();
        $txRCount = $this->txsReceipts->count();
        if ($txsCount !== $txRCount) {
            throw new BlockEncodeException('Transactions and receipts count does not match');
        }

        if ($txsCount > 0) {
            // Step 15
            for ($i = 0; $i < $txsCount; $i++) {
                $serTx = $this->txs->index($i)->raw();

                try {
                    $serTxR = $this->txsReceipts->index($i)->serialize();
                } catch (TxEncodeException $e) {
                    throw new BlockEncodeException(sprintf('[TxReceipt#%d][%s] %s', $i, get_class($e), $e->getMessage()));
                }

                // Step 15.1
                $bodyBuffer->append(UInts::Encode_UInt2LE($serTx->sizeInBytes));

                // Step 15.2
                $bodyBuffer->append($serTx->raw());

                // Step 15.3
                $bodyBuffer->append(UInts::Encode_UInt2LE($serTxR->sizeInBytes));

                // Step 15.4
                $bodyBuffer->append($serTxR->raw());
            }
        }

        // Step 13
        $ser->append(UInts::Encode_UInt4LE($bodyBuffer->sizeInBytes));

        // Step 14
        $ser->append("\0");

        // Step 15
        if ($bodyBuffer->sizeInBytes > 0) {
            $ser->append($bodyBuffer->raw());
        }

        return $ser->readOnly(true);
    }
}
