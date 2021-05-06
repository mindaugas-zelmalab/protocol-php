<?php
declare(strict_types=1);

namespace ForwardBlock\Protocol\Transactions\Traits;

use Comely\DataTypes\Buffer\Binary;

/**
 * Trait CustomDataTrait
 * @package ForwardBlock\Protocol\Transactions\Traits
 */
trait CustomDataTrait
{
    /**
     * @param Binary $data
     * @return $this
     */
    public function setData(Binary $data): self
    {
        $this->data = $data;
        return $this;
    }
}
