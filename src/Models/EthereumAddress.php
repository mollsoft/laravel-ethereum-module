<?php

namespace Mollsoft\LaravelEthereumModule\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mollsoft\LaravelEthereumModule\Casts\BigDecimalCast;
use Mollsoft\LaravelEthereumModule\Casts\EncryptedCast;
use Mollsoft\LaravelEthereumModule\Enums\EthereumModel;
use Mollsoft\LaravelEthereumModule\Facades\Ethereum;

class EthereumAddress extends Model
{
    protected $fillable = [
        'wallet_id',
        'address',
        'title',
        'watch_only',
        'private_key',
        'index',
        'balance',
        'tokens',
        'touch_at',
    ];

    protected $appends = [
        'tokens_balances'
    ];

    protected $hidden = [
        'private_key',
        'tokens',
    ];

    protected function casts(): array
    {
        return [
            'watch_only' => 'boolean',
            'private_key' => EncryptedCast::class,
            'balance' => BigDecimalCast::class,
            'tokens' => 'array',
            'touch_at' => 'datetime',
        ];
    }

    public function getPlainPasswordAttribute(): ?string
    {
        return $this->wallet->plain_password;
    }

    public function getPasswordAttribute(): ?string
    {
        return $this->wallet->password;
    }

    protected function tokensBalances(): Attribute
    {
        /** @var class-string<EthereumToken> $model */
        $model = Ethereum::getModel(EthereumModel::Token);

        return new Attribute(
            get: fn () => $model::get()->map(fn (Model $token) => [
                ...$token->only(['address', 'name', 'symbol', 'decimals']),
                'balance' => $this->tokens[$token->address] ?? null,
            ])->keyBy('address')
        );
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(EthereumWallet::class);
    }
}
