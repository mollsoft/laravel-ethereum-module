<?php

namespace Mollsoft\LaravelEthereumModule\Models;

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
}
