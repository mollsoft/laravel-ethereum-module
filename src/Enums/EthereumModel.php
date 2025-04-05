<?php

namespace Mollsoft\LaravelEthereumModule\Enums;

enum EthereumModel: string
{
    case Node = 'node';
    case Explorer = 'explorer';
    case Token = 'token';
    case Wallet = 'wallet';
    case Address = 'address';
}
