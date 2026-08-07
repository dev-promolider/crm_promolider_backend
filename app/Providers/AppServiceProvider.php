<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \Promolider\Domain\Requests\Repositories\WithdrawalRequestRepositoryInterface::class,
            \Promolider\Infrastructure\Requests\Out\Persistence\EloquentWithdrawalRequestRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
