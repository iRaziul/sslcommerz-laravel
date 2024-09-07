<a href="https://github.com/iraziul/sslcommerz-laravel">
<img style="width: 100%; max-width: 100%;" alt="Sslcommerz Laravel Package" src="/art/sslcommerz laravel.png" >
</a>

# Sslcommerz Laravel Package

This package provides an easy and convenient way to integrate **SSLCommerz** payment gateway into your **Laravel** application. With features like payment processing, payment validation, refunds, and hash verification, this package offers a simple API for developers to quickly implement payment functionality.

## 🔥 Features

-   Great Developer Experience
-   Initiate payments via SSLCommerz
-   Set callback URLs for success, failure, cancellation and IPN
-   Validate payment transactions
-   Refund payments and check refund status
-   Verify hash from SSLCommerz responses
-   Sandbox and live environment support

## Requirements

-   PHP 8.2 or above
-   Laravel 10.0 or above
-   SSLCommerz Credentials

## Installation

You can install the package via Composer:

```bash
composer require raziul/sslcommerz-laravel
```

Once installed, the service provider will be registered automatically.

## Configuration

Add the following environment variables to your `.env` file:

```bash
SSLC_SANDBOX=true # or false for live
SSLC_STORE_ID=your_store_id
SSLC_STORE_PASSWORD=your_store_password
SSLC_STORE_CURRENCY='BDT'

# SSLCommerz route names (optional)
SSLC_ROUTE_SUCCESS='sslc.success'
SSLC_ROUTE_FAILURE='sslc.failure'
SSLC_ROUTE_CANCEL='sslc.cancel'
SSLC_ROUTE_IPN='sslc.ipn'
```

Optionally, You can publish the configuration file using the following command:

```bash
php artisan sslcommerz-laravel:install
```

This will publish the `sslcommerz.php` file to your `config` directory.

### ✨ Getting Sandbox Credentials

SSLCommerz credentials are required to use this package. You can get sandbox credentials by following these steps:

1. **Create Sandbox Account**: Visit the [https://developer.sslcommerz.com/registration/](https://developer.sslcommerz.com/registration/) page to create an account.

2. **Obtain Credentials:** After registration, you will receive your **Store ID** and **Store Password** via email or from the SSLCommerz dashboard.

3. **Set Up in .env:** Copy these credentials and paste them into your `.env` file as shown in the [Configuration](#configuration) step.

> [!IMPORTANT]
> Sandbox credentials are for testing purposes only. You should replace them with your live credentials and change SANDBOX=false before deploying to production.

## 💡 Usage

### 1. **Defining Callback Routes**

To handle different stages of the payment lifecycle, define your callback routes for success, failure, cancellation, and IPN (Instant Payment Notification):

```php
Route::controller(SslcommerzController::class)
    ->prefix('sslcommerz') // prefix to avoid conflicts
    ->name('sslc.')
    ->group(function () {
        Route::get('success', 'success')->name('success');
        Route::get('failure', 'failure')->name('failure');
        Route::get('cancel', 'cancel')->name('cancel');
        Route::get('ipn', 'ipn')->name('ipn');
    });
```

We defined the `sslc.success`, `sslc.failure`, `sslc.cancel`, and `sslc.ipn` routes according to the configured route names.
Now create the `SslcommerzController` controller and implement the `success`, `failure`, `cancel`, and `ipn` methods as required.

### 2. **Initiating a Payment**

Initiating a payment has never been easier. For example, you can use the following code:

```php
use \Raziul\Sslcommerz\Facades\Sslcommerz;

$response = Sslcommerz::setOrder($amount, $invoiceId, $productName)
    ->setCustomer($customerName, $customerEmail, $customerPhone)
    ->setShippingInfo($itemsQuantity, $address)
    ->makePayment();

if ($response->success()) {
    // payment initiated, redirect to payment page
    return redirect($response->gatewayPageURL());
} else {
    // Handle payment failure
}
```

The `makePayment` method returns an instance of `Raziul\Sslcommerz\Data\PaymentResponse` class. Check the [available methods](https://github.com/iRaziul/sslcommerz-laravel/wiki/PaymentResponse) for more details.

### 3. **Validating a Payment**

To validate a payment after a successful transaction:

```php
use \Raziul\Sslcommerz\Facades\Sslcommerz;

$isValid = Sslcommerz::validatePayment($requestData, $transactionId, $amount);

if ($isValid) {
    // Payment is valid
} else {
    // Payment is invalid
}
```

### 4. **Refunds**

If you need to refund a payment, you can do so easily:

```php
$refundResponse = Sslcommerz::refundPayment($bankTransactionId, $amount, $reason);
```

You can also check the refund status:

```php
$refundStatus = Sslcommerz::checkRefundStatus($refundRefId);
```

### 5. **Hash Verification**

To verify the authenticity of the response from SSLCommerz, use the `verifyHash` method:

```php
if (Sslcommerz::verifyHash($request->all())) {
    // Hash is valid
}
```

## 📖 Documentation

You can find detailed documentation, guides and examples on the [Wiki](https://github.com/iraziul/sslcommerz-laravel/wiki).

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
