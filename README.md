![Pest Laravel Expectations](https://banners.beyondco.de/Ethereum.png?theme=light&packageManager=composer+require&packageName=mollsoft%2Flaravel-ethereum-module&pattern=architect&style=style_1&description=Ethereum+Cryptocurrency+Wallet+Module+for+Laravel%3A+Balances%2C+Transactions%2C+Transfers%2C+ERC-20&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

<a href="https://packagist.org/packages/mollsoft/laravel-ethereum-module" target="_blank">
    <img style="display: inline-block; margin-top: 0.5em; margin-bottom: 0.5em" src="https://img.shields.io/packagist/v/mollsoft/laravel-ethereum-module.svg?style=flat&cacheSeconds=3600" alt="Latest Version on Packagist">
</a>

<a href="https://www.php.net">
    <img style="display: inline-block; margin-top: 0.5em; margin-bottom: 0.5em" src="https://img.shields.io/badge/php-%3E=8.2-brightgreen.svg?maxAge=2592000" alt="Php Version">
</a>

<a href="https://laravel.com/">
    <img style="display: inline-block; margin-top: 0.5em; margin-bottom: 0.5em" src="https://img.shields.io/badge/laravel-%3E=10-red.svg?maxAge=2592000" alt="Php Version">
</a>

<a href="https://packagist.org/packages/mollsoft/laravel-ethereum-module" target="_blank">
    <img style="display: inline-block; margin-top: 0.5em; margin-bottom: 0.5em" src="https://img.shields.io/packagist/dt/mollsoft/laravel-ethereum-module.svg?style=flat&cacheSeconds=3600" alt="Total Downloads">
</a>

<a href="https://mollsoft.com"><img alt="Website" src="https://img.shields.io/badge/Website-https://mollsoft.com-black"></a>
<a href="https://t.me/mollsoft"><img alt="Telegram" src="https://img.shields.io/badge/Telegram-@mollsoft-blue"></a>

**Laravel Ethereum Module** is a Laravel package for work with cryptocurrency Ethereum, with the support ERC-20 tokens. It allows you to generate HD wallets using mnemonic phrase, validate addresses, get addresses balances and resources, preview and send ETH/ERC-20 tokens. You can automate the acceptance and withdrawal of cryptocurrency in your application.

You can contact me for help in integrating payment acceptance into your project.

## Requirements

The following versions of PHP are supported by this version.

* PHP 8.2 and older
* Laravel 10 or older
* PHP Extensions: GMP, BCMath, CType.

## Installation
You can install the package via composer:
```bash
composer require mollsoft/laravel-ethereum-module
```

After you can run installer using command:
```bash
php artisan ethereum:install
```

And run migrations:
```bash
php artisan migrate
```

Register Service Provider and Facade in app, edit `config/app.php`:
```php
'providers' => ServiceProvider::defaultProviders()->merge([
    ...,
    \Mollsoft\LaravelEthereumModule\EthereumServiceProvider::class,
])->toArray(),

'aliases' => Facade::defaultAliases()->merge([
    ...,
    'Ethereum' => \Mollsoft\LaravelEthereumModule\Facades\Ethereum::class,
])->toArray(),
```

For Laravel 10 you edit file `app/Console/Kernel` in method `schedule(Schedule $schedule)` add:
```php
$schedule->command('ethereum:sync')
    ->everyMinute()
    ->runInBackground();
```

or for Laravel 11+ add this content to `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

...

Schedule::command('ethereum:sync')
    ->everyMinute()
    ->runInBackground();
```

## Examples
First you need to add Ethereum Nodes, you can register account in <a href="https://www.ankr.com/rpc/">ANKR.COM</a> get take HTTPS Endpoint with API key for Ethereum blockchain:
```php
use \Mollsoft\LaravelEthereumModule\Facades\Ethereum;

Ethereum::createNode('My node', 'https://rpc.ankr.com/eth/{API_KEY}');
```

Second you need add Ethereum Explorer, you can register account in <a href="https://etherscan.io/apis">Etherscan.io API</a> and take Endpoint with API key:
```php
use \Mollsoft\LaravelEthereumModule\Facades\Ethereum;

Ethereum::createExplorer('My explorer', 'https://api.etherscan.io/api', '{API_KEY}');
```

You can create ERC-20 Token:
```php
use \Mollsoft\LaravelEthereumModule\Facades\Ethereum;

$contractAddress = '0xdac17f958d2ee523a2206206994597c13d831ec7';
Ethereum::createToken($contractAddress);
```

Now you can create new Wallet:
```php
use \Mollsoft\LaravelEthereumModule\Facades\Ethereum;

$wallet = Ethereum::createWallet('My wallet');
```