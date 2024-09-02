# Sslcommerz Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/iraziul/sslcommerz-laravel.svg?style=flat-square)](https://packagist.org/packages/iraziul/sslcommerz-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/iraziul/sslcommerz-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/iraziul/sslcommerz-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/iraziul/sslcommerz-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/iraziul/sslcommerz-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/iraziul/sslcommerz-laravel.svg?style=flat-square)](https://packagist.org/packages/iraziul/sslcommerz-laravel)

The `sslcommerz-laravel` package integrates SSLCommerz payment gateway into your Laravel application. It provides a simple and fluent API for processing payments, refunds, and validating transactions.

## Requirements

-   PHP 8.2 or above
-   Laravel 10.0 or above

## Installation

You can install the package via composer:

```bash
composer require raziul/sslcommerz-laravel
```

After installation, the service provider will automatically be registered. The package will also register a facade named `Sslcommerz` for easy access.

## Configuration

To configure the package, add the following environment variables to your `.env` file:

```bash
SSLC_SANDBOX=true # or false
SSLC_STORE_ID=
SSLC_STORE_PASSWORD=
SSLC_STORE_CURRENCY='BDT'

# SSLCommerz route names
SSLC_ROUTE_SUCCESS='sslc.success'
SSLC_ROUTE_FAILURE='sslc.failure'
SSLC_ROUTE_CANCEL='sslc.cancel'
SSLC_ROUTE_IPN='sslc.ipn'
```

These variables will be automatically used by the package.

Optionally, You can publish the config file with:

```bash
php artisan sslcommerz:install"
```

## Usage

### Initiating a Payment

To create a new payment, you can use the Sslcommerz facade:

```php
use \Raziul\Sslcommerz\Facades\Sslcommerz;

$response = Sslcommerz::setOrder(
        $amount,
        $invoiceId,
        $productName,
    )->setCustomer(
        $customerName,
        $customerEmail,
        $customerPhone,
    )->setShippingInfo(
        $itemsQuantity,
        $address,
    )->makePayment();

if ($response->success()) {
    // payment initiated, redirect to payment page
    return redirect($response->gatewayPageURL());
} else {
    // Handle payment failure
}
```

### Validating a Payment

To validate a payment after a successful transaction:

```php
use \Raziul\Sslcommerz\Facades\Sslcommerz;

$isValid = Sslcommerz::validatePayment(
    request()->all(),
    $transactionId,
    $amount,
);

if ($isValid) {
    // Payment is valid
} else {
    // Payment is invalid
}
```

You can find more details on the [Wiki](https://github.com/iraziul/sslcommerz-laravel/wiki).

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Raziul Islam](https://github.com/iRaziul)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
