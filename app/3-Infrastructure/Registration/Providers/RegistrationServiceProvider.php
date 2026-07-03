<?php
namespace Promolider\Infrastructure\Registration\Providers;

use Illuminate\Support\ServiceProvider;
use Promolider\Domain\Registration\Ports\Out\PreregistroRepositoryInterface;
use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;
use Promolider\Domain\Registration\Ports\Out\PaymentGatewayInterface;
use Promolider\Domain\Registration\Ports\Out\NotificationServiceInterface;
use Promolider\Infrastructure\Registration\Out\Persistence\EloquentPreregistroRepository;
use Promolider\Infrastructure\Registration\Out\Persistence\EloquentRegistrationRepository;
use Promolider\Infrastructure\Registration\Out\Payment\OpenpayPaymentGateway;
use Promolider\Infrastructure\Registration\Out\Notifications\SesNotificationService;

class RegistrationServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind Ports (Domain) to Adapters (Infrastructure)
        $this->app->bind(PreregistroRepositoryInterface::class, EloquentPreregistroRepository::class);
        $this->app->bind(RegistrationRepositoryInterface::class, EloquentRegistrationRepository::class);
        $this->app->bind(PaymentGatewayInterface::class, OpenpayPaymentGateway::class);
        $this->app->bind(NotificationServiceInterface::class, SesNotificationService::class);
    }

    public function boot()
    {
        //
    }
}
