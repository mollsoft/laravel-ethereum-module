<?php

namespace Mollsoft\LaravelEthereumModule\Api\Node;

use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use kornrunner\Ethereum\Token;
use kornrunner\Ethereum\Transaction;
use Mollsoft\LaravelEthereumModule\Api\Node\DTO\PreviewTransferDTO;

class NodeApi
{
    protected string $baseURL;
    protected array $tokenDecimals = [];

    public function __construct(string $baseURL)
    {
        $this->baseURL = $baseURL;
    }

    public function rpc(string $method, array $params = []): mixed
    {
        $response = Http::post($this->baseURL, [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => 1
        ]);

        $result = $response->json();

        if (isset($result['error'])) {
            throw new \Exception($result['error']['message']);
        }

        return $result['result'];
    }

    public function getBalance(string $address): BigDecimal
    {
        $balanceHex = $this->rpc('eth_getBalance', [$address, 'latest']);

        return BigDecimal::ofUnscaledValue(hexdec($balanceHex), 18);
    }

    public function getTokenName(string $contract): ?string
    {
        $hex = $this->rpc('eth_call', [
            [
                'to' => $contract,
                'data' => '0x06fdde03'
            ],
            'latest'
        ]);

        return hex2bin(substr($hex, 130));
    }

    public function getTokenSymbol(string $contract): ?string
    {
        $hex = $this->rpc('eth_call', [
            [
                'to' => $contract,
                'data' => '0x95d89b41'
            ],
            'latest'
        ]);

        return hex2bin(substr($hex, 130));
    }

    public function getTokenDecimals(string $contract): int
    {
        $hex = $this->rpc('eth_call', [
            [
                'to' => $contract,
                'data' => '0x313ce567'
            ],
            'latest'
        ]);

        return (int)hexdec($hex);
    }

    public function getBalanceOfToken(string $address, string $contract): BigDecimal
    {
        $decimals = $this->tokenDecimals[$contract] ??= $this->getTokenDecimals($contract);

        $data = '0x70a08231000000000000000000000000'.substr($address, 2);
        $balanceHex = $this->rpc('eth_call', [
            [
                'to' => $contract,
                'data' => $data
            ],
            'latest'
        ]);

        return BigDecimal::ofUnscaledValue(hexdec($balanceHex), $decimals);
    }

    public function getLatestBlockNumber(): int
    {
        $hex = $this->rpc('eth_blockNumber');

        return (int)hexdec($hex);
    }


    public function previewTransfer(string $from, string $to, BigDecimal $amount): PreviewTransferDTO
    {
        $gasPrice = $this->rpc('eth_gasPrice');

        $gasEstimate = $this->rpc('eth_estimateGas', [[
            'from' => $from,
            'to' => $to,
            'value' => '0x' . $amount->multipliedBy(pow(10, 18))->toBigInteger()->toBase(16)
        ]]);

        return PreviewTransferDTO::make([
            'gas_price' => $gasPrice,
            'gas_limit' => $gasEstimate,
        ]);
    }

    public function previewTokenTransfer(string $contract, string $from, string $to, BigDecimal $amount): PreviewTransferDTO
    {
        $decimals = $this->tokenDecimals[$contract] ??= $this->getTokenDecimals($contract);

        $data = '0xa9059cbb000000000000000000000000'.substr($to, 2).str_pad($amount->multipliedBy(pow(10, $decimals))->toBigInteger()->toBase(16), 64, '0', STR_PAD_LEFT);

        $gasPrice = $this->rpc('eth_gasPrice');
        $gasEstimate = $this->rpc('eth_estimateGas', [[
            'from' => $from,
            'to' => $contract,
            'data' => $data
        ]]);

        return PreviewTransferDTO::make([
            'gas_price' => $gasPrice,
            'gas_limit' => $gasEstimate,
        ]);
    }

    public function transfer(string $from, string $to, string $privateKey, BigDecimal $amount, int $gasLimit = 21000): string
    {
        $nonce = $this->rpc('eth_getTransactionCount', [$from, 'pending']);
        $gasPrice = $this->rpc('eth_gasPrice');
        $gasLimit = '0x'.dechex($gasLimit);

        $tx = new Transaction(
            nonce: substr($nonce, 2),
            gasPrice: substr($gasPrice, 2),
            gasLimit: substr($gasLimit, 2),
            to: $to,
            value: '0x' .$amount->multipliedBy(pow(10, 18))->toBigInteger()->toBase(16),
            data: ''
        );

        $raw = '0x' . $tx->getRaw($privateKey, 1);
        return $this->rpc('eth_sendRawTransaction', [$raw]);
    }

    public function transferToken(string $contract, string $from, string $to, string $privateKey, BigDecimal $amount, int $gasLimit = 50000): string
    {
        $decimals = $this->tokenDecimals[$contract] ??= $this->getTokenDecimals($contract);

        $contract = Str::lower($contract);
        $from = Str::lower($from);
        $to = Str::lower($to);

        $nonce = $this->rpc('eth_getTransactionCount', [$from, 'pending']);
        $gasPrice = $this->rpc('eth_gasPrice');
        $gasLimit = '0x'.dechex($gasLimit);

        $data = '0xa9059cbb000000000000000000000000'.substr($to, 2).str_pad($amount->multipliedBy(pow(10, $decimals))->toBigInteger()->toBase(16), 64, '0', STR_PAD_LEFT);

        $tx = new Transaction(
            nonce: substr($nonce, 2),
            gasPrice: substr($gasPrice, 2),
            gasLimit: substr($gasLimit, 2),
            to: $contract,
            value: '',
            data: $data
        );

        $raw = '0x' . $tx->getRaw($privateKey, 1);
        return $this->rpc('eth_sendRawTransaction', [$raw]);
    }
}
