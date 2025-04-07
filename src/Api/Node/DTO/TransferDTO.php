<?php

namespace Mollsoft\LaravelEthereumModule\Api\Node\DTO;

use Brick\Math\BigDecimal;
use Mollsoft\LaravelEthereumModule\Api\BaseDTO;

class TransferDTO extends PreviewTransferDTO
{
    public function txid(): string
    {
        return $this->getOrFail('txid');
    }
}