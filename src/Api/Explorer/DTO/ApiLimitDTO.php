<?php

namespace Mollsoft\LaravelEthereumModule\Api\Explorer\DTO;

use Mollsoft\LaravelEthereumModule\Api\BaseDTO;

class ApiLimitDTO extends BaseDTO
{
    public function used(): int
    {
        return (int)$this->getOrFail('creditsUsed');
    }

    public function available(): int
    {
        return (int)$this->getOrFail('creditsAvailable');
    }

    public function limit(): int
    {
        return (int)$this->getOrFail('creditLimit');
    }
}
