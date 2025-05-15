<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Illuminate\Database\Eloquent\Relations\Relation;
/**
 * Class AppServiceProvider.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(OAuthService::class, function ($app) {
            return new OAuthService();
        });
        
        $this->app->singleton(RoleService::class, function ($app) {
            return new RoleService();
        });
        
        $this->app->singleton(TokenService::class, function ($app) {
            return new TokenService($app->make(OAuthService::class));
        });
        
        // Register middleware with dependencies
        $this->app->bind(AuthenticationMiddleware::class, function ($app) {
            return new AuthenticationMiddleware($app->make(TokenService::class));
        });

        $this->app->singleton(\App\Services\HubApiService::class, function ($app) {
            return new \App\Services\HubApiService($app->make(\App\Services\OAuthService::class));
        });
    }


    protected function schedule(Schedule $schedule)
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        Relation::morphMap([
            'teacher' => \App\Models\Teacher::class,
            // 'supervisor' => \App\Models\Supervisor::class,
       
        ]);
    }
}
