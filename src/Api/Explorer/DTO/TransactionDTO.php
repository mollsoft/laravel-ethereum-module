<?php

namespace Mollsoft\LaravelEthereumModule\Api\Explorer\DTO;

use Brick\Math\BigDecimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Mollsoft\LaravelEthereumModule\Api\BaseDTO;

class TransactionDTO extends BaseDTO
{
    public function hash(): string
    {
        return (string)$this->getOrFail('hash');
    }

    public function blockNumber(): int
    {
        return (int)$this->getOrFail('blockNumber');
    }

    public function time(): Carbon
    {
        $value = $this->getOrFail('timeStamp');

        return Date::createFromTimestampUTC($value);
    }

    public function from(): string
    {
        return (string)$this->getOrFail('from');
    }

    public function to(): string
    {
        return (string)$this->getOrFail('to');
    }

    public function amount(): BigDecimal
    {
        $value = $this->getOrFail('value');

        return BigDecimal::ofUnscaledValue($value, 18);
    }

    public function gas(): int
    {
        return (int)$this->getOrFail('gas');
    }

    public function gasUsed(): int
    {
        return (int)$this->getOrFail('gasUsed');
    }

    public function gasPrice(): BigDecimal
    {
        $value = $this->getOrFail('gasPrice');

        return BigDecimal::ofUnscaledValue($value, 18);
    }

    public function fee(): BigDecimal
    {
        $gasUsed = $this->gasUsed();
        $gasPrice = $this->getOrFail('gasPrice');

        return $gasPrice->multipliedBy($gasUsed);
    }

    public function contractAddress(): ?string
    {
        return $this->get('contractAddress');
    }

    public function confirmations(): int
    {
        return (int)$this->getOrFail('confirmations');
    }

    public function isError(): bool
    {
        return (bool)$this->getOrFail('isError');
    }
}
