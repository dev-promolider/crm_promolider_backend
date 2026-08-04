<?php

namespace Promolider\Infrastructure\Infoproducts\Providers;

use Illuminate\Support\ServiceProvider;
use Promolider\Domain\Infoproducts\Ports\Out\ClassVideoStorageInterface;
use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleClassRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleRepositoryInterface;
use Promolider\Infrastructure\Infoproducts\Out\Persistence\EloquentInfoproductRepository;
use Promolider\Infrastructure\Infoproducts\Out\Persistence\EloquentCourseRepository;
use Promolider\Infrastructure\Infoproducts\Out\Persistence\EloquentModuleClassRepository;
use Promolider\Infrastructure\Infoproducts\Out\Persistence\EloquentModuleRepository;
use Promolider\Infrastructure\Infoproducts\Out\Storage\S3ClassVideoStorageService;

class InfoproductServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(InfoproductRepositoryInterface::class, EloquentInfoproductRepository::class);
        $this->app->bind(CourseRepositoryInterface::class, EloquentCourseRepository::class);
        $this->app->bind(ModuleClassRepositoryInterface::class, EloquentModuleClassRepository::class);
        $this->app->bind(ModuleRepositoryInterface::class, EloquentModuleRepository::class);
        $this->app->bind(ClassVideoStorageInterface::class, S3ClassVideoStorageService::class);
    }

    public function boot()
    {
        //
    }
}
