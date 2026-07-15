<?php

namespace Promolider\Infrastructure\Wallet\Providers;

use Illuminate\Support\ServiceProvider;
use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;
use Promolider\Infrastructure\Wallet\Out\Persistence\EloquentWalletRepository;

class WalletServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(WalletRepositoryInterface::class, EloquentWalletRepository::class);
    }

    public function boot()
    {
        //
    }
}
