<?php

namespace Mollsoft\LaravelEthereumModule\Concerns;

use Illuminate\Support\Str;
use Mollsoft\LaravelEthereumModule\Enums\EthereumModel;
use Mollsoft\LaravelEthereumModule\Facades\Ethereum;
use Mollsoft\LaravelEthereumModule\Models\EthereumNode;
use Mollsoft\LaravelEthereumModule\Models\EthereumToken;

trait Token
{
    public function createToken(string $contract, ?EthereumNode $node = null)
    {
        $contract = Str::lower($contract);

        if( !$node ) {
            $node = Ethereum::getNode();
        }

        $node->increment('requests', 3);

        $api = $node->api();
        $name = $api->getTokenName($contract);
        $symbol = $api->getTokenSymbol($contract);
        $decimals = $api->getTokenDecimals($contract);

        /** @var class-string<EthereumToken> $model */
        $model = Ethereum::getModel(EthereumModel::Token);

        return $model::create([
            'address' => $contract,
            'name' => $name,
            'symbol' => $symbol,
            'decimals' => $decimals,
        ]);
    }
}
