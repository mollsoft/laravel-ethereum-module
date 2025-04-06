<?php

namespace Mollsoft\LaravelEthereumModule\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Mollsoft\LaravelEthereumModule\Enums\EthereumModel;
use Mollsoft\LaravelEthereumModule\Facades\Ethereum;
use Mollsoft\LaravelEthereumModule\Models\EthereumAddress;
use Mollsoft\LaravelEthereumModule\Models\EthereumNode;
use Mollsoft\LaravelEthereumModule\Models\EthereumWallet;
use Mollsoft\LaravelEthereumModule\Services\Sync\AddressSync;
use Mollsoft\LaravelEthereumModule\Services\Sync\EthereumSync;
use Mollsoft\LaravelEthereumModule\Services\Sync\NodeSync;
use Mollsoft\LaravelEthereumModule\Services\Sync\WalletSync;

class EthereumSyncCommand extends Command
{
    protected $signature = 'ethereum:sync';

    protected $description = 'Start Ethereum sync';

    public function handle(): void
    {
        Cache::lock('ethereum', 300)->get(function() {
            $this->line('---- Starting sync Ethereum...');

            try {
                $service = App::make(EthereumSync::class);

                $service->setLogger(fn(string $message, ?string $type) => $this->{$type ? ($type === 'success' ? 'info' : $type) : 'line'}($message));

                $service->run();
            } catch (\Exception $e) {
                $this->error('---- Error: '.$e->getMessage());
            }

            $this->line('---- Completed!');
        });
    }
}
