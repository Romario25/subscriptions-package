<?php
namespace Romario25\Subscriptions;


use Illuminate\Support\ServiceProvider;
use Romario25\Subscriptions\Services\VerifyService;

class SubscriptionsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/subscriptions.php' => config_path('subscriptions.php'),
            'config'
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../migrations');
    }

    public function register()
    {

        $this->mergeConfigFrom(
            __DIR__.'/../config/subscriptions.php', 'subscriptions'
        );

        $this->app->bind(SubscriptionsService::class, function($app){
            $subscriptions = new SubscriptionsService(new VerifyService());

            return $subscriptions;
        });
    }
}