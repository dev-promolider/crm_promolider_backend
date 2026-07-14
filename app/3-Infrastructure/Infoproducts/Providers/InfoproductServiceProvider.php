<?php

namespace Promolider\Infrastructure\Infoproducts\Providers;

use Illuminate\Support\ServiceProvider;
use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;
use Promolider\Infrastructure\Infoproducts\Out\Persistence\EloquentInfoproductRepository;
use Promolider\Infrastructure\Infoproducts\Out\Persistence\EloquentCourseRepository;

class InfoproductServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(InfoproductRepositoryInterface::class, EloquentInfoproductRepository::class);
        $this->app->bind(CourseRepositoryInterface::class, EloquentCourseRepository::class);
    }

    public function boot()
    {
        //
    }
}
