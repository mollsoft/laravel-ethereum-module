<?php

namespace Mollsoft\LaravelEthereumModule\Enums;

enum TransactionType: string
{
    case OUTGOING = 'out';
    case INCOMING = 'in';
}