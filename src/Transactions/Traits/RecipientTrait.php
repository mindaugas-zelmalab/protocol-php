<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions\Traits;

use ForwardBlock\Protocol\KeyPair\PublicKey;

/**
 * Trait RecipientTrait
 * @package ForwardBlock\Protocol\Transactions\Traits
 */
trait RecipientTrait
{
    /**
     * @param PublicKey $recipient
     * @return $this
     */
    public function sendToRecipient(PublicKey $recipient): self
    {
        if (isset($this->recipientPubKey)) {
            $this->recipientPubKey = $recipient;
        }

        return $this;
    }
}
