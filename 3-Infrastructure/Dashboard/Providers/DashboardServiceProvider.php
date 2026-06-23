<?php
namespace Promolider\Infrastructure\Dashboard\Providers;

use Illuminate\Support\ServiceProvider;
use Promolider\Domain\Dashboard\Ports\Out\DashboardRepositoryInterface;
use Promolider\Infrastructure\Dashboard\Out\Persistence\EloquentDashboardRepository;

class DashboardServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(DashboardRepositoryInterface::class, EloquentDashboardRepository::class);
    }

    public function boot()
    {
        //
    }
}
