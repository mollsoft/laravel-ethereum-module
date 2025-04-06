<?php

namespace Mollsoft\LaravelEthereumModule;

use Mollsoft\LaravelEthereumModule\Console\Commands\AddressSyncCommand;
use Mollsoft\LaravelEthereumModule\Console\Commands\EthereumSyncCommand;
use Mollsoft\LaravelEthereumModule\Console\Commands\ExplorerSyncCommand;
use Mollsoft\LaravelEthereumModule\Console\Commands\NodeSyncCommand;
use Mollsoft\LaravelEthereumModule\Console\Commands\WalletSyncCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EthereumServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('ethereum')
            ->hasConfigFile()
            ->hasCommands(
                NodeSyncCommand::class,
                ExplorerSyncCommand::class,
                AddressSyncCommand::class,
                WalletSyncCommand::class,
                EthereumSyncCommand::class,
            )
            ->discoversMigrations()
            ->runsMigrations()
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations();
            });

        $this->app->singleton(Ethereum::class);
    }
}
