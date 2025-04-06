<?php

namespace Mollsoft\LaravelEthereumModule\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Mollsoft\LaravelEthereumModule\Enums\EthereumModel;
use Mollsoft\LaravelEthereumModule\Facades\Ethereum;
use Mollsoft\LaravelEthereumModule\Models\EthereumExplorer;
use Mollsoft\LaravelEthereumModule\Services\Sync\ExplorerSync;

class ExplorerSyncCommand extends Command
{
    protected $signature = 'ethereum:explorer-sync {explorer_id}';

    protected $description = 'Start Ethereum explorer sync';

    public function handle(): void
    {
        $explorerId = (int)$this->argument('explorer_id');

        $this->line('-- Starting sync Ethereum Explorer #'.$explorerId.' ...');

        try {
            /** @var class-string<EthereumExplorer> $model */
            $model = Ethereum::getModel(EthereumModel::Explorer);
            $explorer = $model::findOrFail($explorerId);

            $this->line('-- Explorer: *'.$explorer->name.'*'.$explorer->title);

            $service = App::make(ExplorerSync::class, [
                'explorer' => $explorer
            ]);

            $service->setLogger(fn(string $message, ?string $type) => $this->{$type ? ($type === 'success' ? 'info' : $type) : 'line'}($message));

            $service->run();
        } catch (\Exception $e) {
            $this->error('-- Error: '.$e->getMessage());
        }

        $this->line('-- Completed!');
    }
}
