<?php

return [
    /*
     * Touch Synchronization System (TSS) config
     * If there are many addresses in the system, we synchronize only those that have been touched recently.
     * You must update touch_at in EthereumAddress, if you want sync here.
     */
    'touch' => [
        /*
         * Is system enabled?
         */
        'enabled' => false,

        /*
         * The time during which the address is synchronized after touching it (in seconds).
         */
        'waiting_seconds' => 3600,
    ],

    /*
     * Sets the handler to be used when Ethereum Wallet
     * receives a new deposit.
     */
    'webhook_handler' => \Mollsoft\LaravelEthereumModule\Webhook\EmptyWebhookHandler::class,

    /*
     * Set model class for both TronWallet, TronAddress, TronTrc20,
     * to allow more customization.
     *
     * Node model must be or extend `\Mollsoft\LaravelEthereumModule\Models\EthereumNode::class`
     * Explorer model must be or extend `\Mollsoft\LaravelEthereumModule\Models\EthereumExplorer::class`
     * Token model must be or extend `\Mollsoft\LaravelEthereumModule\Models\EthereumToken::class`
     * Wallet model must be or extend `\Mollsoft\LaravelEthereumModule\Models\EthereumWallet:class`
     * Address model must be or extend `\Mollsoft\LaravelEthereumModule\Models\EthereumAddress::class`
     */
    'models' => [
        'node' => \Mollsoft\LaravelEthereumModule\Models\EthereumNode::class,
        'explorer' => \Mollsoft\LaravelEthereumModule\Models\EthereumExplorer::class,
        'token' => \Mollsoft\LaravelEthereumModule\Models\EthereumToken::class,
        'wallet' => \Mollsoft\LaravelEthereumModule\Models\EthereumWallet::class,
        'address' => \Mollsoft\LaravelEthereumModule\Models\EthereumAddress::class,
        'transaction' => \Mollsoft\LaravelEthereumModule\Models\EthereumTransaction::class,
        'deposit' => \Mollsoft\LaravelEthereumModule\Models\EthereumDeposit::class,
    ],
];
