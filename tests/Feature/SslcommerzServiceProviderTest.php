<?php

declare(strict_types=1);

use Raziul\Sslcommerz\Exceptions\SslcommerzException;
use Raziul\Sslcommerz\SslcommerzClient;

describe('SslcommerzServiceProvider', function () {
    beforeEach(function () {
        $this->app = $this->refreshApplication();
    });

    it('merges configuration', function () {
        expect(config('sslcommerz'))->toBeArray();
        expect(config('sslcommerz.sandbox'))->toBeTrue();
    });

    it('throws exception for missing store credentials', function () {
        config()->set('sslcommerz', [
            'store' => [
                'id' => '',
                'password' => null,
                'currency' => 'BDT',
            ],
            'route' => [
                'success' => 'sslc.success',
                'failure' => 'sslc.failure',
                'cancel' => 'sslc.cancel',
                'ipn' => 'sslc.ipn',
            ],
        ]);

        app(SslcommerzClient::class);
    })->throws(SslcommerzException::class, 'SSLCommerz store credentials are not set.');

    it('registers SslcommerzClient as a singleton', function () {
        config()->set('sslcommerz', [
            'sandbox' => true,
            'store' => [
                'id' => 'test_id',
                'password' => 'test_password',
                'currency' => 'BDT',
            ],
            'route' => [
                'success' => 'sslc.success',
                'failure' => 'sslc.failure',
                'cancel' => 'sslc.cancel',
                'ipn' => 'sslc.ipn',
            ],
            'product_profile' => 'general',
        ]);

        // Register dummy routes for route() to work
        Route::get('/success', fn () => 'success')->name('sslc.success');
        Route::get('/failure', fn () => 'failure')->name('sslc.failure');
        Route::get('/cancel', fn () => 'cancel')->name('sslc.cancel');
        Route::get('/ipn', fn () => 'ipn')->name('sslc.ipn');

        $client1 = app(SslcommerzClient::class);
        $client2 = app(SslcommerzClient::class);

        expect($client1)->toBeInstanceOf(SslcommerzClient::class);
        expect($client1)->toBe($client2);
    });
});
