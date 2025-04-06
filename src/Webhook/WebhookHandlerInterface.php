<?php

namespace Mollsoft\LaravelEthereumModule\Webhook;

use Mollsoft\LaravelEthereumModule\Models\EthereumDeposit;

interface WebhookHandlerInterface
{
    public function handle(EthereumDeposit $deposit): void;
}