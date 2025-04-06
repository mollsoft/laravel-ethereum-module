<?php

namespace Mollsoft\LaravelEthereumModule\Api\Node\DTO;

use Brick\Math\BigDecimal;
use Brick\Math\BigInteger;
use Mollsoft\LaravelEthereumModule\Api\BaseDTO;

class PreviewTransferDTO extends BaseDTO
{
    public function gasPrice(): BigDecimal
    {
        $value = $this->getOrFail('gas_price');
        $value = ltrim($value, '0x');
        $value = BigInteger::fromBase($value, 16);

        return BigDecimal::ofUnscaledValue($value);
    }

    public function gasLimit(): BigDecimal
    {
        $value = $this->getOrFail('gas_limit');
        $value = ltrim($value, '0x');
        $value = BigInteger::fromBase($value, 16);

        return BigDecimal::ofUnscaledValue($value);
    }

    public function fee(): BigDecimal
    {
        return $this
            ->gasPrice()
            ->multipliedBy($this->gasLimit())
            ->dividedBy(pow(10, 18), 18);
    }

    public function toArray(): array
    {
        return [
            'gas_price' => $this->gasPrice()->__toString(),
            'gas_limit' => $this->gasLimit()->__toString(),
            'fee' => $this->fee()->__toString(),
        ];
    }
}