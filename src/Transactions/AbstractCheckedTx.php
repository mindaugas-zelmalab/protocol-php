<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Accounts\ChainAccountInterface;
use ForwardBlock\Protocol\Exception\CheckTxException;
use ForwardBlock\Protocol\Exception\VerifySignaturesException;

/**
 * Class AbstractCheckedTx
 * @package ForwardBlock\Protocol\Transactions
 */
abstract class AbstractCheckedTx implements PreparedOrCheckedTx
{
    /** @var AbstractPreparedTx */
    protected AbstractPreparedTx $tx;
    /** @var AbstractTxReceipt */
    protected AbstractTxReceipt $receipt;
    /** @var int */
    protected int $totalSigns;
    /** @var int */
    protected int $requiredSigns;
    /** @var int */
    protected int $verifiedSigns;

    /**
     * AbstractCheckedTx constructor.
     * @param AbstractProtocolChain $p
     * @param ChainAccountInterface $sender
     * @param AbstractPreparedTx $tx
     * @param int $blockHeightContext
     * @throws CheckTxException
     * @throws \ForwardBlock\Protocol\Exception\TxEncodeException
     * @throws \ForwardBlock\Protocol\Exception\TxFlagException
     */
    public function __construct(AbstractProtocolChain $p, ChainAccountInterface $sender, AbstractPreparedTx $tx, int $blockHeightContext)
    {
        $this->tx = $tx;

        // Block height context
        $chainId = $p->config()->chainId;
        if ($blockHeightContext === 0) {
            $chainId = bin2hex(str_repeat("\0", 32));
        }

        // Signatures Verification
        $signatures = $tx->signatures();
        if (!$signatures) {
            throw new CheckTxException('Transaction has no signatures', CheckTxException::ERR_UNSIGNED);
        }

        $this->totalSigns = count($signatures);
        $this->requiredSigns = $p->accounts()->sigRequiredCount($sender);
        $forkIdHeightContext = $p->getForkId($blockHeightContext);

        try {
            $this->verifiedSigns = $p->accounts()->verifyAllSignatures($sender, $tx->hashPreImage($chainId, $forkIdHeightContext)->base16(), ...$signatures);
        } catch (VerifySignaturesException $e) {
            throw new CheckTxException($e->getMessage(), CheckTxException::ERR_SIGNATURES);
        }

        if ($this->requiredSigns > $this->verifiedSigns) {
            throw new CheckTxException(
                sprintf('Required %d signatures, verified %d', $this->requiredSigns, $this->verifiedSigns),
                CheckTxException::ERR_SIGNATURES
            );
        }

        // Check TxFlag status in height context
        $txFlag = $p->txFlags()->get($tx->flag());
        if (!$p->isEnabledTxFlag($txFlag, $blockHeightContext)) {
            throw new CheckTxException(
                sprintf('Transaction flag %d (%s) is disabled in block height %d context', $txFlag->id(), strtoupper($txFlag->name()), $blockHeightContext),
                CheckTxException::ERR_FLAG_DISABLED
            );
        }

        // Get Raw Receipt
        try {
            $this->receipt = $p->txFlags()->get($tx->flag())->newReceipt($tx, $blockHeightContext);
        } catch (\Exception $e) {
            if ($p->isDebug()) {
                trigger_error(sprintf('[%s][%s] %s', get_class($e), $e->getCode(), $e->getMessage()), E_USER_WARNING);
            }

            throw new CheckTxException('Failed to generate transaction raw receipt', CheckTxException::ERR_RECEIPT);
        }
    }

    /**
     * @return int
     */
    public function verifiedSignsCount(): int
    {
        return $this->verifiedSigns;
    }

    /**
     * @return int
     */
    public function requiredSignsCount(): int
    {
        return $this->requiredSigns;
    }

    /**
     * @return int
     */
    public function totalSignsCount(): int
    {
        return $this->totalSigns;
    }

    /**
     * @return AbstractPreparedTx
     */
    public function tx(): AbstractPreparedTx
    {
        return $this->tx;
    }

    /**
     * @return AbstractTxReceipt
     */
    public function rawReceipt(): AbstractTxReceipt
    {
        return $this->receipt;
    }
}
