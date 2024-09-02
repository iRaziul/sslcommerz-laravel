<?php

namespace Raziul\Sslcommerz;

use Illuminate\Contracts\Foundation\Application;
use Raziul\Sslcommerz\Exceptions\SslcommerzException;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SslcommerzServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('sslcommerz-laravel')
            ->hasConfigFile('sslcommerz')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('iraziul/sslcommerz-laravel');
            });
    }

    public function packageRegistered()
    {
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
}
