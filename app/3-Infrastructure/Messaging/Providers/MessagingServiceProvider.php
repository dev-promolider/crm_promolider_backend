<?php

namespace Promolider\Infrastructure\Messaging\Providers;

use Illuminate\Support\ServiceProvider;
use Promolider\Domain\Messaging\Ports\Out\MessageRepositoryInterface;
use Promolider\Infrastructure\Messaging\Out\Persistence\EloquentMessageRepository;

class MessagingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(MessageRepositoryInterface::class, EloquentMessageRepository::class);
    }

    public function boot()
    {
        //
    }
}
