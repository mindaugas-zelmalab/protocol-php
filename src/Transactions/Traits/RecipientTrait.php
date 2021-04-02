<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions\Traits;

use ForwardBlock\Protocol\Accounts\ChainAccountInterface;

/**
 * Trait RecipientTrait
 * @package ForwardBlock\Protocol\Transactions\Traits
 */
trait RecipientTrait
{
    /**
     * @param ChainAccountInterface $recipient
     * @return $this
     */
    public function sendToRecipient(ChainAccountInterface $recipient): self
    {
        if (isset($this->recipientPubKey)) {
            $this->recipientPubKey = $recipient->getPublicKey();
        }

        return $this;
    }
}
