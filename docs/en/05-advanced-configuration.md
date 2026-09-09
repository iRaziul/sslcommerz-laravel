# Advanced Configuration & Customization

The `sslcommerz-laravel` package is designed to be highly flexible, supporting custom metadata, gateway filtering, specific product profiles, and multi-currency operations.

---

## 🏷️ Custom Parameters (`value_a`, `value_b`, `value_c`, `value_d`)

SSLCommerz allows you to attach up to 4 custom string parameters (`value_a`, `value_b`, `value_c`, `value_d`) with every payment request. These values will be returned back in the response callbacks and IPN webhooks unchanged.

You can pass these via the `$additionalData` argument in `makePayment()`:

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

$response = Sslcommerz::setOrder(1500, 'INV-1001', 'Pro Subscription')
    ->setCustomer('John Doe', 'john@example.com')
    ->makePayment([
        'value_a' => (string) auth()->id(),    // User ID
        'value_b' => 'monthly_plan',           // Subscription Plan Code
        'value_c' => 'PROMO20',                // Applied Coupon Code
        'value_d' => tenant('id') ?? 'main',   // Multi-tenant identifier
    ]);
```

### Retrieving Custom Values in Callbacks

```php
public function success(Request $request)
{
    $userId = $request->input('value_a');
    $planCode = $request->input('value_b');
    $coupon = $request->input('value_c');
    $tenantId = $request->input('value_d');
    
    // Process business logic...
}
```

---

## 💳 Gateway Filtering (`setGateways`)

By default, SSLCommerz displays all available payment methods enabled for your merchant account. If you want to restrict or customize which payment gateways appear on the checkout screen (e.g. only bKash and Nagad), use `setGateways()`:

```php
// Only show bKash and Nagad
$response = Sslcommerz::setOrder(500, 'INV-1002', 'Course Enrollment')
    ->setCustomer('Rahim Ali', 'rahim@example.com')
    ->setGateways(['bkash', 'nagad'])
    ->makePayment();
```

### Common Gateway Identifiers in SSLCommerz

| Gateway Code | Description |
| ------------ | ----------- |
| `bkash` | bKash Mobile Banking |
| `nagad` | Nagad Mobile Banking |
| `rocket` | Dutch-Bangla Rocket |
| `upay` | UCB upay |
| `dbbl_nexus` | DBBL Nexus Card |
| `visa` | Visa Cards |
| `master` | Mastercard |
| `amex` | American Express |
| `ibbl` | Islami Bank mCash / Internet Banking |
| `city` | City Bank Internet Banking (Citytouch) |
| `ebl` | Eastern Bank Skybanking |
| `tap` | Trust Axiata TAP |

> [!TIP]
> Pass `null` or omit calling `setGateways()` to display all enabled gateways.

---

## 📦 Product Profiles (`setProductProfile`)

SSLCommerz categorizes transactions using product profiles. By default, the package sets `product_profile` to `'general'`. You can customize this globally in `config/sslcommerz.php` or dynamically per payment:

```php
$response = Sslcommerz::setOrder(2400, 'INV-1003', 'Dhaka to Cox Bazar Flight')
    ->setCustomer('Farhan Karim', 'farhan@example.com')
    ->setProductProfile('airline-tickets')
    ->makePayment();
```

### Supported Profiles

| Profile Name | Typical Use Case |
| ------------ | ---------------- |
| `general` | Default general goods and services |
| `physical-goods` | E-commerce with physical delivery |
| `non-physical-goods` | Digital downloads, software licenses, ebooks |
| `airline-tickets` | Flight tickets, airline bookings |
| `travel-vertical` | Hotel reservations, holiday packages, tours |
| `telecom-vertical` | Mobile recharge, internet bills, utilities |

---

## 💱 Multi-Currency Support

To initiate payments in currencies other than `BDT` (e.g., `USD`, `EUR`, `GBP`), ensure your SSLCommerz merchant account has multi-currency enabled.

### 1. Initiating in Another Currency

Set the currency dynamically in `.env` or pass it during payment creation:

```php
// If your store config is set to USD or if you override:
$response = Sslcommerz::setOrder(
        amount: 25.00, // USD amount
        invoiceId: 'INV-USD-1004',
        productName: 'International Software License'
    )
    ->setCustomer('Sarah Smith', 'sarah@example.com')
    ->makePayment([
        'currency' => 'USD',
    ]);
```

### 2. Validating Non-BDT Transactions

When validating a non-BDT transaction, pass the currency to `validatePayment()`:

```php
$isValid = Sslcommerz::validatePayment(
    payload: $request->all(),
    transactionId: $tranId,
    amount: 25.00,
    currency: 'USD'
);
```

> [!NOTE]
> For `BDT`, the package validates `amount`. For non-BDT currencies (e.g. `USD`), the package checks both `currency_type` and `currency_amount` from SSLCommerz.

---

## 🌐 Dynamic Callback URLs (`setCallbackUrls`)

By default, the package reads callback route names from `config/sslcommerz.php`. If you need custom or tenant-specific callback URLs dynamically for a specific request:

```php
$response = Sslcommerz::setCallbackUrls(
        successUrl: url('/tenant-a/payment/success'),
        failedUrl: url('/tenant-a/payment/failure'),
        cancelUrl: url('/tenant-a/payment/cancel'),
        ipnUrl: url('/api/v1/tenant-a/webhooks/sslcommerz')
    )
    ->setOrder(1000, 'INV-1005', 'Custom URL Order')
    ->setCustomer('Ali', 'ali@example.com')
    ->makePayment();
```

---

## ⏭️ Next Steps

- Explore the complete [API Reference](06-api-reference.md).
- Follow the [Complete Example](07-complete-example.md) to build a production payment flow.
