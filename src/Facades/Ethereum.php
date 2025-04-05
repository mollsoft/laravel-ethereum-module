<?php

namespace Mollsoft\LaravelEthereumModule\Facades;

use Illuminate\Support\Facades\Facade;

class Ethereum extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Mollsoft\LaravelEthereumModule\Ethereum::class;
    }
}
