<?php

namespace Mollsoft\LaravelEthereumModule\Concerns;

use Mollsoft\LaravelEthereumModule\Enums\EthereumModel;
use Mollsoft\LaravelEthereumModule\Facades\Ethereum;
use Mollsoft\LaravelEthereumModule\Models\EthereumExplorer;
use Mollsoft\LaravelEthereumModule\Models\EthereumNode;

trait Explorer
{
    public function createExplorer(string $name, string $baseURL, string $apiKey, ?string $title = null): EthereumExplorer
    {
        /** @var class-string<EthereumExplorer> $explorerModel */
        $explorerModel = Ethereum::getModel(EthereumModel::Explorer);
        $explorer = new $explorerModel([
            'name' => $name,
            'title' => $title,
            'base_url' => $baseURL,
            'api_key' => $apiKey,
            'requests' => 1,
            'worked' => true,
        ]);

        $explorer->api()->getApiLimit();
        $explorer->save();

        return $explorer;
    }

    public function createEtherscanExplorer(string $apiKey, string $name, ?string $title = null): EthereumNode
    {
        /** @var class-string<EthereumExplorer> $explorerModel */
        $explorerModel = Ethereum::getModel(EthereumModel::Explorer);
        $explorer = new $explorerModel([
            'name' => $name,
            'title' => $title,
            'base_url' => 'https://api.etherscan.io/api',
            'api_key' => $apiKey,
            'requests' => 1,
            'worked' => true,
        ]);

        $explorer->api()->getApiLimit();
        $explorer->save();

        return $explorer;
    }

    public function getExplorer(): EthereumExplorer
    {
        return $this->getModel(EthereumModel::Explorer)::query()
            ->where('worked', '=', true)
            ->orderBy('requests')
            ->firstOrFail();
    }
}
