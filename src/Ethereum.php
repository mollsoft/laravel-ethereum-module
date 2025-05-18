<?php

namespace Mollsoft\LaravelEthereumModule;

use Illuminate\Database\Eloquent\Model;
use Mollsoft\LaravelEthereumModule\Concerns\Address;
use Mollsoft\LaravelEthereumModule\Concerns\Explorer;
use Mollsoft\LaravelEthereumModule\Concerns\Mnemonic;
use Mollsoft\LaravelEthereumModule\Concerns\Node;
use Mollsoft\LaravelEthereumModule\Concerns\Token;
use Mollsoft\LaravelEthereumModule\Concerns\Transfer;
use Mollsoft\LaravelEthereumModule\Concerns\Wallet;
use Mollsoft\LaravelEthereumModule\Enums\EthereumModel;
use Mollsoft\LaravelEthereumModule\Models\EthereumExplorer;
use Mollsoft\LaravelEthereumModule\Models\EthereumNode;

class Ethereum
{
    use Node, Explorer, Token, Mnemonic, Address, Wallet, Transfer;

    /**
     * @param EthereumModel $model
     * @return class-string<Model>
     */
    public function getModel(EthereumModel $model): string
    {
        return config('ethereum.models.'.$model->value);
    }

    public function getNode(): EthereumNode
    {
        return $this->getModel(EthereumModel::Node)::query()
            ->where('worked', '=', true)
            ->where('available', '=', true)
            ->orderBy('requests')
            ->firstOrFail();
    }

    public function getExplorer(): EthereumExplorer
    {
        return $this->getModel(EthereumModel::Explorer)::query()
            ->where('worked', '=', true)
            ->where('available', '=', true)
            ->orderBy('requests')
            ->firstOrFail();
    }
}
