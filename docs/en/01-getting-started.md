# Getting Started

Welcome to the comprehensive guide for **SSLCommerz Laravel** (`raziul/sslcommerz-laravel`). This package simplifies the integration of Bangladesh's premier payment gateway, **SSLCommerz**, into your Laravel applications.

---

## 📋 Requirements

Before getting started, make sure your environment meets the following requirements:

| Requirement | Supported Versions |
| ----------- | ------------------ |
| **PHP** | `8.2`, `8.3`, `8.4`, `8.5` |
| **Laravel** | `10.x`, `11.x`, `12.x`, `13.x` |
| **PHP Extensions** | `cURL`, `OpenSSL`, `JSON`, `mbstring` |
| **SSLCommerz Account** | Sandbox or Live Store Credentials |

---

## 📦 Installation

Install the package into your Laravel project via Composer:

```bash
composer require raziul/sslcommerz-laravel
```

Laravel's package discovery will automatically register the `Raziul\Sslcommerz\SslcommerzServiceProvider` and the `Sslcommerz` facade alias.

---

## ⚙️ Configuration Setup

### 1. Run the Install Command

You can publish the configuration file using the built-in artisan command:

```bash
php artisan sslcommerz:install
```

> [!NOTE]
> You can also use the alias `php artisan sslcommerz-laravel:install` or standard vendor publish:
> ```bash
> php artisan vendor:publish --tag=sslcommerz-config
> ```

This command publishes `config/sslcommerz.php` to your application's `config` directory.

---

### 2. Environment Variables (`.env`)

Add the following environment variables to your `.env` file:

```env
# SSLCommerz Mode: true for sandbox (testing), false for live (production)
SSLC_SANDBOX=true

# Store Credentials provided by SSLCommerz
SSLC_STORE_ID=your_store_id
SSLC_STORE_PASSWORD=your_store_password

# Default Currency (BDT, USD, EUR, etc.)
SSLC_STORE_CURRENCY=BDT

# Named Routes for SSLCommerz callbacks (Optional, defaults shown below)
SSLC_ROUTE_SUCCESS=sslc.success
SSLC_ROUTE_FAILURE=sslc.failure
SSLC_ROUTE_CANCEL=sslc.cancel
SSLC_ROUTE_IPN=sslc.ipn
```

---

### 3. Understanding `config/sslcommerz.php`

Here is a breakdown of the published configuration file:

```php
return [
    /**
     * Enable/Disable Sandbox mode
     * true  => https://sandbox.sslcommerz.com
     * false => https://securepay.sslcommerz.com
     */
    'sandbox' => env('SSLC_SANDBOX', true),

    /**
     * The API credentials given from SSLCommerz
     */
    'store' => [
        'id' => env('SSLC_STORE_ID'),
        'password' => env('SSLC_STORE_PASSWORD'),
        'currency' => env('SSLC_STORE_CURRENCY', 'BDT'),
    ],

    /**
     * Route names for success/failure/cancel/ipn callbacks
     * The package will automatically resolve these routes via Laravel's route() helper.
     */
    'route' => [
        'success' => env('SSLC_ROUTE_SUCCESS', 'sslc.success'),
        'failure' => env('SSLC_ROUTE_FAILURE', 'sslc.failure'),
        'cancel' => env('SSLC_ROUTE_CANCEL', 'sslc.cancel'),
        'ipn' => env('SSLC_ROUTE_IPN', 'sslc.ipn'),
    ],

    /**
     * Product profile required by SSLCommerz
     * Default: "general"
     *
     * Available profiles:
     * - general
     * - physical-goods
     * - non-physical-goods
     * - airline-tickets
     * - travel-vertical
     * - telecom-vertical
     */
    'product_profile' => 'general',
];
```

---

## 🧪 Getting Sandbox Credentials

To test payments during development, you need SSLCommerz Sandbox credentials:

1. **Register**: Visit the [SSLCommerz Developer Registration](https://developer.sslcommerz.com/registration/) portal and fill out the registration form.
2. **Retrieve Credentials**: After registration, you will receive an email containing:
   - **Store ID** (e.g. `testb64...`)
   - **Store Password** (e.g. `testb64...@ssl`)
3. **Configure**: Paste your `SSLC_STORE_ID` and `SSLC_STORE_PASSWORD` into your `.env` file and set `SSLC_SANDBOX=true`.

> [!IMPORTANT]
> **Production Checklist**:
> When switching from sandbox to live production:
> 1. Set `SSLC_SANDBOX=false` in your production `.env`.
> 2. Set `SSLC_STORE_ID` to your live Merchant Store ID.
> 3. Set `SSLC_STORE_PASSWORD` to your live Merchant Store Password.
> 4. Ensure your domain has an active SSL certificate (HTTPS).
> 5. Ensure your IPN URL is publicly accessible from SSLCommerz servers.

---

## ⏭️ Next Steps

- Proceed to [Basic Usage](02-basic-usage.md) to set up routes, CSRF handling, and initiate your first payment.
- Check [Validation & Security](03-validation-and-security.md) to secure incoming transaction callbacks.
