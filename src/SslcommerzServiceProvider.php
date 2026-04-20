<?php

declare(strict_types=1);

namespace Raziul\Sslcommerz;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Raziul\Sslcommerz\Console\Commands\InstallCommand;
use Raziul\Sslcommerz\Exceptions\SslcommerzException;

final class SslcommerzServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sslcommerz.php', 'sslcommerz');

        $this->app->singleton(SslcommerzClient::class, function (Application $app) {
            $config = $app->config->get('sslcommerz');

            if (empty($config['store']['id']) || empty($config['store']['password'])) {
                throw new SslcommerzException('SSLCommerz store credentials are not set.');
            }

            return (new SslcommerzClient(
                $config['store']['id'],
                $config['store']['password'],
                $config['store']['currency'],
                $config['sandbox']
            ))
                ->setCallbackUrls(
                    route($config['route']['success']),
                    route($config['route']['failure']),
                    route($config['route']['cancel']),
                    route($config['route']['ipn'])
                )
                ->setProductProfile($config['product_profile']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerCommands();
    }

    /**
     * Register the package's publishable resources.
     */
    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/sslcommerz.php' => config_path('sslcommerz.php'),
            ], 'sslcommerz-config');
        }
    }

    /**
     * Register the package's commands.
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}
