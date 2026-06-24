<?php

namespace App\Providers;

use App\Services\TreeBinaryService;
use Illuminate\Support\ServiceProvider;

class TreeBinaryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(TreeBinaryService::class);
    }
}
