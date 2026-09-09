<a href="https://github.com/iRaziul/sslcommerz-laravel">
<img style="width: 100%; max-width: 100%;" alt="Sslcommerz Laravel Package" src="/art/sslcommerz-laravel.webp" >
</a>

# SSLCommerz Laravel Package

<p align="center">
    <a href="README.md">🇬🇧 <strong>English</strong></a> •
    <a href="README.bn.md">🇧🇩 বাংলা</a>
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/raziul/sslcommerz-laravel.svg?style=flat-square)](https://packagist.org/packages/raziul/sslcommerz-laravel)
[![Laravel Compatibility](https://badge.laravel.cloud/badge/raziul/sslcommerz-laravel?style=flat)](https://packagist.org/packages/raziul/sslcommerz-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/iRaziul/sslcommerz-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/iRaziul/sslcommerz-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/raziul/sslcommerz-laravel.svg?style=flat-square)](https://packagist.org/packages/raziul/sslcommerz-laravel)
[![License](https://img.shields.io/packagist/l/raziul/sslcommerz-laravel.svg?style=flat-square)](https://packagist.org/packages/raziul/sslcommerz-laravel)

This package provides an elegant and convenient way to integrate the **SSLCommerz** payment gateway into your **Laravel** application. With features like fluent payment initiation, server-side transaction validation, refund processing, and MD5 hash verification, this package offers a clean API for developers to implement payments quickly and securely.

---

## 📑 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Quick Start](#-quick-start)
- [Documentation](#-documentation)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security Vulnerabilities](#security-vulnerabilities)
- [Credits](#credits)
- [License](#license)

---

## ✨ Features

- 🚀 Great Developer Experience with a fluent, chainable API
- 💳 Seamless payment session creation via SSLCommerz Hosted Checkout
- 🔄 Automatic callback routing for Success, Failure, Cancel, and IPN
- 🛡️ Direct server-to-server transaction validation
- 💸 Full & partial refund support with refund status tracking
- 🔐 Response authenticity verification using MD5 digital signatures
- 🧪 Sandbox and Live production environments
- 💱 Multi-currency support (BDT, USD, EUR, GBP, etc.)
- 🎯 Payment gateway filtering (e.g. restrict to bKash, Nagad, Visa)

---

## 📋 Requirements

- **PHP**: `8.2` or higher (`8.2`, `8.3`, `8.4`, `8.5`)
- **Laravel**: `10.x`, `11.x`, `12.x`, `13.x`
- **SSLCommerz Account**: Sandbox or Live Merchant credentials

---

## 📦 Installation

You can install the package via Composer:

```bash
composer require raziul/sslcommerz-laravel
```

Laravel's package discovery will register the service provider and `Sslcommerz` facade automatically.

---

## ⚙️ Configuration

### 1. Publish Configuration

Publish the `config/sslcommerz.php` configuration file:

```bash
php artisan sslcommerz:install
```

### 2. Environment Variables

Add your credentials to `.env`:

```env
SSLC_SANDBOX=true # Set to false for live production
SSLC_STORE_ID=your_store_id
SSLC_STORE_PASSWORD=your_store_password
SSLC_STORE_CURRENCY=BDT
```

---

## 💡 Quick Start

### 1. Initiate a Payment

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

$response = Sslcommerz::setOrder($amount, $invoiceId, 'Order #' . $invoiceId)
    ->setCustomer($name, $email, $phone, $address)
    ->setShippingInfo(1, $address)
    ->makePayment();

if ($response->success()) {
    return redirect($response->gatewayPageURL());
}
```

### 2. Validate the Payment

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

$isValid = Sslcommerz::validatePayment($request->all(), $transactionId, $amount);

if ($isValid) {
    // Payment is authentic and verified
    $order->update(['status' => 'completed']);
}
```

---

## 📖 Documentation

- [Getting Started](docs/en/01-getting-started.md) ([বাংলা](docs/bn/01-getting-started.md))
- [Basic Usage & Workflow](docs/en/02-basic-usage.md) ([বাংলা](docs/bn/02-basic-usage.md))
- [Validation & Security](docs/en/03-validation-and-security.md) ([বাংলা](docs/bn/03-validation-and-security.md))
- [Refunds](docs/en/04-refunds.md) ([বাংলা](docs/bn/04-refunds.md))
- [Advanced Configuration](docs/en/05-advanced-configuration.md) ([বাংলা](docs/bn/05-advanced-configuration.md))
- [API Reference](docs/en/06-api-reference.md) ([বাংলা](docs/bn/06-api-reference.md))
- [Complete Example](docs/en/07-complete-example.md) ([বাংলা](docs/bn/07-complete-example.md))
- [Testing & Troubleshooting](docs/en/08-testing-and-troubleshooting.md) ([বাংলা](docs/bn/08-testing-and-troubleshooting.md))

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Raziul Islam](https://github.com/iRaziul)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
