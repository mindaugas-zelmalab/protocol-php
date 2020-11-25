<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions;

use ForwardBlock\Protocol\AbstractProtocolChain;
use ForwardBlock\Protocol\Accounts\ChainAccountInterface;
use ForwardBlock\Protocol\Exception\CheckTxException;

/**
 * Class AbstractCheckedTx
 * @package ForwardBlock\Protocol\Transactions
 */
abstract class AbstractCheckedTx
{
    /** @var Transaction */
    protected Transaction $tx;
    /** @var AbstractTxReceipt */
    protected AbstractTxReceipt $receipt;

    /**
     * AbstractCheckedTx constructor.
     * @param AbstractProtocolChain $p
     * @param ChainAccountInterface|null $sender
     * @param Transaction $tx
     * @param int $blockHeightContext
     * @throws CheckTxException
     * @throws \ForwardBlock\Protocol\Exception\TxEncodeException
     */
    public function __construct(AbstractProtocolChain $p, ?ChainAccountInterface $sender, Transaction $tx, int $blockHeightContext)
    {
        $this->tx = $tx;

        // Signatures Verification
        $verifiedSigns = true;
        if ($blockHeightContext === 0) {
            if ($tx->flag() !== AbstractProtocolChain::GENESIS_TX_FLAG) {
                throw new CheckTxException('Non-Genesis tx in block height context 0 must verify signatures');
            }

            $verifiedSigns = false;
        }

        if ($verifiedSigns) {
            if (!$sender) {
                throw new CheckTxException('Sender is required for all transactions');
            }

            $signatures = $tx->signatures();
            $reqSigns = $p->accounts()->sigRequiredCount($sender);
            $verifiedSigns = $p->accounts()->verifyAllSignatures($sender, $tx->hashPreImage()->base16(), ...$signatures);
            if ($reqSigns > $verifiedSigns) {
                throw new CheckTxException(
                    sprintf('Required %d signatures, verified %d', $reqSigns, $verifiedSigns),
                    CheckTxException::ERR_SIGNATURES
                );
            }
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
     * @return Transaction
     */
    public function tx(): Transaction
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
