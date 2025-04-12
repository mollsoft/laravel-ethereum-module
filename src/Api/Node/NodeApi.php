<?php

namespace Mollsoft\LaravelEthereumModule\Api\Node;

use Brick\Math\BigDecimal;
use Brick\Math\BigInteger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use kornrunner\Ethereum\Token;
use kornrunner\Ethereum\Transaction;
use Mollsoft\LaravelEthereumModule\Api\Node\DTO\PreviewTransferDTO;
use Mollsoft\LaravelEthereumModule\Api\Node\DTO\TransferDTO;

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

        if( count($result ?? []) === 0 || !isset( $result['result'] ) ) {
            throw new \Exception($response->body());
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

    public static function hexToBigDecimal(string $hex): BigDecimal
    {
        $value = ltrim($hex, '0x');
        $value = BigInteger::fromBase($value, 16);
        return BigDecimal::ofUnscaledValue($value);
    }

    public static function bigDecimalToHex(BigDecimal $bigDecimal): string
    {
        $hex = $bigDecimal->toBigInteger()->toBase(16);
        if (strlen($hex) % 2 !== 0) {
            $hex = '0' . $hex;
        }

        return $hex;
    }

    public function gasPrice(): BigDecimal
    {
        $value = $this->rpc('eth_gasPrice');

        return static::hexToBigDecimal($value);
    }

    public function gasEstimate(string $from, string $to, string $data): BigDecimal
    {
        $value = $this->rpc('eth_estimateGas', [
            [
                'from' => $from,
                'to' => $to,
                'data' => $data
            ]
        ]);

        return static::hexToBigDecimal($value);
    }

    public function previewTransfer(
        string $from,
        string $to,
        BigDecimal $amount,
        ?BigDecimal $balanceBefore = null,
        ?int $gasLimit = null
    ): PreviewTransferDTO {
        $from = Str::lower($from);
        $to = Str::lower($to);

        $data = '0x'.static::bigDecimalToHex($amount->multipliedBy(pow(10, 18)));

        $gasPrice = $this->gasPrice();
        $gasEstimate = $this->gasEstimate($from, $to, $data);
        if( $gasLimit ) {
            $gasLimit = BigDecimal::of($gasLimit);
            $gasEstimate = $gasLimit->isLessThan($gasEstimate) ? $gasLimit : $gasEstimate;
        }
        $fee = $gasPrice
            ->multipliedBy($gasEstimate)
            ->dividedBy(pow(10, 18), 18);

        if ($balanceBefore === null) {
            $balanceBefore = $this->getBalance($from);
        }
        $balanceAfter = $balanceBefore->minus($fee)->minus($amount);

        $error = null;
        if ($balanceAfter->isNegative()) {
            $error = 'Недостаточно баланса ETH';
        }

        return PreviewTransferDTO::make([
            'from' => $from,
            'to' => $to,
            'amount' => $amount->__toString(),
            'data' => $data,
            'gas_price' => $gasPrice->__toString(),
            'gas_limit' => $gasEstimate->__toString(),
            'fee' => $fee->__toString(),
            'balance_before' => $balanceBefore->__toString(),
            'balance_after' => $balanceAfter->__toString(),
            'error' => $error,
        ]);
    }

    public function transfer(
        string $from,
        string $to,
        string $privateKey,
        BigDecimal $amount,
        ?BigDecimal $balanceBefore = null,
        ?int $gasLimit = null
    ): TransferDTO {
        $preview = $this->previewTransfer($from, $to, $amount, $balanceBefore, $gasLimit);
        if( $preview->hasError() ) {
            throw new \Exception($preview->error());
        }

        $nonce = $this->rpc('eth_getTransactionCount', [$from, 'pending']);
        $gasPrice = static::bigDecimalToHex($preview->gasPrice());
        $gasLimit = static::bigDecimalToHex($preview->gasLimit());

        $tx = new Transaction(
            nonce: substr($nonce, 2),
            gasPrice: $gasPrice,
            gasLimit: $gasLimit,
            to: $preview->to(),
            value: '0x'.static::bigDecimalToHex($amount->multipliedBy(pow(10, 18))),
            data: ''
        );

        $raw = '0x'.$tx->getRaw($privateKey, 1);
        $txid = $this->rpc('eth_sendRawTransaction', [$raw]);

        return TransferDTO::make([
            ...$preview->toArray(),
            'txid' => $txid,
        ]);
    }

    public function previewTokenTransfer(
        string $contract,
        string $from,
        string $to,
        BigDecimal $amount,
        ?BigDecimal $balanceBefore = null,
        ?BigDecimal $tokenBalanceBefore = null,
        ?int $gasLimit = null,
    ): PreviewTransferDTO {
        $contract = Str::lower($contract);
        $from = Str::lower($from);
        $to = Str::lower($to);

        $decimals = $this->tokenDecimals[$contract] ??= $this->getTokenDecimals($contract);

        if ($tokenBalanceBefore === null) {
            $tokenBalanceBefore = $this->getBalanceOfToken($from, $contract);
        }
        $tokenBalanceAfter = $tokenBalanceBefore->minus($amount);

        if ($balanceBefore === null) {
            $balanceBefore = $this->getBalance($from);
        }

        if ($tokenBalanceAfter->isNegative()) {
            return PreviewTransferDTO::make([
                'contract' => $contract,
                'from' => $from,
                'to' => $to,
                'amount' => $amount->__toString(),
                'data' => '',
                'gas_price' => 0,
                'gas_limit' => 0,
                'fee' => 0,
                'balance_before' => $balanceBefore->__toString(),
                'balance_after' => $balanceBefore->__toString(),
                'token_balance_before' => $tokenBalanceBefore->__toString(),
                'token_balance_after' => $tokenBalanceAfter->__toString(),
                'error' => 'Недостаточно баланса токена',
            ]);
        }

        $data = '0xa9059cbb000000000000000000000000'.substr($to, 2).str_pad(
                $amount->multipliedBy(pow(10, $decimals))->toBigInteger()->toBase(16),
                64,
                '0',
                STR_PAD_LEFT
            );

        $gasPrice = $this->gasPrice();
        $gasEstimate = $this->gasEstimate($from, $contract, $data);
        if( $gasLimit ) {
            $gasLimit = BigDecimal::of($gasLimit);
            $gasEstimate = $gasLimit->isLessThan($gasEstimate) ? $gasLimit : $gasEstimate;
        }
        $fee = $gasPrice
            ->multipliedBy($gasEstimate)
            ->dividedBy(pow(10, 18), 18);
        $balanceAfter = $balanceBefore->minus($fee);

        $error = null;
        if ($balanceAfter->isNegative()) {
            $error = 'Недостаточно баланса ETH';
        }

        return PreviewTransferDTO::make([
            'contract' => $contract,
            'from' => $from,
            'to' => $to,
            'amount' => $amount->__toString(),
            'data' => $data,
            'gas_price' => $gasPrice->__toString(),
            'gas_limit' => $gasEstimate->__toString(),
            'fee' => $fee->__toString(),
            'balance_before' => $balanceBefore->__toString(),
            'balance_after' => $balanceAfter->__toString(),
            'token_balance_before' => $tokenBalanceBefore->__toString(),
            'token_balance_after' => $tokenBalanceAfter->__toString(),
            'error' => $error,
        ]);
    }

    public function transferToken(
        string $contract,
        string $from,
        string $to,
        string $privateKey,
        BigDecimal $amount,
        ?BigDecimal $balanceBefore = null,
        ?BigDecimal $tokenBalanceBefore = null,
        ?int $gasLimit = null,
    ): TransferDTO {
        $preview = $this->previewTokenTransfer($contract, $from, $to, $amount, $balanceBefore, $tokenBalanceBefore, $gasLimit);
        if( $preview->hasError() ) {
            throw new \Exception($preview->error());
        }

        $nonce = $this->rpc('eth_getTransactionCount', [$from, 'pending']);
        $gasPrice = $preview->gasPrice()->toBigInteger()->toBase(16);
        $gasLimit = $preview->gasLimit()->toBigInteger()->toBase(16);

        $tx = new Transaction(
            nonce: substr($nonce, 2),
            gasPrice: $gasPrice,
            gasLimit: $gasLimit,
            to: $preview->contract(),
            value: '',
            data: $preview->data()
        );

        $raw = '0x'.$tx->getRaw($privateKey, 1);
        $txid = $this->rpc('eth_sendRawTransaction', [$raw]);

        return TransferDTO::make([
            ...$preview->toArray(),
            'txid' => $txid,
        ]);
    }
}
