<?php
namespace Romario25\Subscriptions;


use Illuminate\Support\ServiceProvider;

class SubscriptionsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/subscriptions.php' => config_path('subscriptions.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../migrations');
    }

    public function register()
    {

        $this->mergeConfigFrom(
            __DIR__.'/../config/subscriptions.php', 'subscriptions'
        );

        $this->app->bind('Subscriptions', function($app){
            $subscriptions = new SubscriptionsService("test");

            return $subscriptions;
        });
    }
}