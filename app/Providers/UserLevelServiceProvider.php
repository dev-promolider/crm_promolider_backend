<?php

namespace App\Providers;

use App\Services\UserLevelService;
use Illuminate\Support\ServiceProvider;

class UserLevelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserLevelService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
