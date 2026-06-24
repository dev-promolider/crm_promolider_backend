<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
      //  'App\Models\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class,      
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes();
        Gate::define('is-admin', function (User $user) {
            return $user->accountType->id == 1;
        });
        Gate::define('viewWebSocketsDashboard', function ($user = null) {
            return $user != null;
        });
    }
}