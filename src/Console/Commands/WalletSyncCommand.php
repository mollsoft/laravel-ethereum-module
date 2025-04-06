<?php

namespace Mollsoft\LaravelEthereumModule\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Mollsoft\LaravelEthereumModule\Enums\EthereumModel;
use Mollsoft\LaravelEthereumModule\Facades\Ethereum;
use Mollsoft\LaravelEthereumModule\Models\EthereumAddress;
use Mollsoft\LaravelEthereumModule\Models\EthereumNode;
use Mollsoft\LaravelEthereumModule\Models\EthereumWallet;
use Mollsoft\LaravelEthereumModule\Services\Sync\AddressSync;
use Mollsoft\LaravelEthereumModule\Services\Sync\NodeSync;
use Mollsoft\LaravelEthereumModule\Services\Sync\WalletSync;

class WalletSyncCommand extends Command
{
    protected $signature = 'ethereum:wallet-sync {wallet_id}';

    protected $description = 'Start Ethereum wallet sync';

    public function handle(): void
    {
        $walletId = (int)$this->argument('wallet_id');

        $this->line('-- Starting sync Ethereum wallet #'.$walletId.' ...');

        try {
            /** @var class-string<EthereumWallet> $model */
            $model = Ethereum::getModel(EthereumModel::Wallet);
            $wallet = $model::findOrFail($walletId);

            $this->line('-- Wallet: *'.$wallet->name.'*'.$wallet->title);

            $service = App::make(WalletSync::class, compact('wallet'));

            $service->setLogger(fn(string $message, ?string $type) => $this->{$type ? ($type === 'success' ? 'info' : $type) : 'line'}($message));

            $service->run();
        } catch (\Exception $e) {
            $this->error('-- Error: '.$e->getMessage());
        }

        $this->line('-- Completed!');
    }
}
