<?php

namespace Mollsoft\LaravelEthereumModule\Models;

use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Model;
use Mollsoft\LaravelEthereumModule\Api\Node\NodeApi;

class EthereumNode extends Model
{
    protected ?NodeApi $_api = null;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'title',
        'base_url',
        'sync_at',
        'sync_data',
        'requests',
        'requests_at',
        'worked',
    ];

    protected function casts(): array
    {
        return [
            'sync_at' => 'datetime',
            'sync_data' => 'array',
            'requests_at' => 'date',
            'worked' => 'boolean',
        ];
    }

    public function api(): NodeApi
    {
        if( !$this->_api ) {
            $this->_api = new NodeApi($this->base_url);
        }

        return $this->_api;
    }

    public function getLatestBlockNumber(): int
    {
        $result = $this->api()->getLatestBlockNumber();

        $this->increment('requests');

        return $result;
    }

    public function getBalance(string|EthereumAddress $address): BigDecimal
    {
        if( $address instanceof EthereumAddress ) {
            $address = $address->address;
        }

        $result = $this->api()->getBalance($address);

        $this->increment('requests');

        return $result;
    }

    public function getBalanceOfToken(string|EthereumAddress $address, string|EthereumToken $contract): BigDecimal
    {
        if( $address instanceof EthereumAddress ) {
            $address = $address->address;
        }
        if( $contract instanceof EthereumToken ) {
            $contract = $contract->address;
        }

        $result = $this->api()->getBalanceOfToken($address, $contract);

        $this->increment('requests');

        return $result;
    }
}
