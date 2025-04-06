<?php

namespace Mollsoft\LaravelEthereumModule\Concerns;

use Brick\Math\BigDecimal;
use Mollsoft\LaravelEthereumModule\Api\Node\DTO\PreviewTransferDTO;
use Mollsoft\LaravelEthereumModule\Facades\Ethereum;
use Mollsoft\LaravelEthereumModule\Models\EthereumAddress;
use Mollsoft\LaravelEthereumModule\Models\EthereumToken;

trait Transfer
{
    public function previewTransfer(
        EthereumAddress $from,
        EthereumAddress|string $to,
        BigDecimal|float|int|string $amount
    ): PreviewTransferDTO {
        $node = $from->wallet->node ?? Ethereum::getNode();
        $node->increment('requests', 2);
        $api = $node->api();

        if ($to instanceof EthereumAddress) {
            $to = $to->address;
        }
        $amount = BigDecimal::of($amount);

        return $api->previewTransfer(
            from: $from->address,
            to: $to,
            amount: $amount,
        );
    }

    public function previewTokenTransfer(
        EthereumToken|string $contract,
        EthereumAddress $from,
        EthereumAddress|string $to,
        BigDecimal|float|int|string $amount
    ): PreviewTransferDTO {
        $node = $from->wallet->node ?? Ethereum::getNode();
        $node->increment('requests', 3);
        $api = $node->api();

        if ($contract instanceof EthereumToken) {
            $contract = $contract->address;
        }
        if ($to instanceof EthereumAddress) {
            $to = $to->address;
        }
        $amount = BigDecimal::of($amount);

        return $api->previewTokenTransfer(
            contract: $contract,
            from: $from->address,
            to: $to,
            amount: $amount,
        );
    }

    public function transfer(
        EthereumAddress $from,
        EthereumAddress|string $to,
        BigDecimal|float|int|string $amount,
        int $gasLimit = 25000
    ): string {
        $node = $from->wallet->node ?? Ethereum::getNode();
        $node->increment('requests', 3);
        $api = $node->api();

        if ($to instanceof EthereumAddress) {
            $to = $to->address;
        }
        $amount = BigDecimal::of($amount);

        return $api->transfer(
            from: $from->address,
            to: $to,
            privateKey: $from->private_key,
            amount: $amount,
            gasLimit: $gasLimit,
        );
    }

    public function transferToken(
        EthereumToken|string $contract,
        EthereumAddress $from,
        EthereumAddress|string $to,
        BigDecimal|float|int|string $amount,
        int $gasLimit = 50000
    ): string {
        $node = $from->wallet->node ?? Ethereum::getNode();
        $node->increment('requests', 4);
        $api = $node->api();

        if ($contract instanceof EthereumToken) {
            $contract = $contract->address;
        }
        if ($to instanceof EthereumAddress) {
            $to = $to->address;
        }
        $amount = BigDecimal::of($amount);

        return $api->transferToken(
            contract: $contract,
            from: $from->address,
            to: $to,
            privateKey: $from->private_key,
            amount: $amount,
            gasLimit: $gasLimit,
        );
    }
}