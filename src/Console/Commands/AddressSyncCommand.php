<?php

namespace Mollsoft\LaravelEthereumModule\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Mollsoft\LaravelEthereumModule\Enums\EthereumModel;
use Mollsoft\LaravelEthereumModule\Facades\Ethereum;
use Mollsoft\LaravelEthereumModule\Models\EthereumAddress;
use Mollsoft\LaravelEthereumModule\Models\EthereumNode;
use Mollsoft\LaravelEthereumModule\Services\Sync\AddressSync;
use Mollsoft\LaravelEthereumModule\Services\Sync\NodeSync;

class AddressSyncCommand extends Command
{
    protected $signature = 'ethereum:address-sync {address_id}';

    protected $description = 'Start Ethereum address sync';

    public function handle(): void
    {
        $addressId = (int)$this->argument('address_id');

        $this->line('-- Starting sync Ethereum address #'.$addressId.' ...');

        try {
            /** @var class-string<EthereumAddress> $model */
            $model = Ethereum::getModel(EthereumModel::Address);
            $address = $model::findOrFail($addressId);

            $this->line('-- Address: *'.$address->address.'*'.$address->title);

            $service = App::make(AddressSync::class, [
                'address' => $address,
                'force' => true,
            ]);

            $service->setLogger(fn(string $message, ?string $type) => $this->{$type ? ($type === 'success' ? 'info' : $type) : 'line'}($message));

            $service->run();
        } catch (\Exception $e) {
            $this->error('-- Error: '.$e->getMessage());
        }

        $this->line('-- Completed!');
    }
}
