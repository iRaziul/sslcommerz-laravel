# Validation & Security

Validating incoming payment transactions is the most critical step in any payment gateway integration. Never mark an order as paid purely based on browser redirects or unverified POST payloads.

---

## 🔒 Why Validation is Mandatory

When a customer completes a payment:
1. The customer's browser is redirected to your `success_url` via a POST request with transaction data.
2. A malicious user could potentially forge an HTTP POST request to your `/sslcommerz/success` endpoint with fake parameters.
3. A network disruption could terminate the user's connection before the redirect reaches your server.

To prevent fraud and transaction discrepancies, **SSLCommerz provides two security mechanisms**:
1. **Server-Side Transaction Validation** (`validatePayment`)
2. **MD5 Hash Verification** (`verifyHash`)

---

## 🛡️ 1. Server-Side Validation (`validatePayment`)

The `validatePayment()` method makes a direct, secure server-to-server API call from your Laravel backend to SSLCommerz's validation endpoint (`/validator/api/validationserverAPI.php`) to confirm that:
- The `val_id` is legitimate.
- The transaction status is not `INVALID_TRANSACTION`.
- The `tran_id` matches your database record.
- The charged `amount` and `currency` match your expected order amount.

### Syntax

```php
$isValid = Sslcommerz::validatePayment(
    array $payload,       // Array containing 'val_id' (usually $request->all())
    string $transactionId,// Your internal order transaction ID (e.g. 'INV-12345')
    int|float $amount,    // Expected order total amount
    string $currency = 'BDT' // Expected currency (default 'BDT')
);
```

### Usage Example

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

public function success(Request $request)
{
    $tranId = $request->input('tran_id');
    $order = Order::where('transaction_id', $tranId)->firstOrFail();

    // Perform server-side validation against SSLCommerz API
    $isValid = Sslcommerz::validatePayment(
        payload: $request->all(),
        transactionId: $order->transaction_id,
        amount: $order->amount,
        currency: $order->currency // e.g. 'BDT' or 'USD'
    );

    if ($isValid) {
        // Payment is confirmed authentic and amount matches
        $order->update(['status' => 'completed']);
    } else {
        // Transaction is invalid, tampered, or amount mismatched
        $order->update(['status' => 'validation_failed']);
    }
}
```

---

## 🔑 2. Hash Verification (`verifyHash`)

SSLCommerz includes a `verify_sign` (MD5 signature) and `verify_key` in callback payloads. The package automatically verifies that the payload has not been tampered with in transit.

### How it Works Internally

1. SSLCommerz generates an MD5 hash of your `md5(store_passwd)` combined with the response parameters specified in `verify_key`.
2. The package sorts the keys alphabetically, creates the query string, computes the MD5 checksum, and matches it with `verify_sign`.

### Syntax

```php
$isAuthentic = Sslcommerz::verifyHash(array $data);
```

### Usage Example

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

public function ipn(Request $request)
{
    // 1. Verify that the request came from SSLCommerz and payload is untampered
    if (! Sslcommerz::verifyHash($request->all())) {
        logger()->warning('SSLCommerz IPN hash verification failed', $request->all());
        return response()->json(['error' => 'Invalid signature'], 403);
    }

    // 2. Perform server validation
    $tranId = $request->input('tran_id');
    $order = Order::where('transaction_id', $tranId)->first();

    if ($order && Sslcommerz::validatePayment($request->all(), $tranId, $order->amount)) {
        $order->update(['status' => 'completed']);
        return response()->json(['message' => 'IPN Verified']);
    }

    return response()->json(['error' => 'Validation failed'], 400);
}
```

---

## ⚡ Handling Race Conditions & Idempotency

Both the **User Redirect** (`success` route) and the **IPN Webhook** (`ipn` route) may attempt to update the order status at roughly the same time.

To ensure idempotency and prevent duplicate fulfillment (e.g. sending two confirmation emails or double crediting a user balance):

### Recommended Pattern (Database Transactions & Locking)

```php
use Illuminate\Support\Facades\DB;

public function processSuccessfulPayment(Request $request)
{
    $tranId = $request->input('tran_id');

    return DB::transaction(function () use ($request, $tranId) {
        // Acquire row lock to prevent race conditions
        $order = Order::where('transaction_id', $tranId)->lockForUpdate()->first();

        if (! $order) {
            return false;
        }

        // Check if already marked as completed by either IPN or redirect
        if ($order->status === 'completed') {
            return true;
        }

        // Validate payment
        if (Sslcommerz::validatePayment($request->all(), $order->transaction_id, $order->amount, $order->currency)) {
            $order->update([
                'status' => 'completed',
                'val_id' => $request->input('val_id'),
                'bank_tran_id' => $request->input('bank_tran_id'),
                'card_type' => $request->input('card_type'),
                'paid_at' => now(),
            ]);

            // Dispatch order fulfillment events (e.g. SendOrderInvoiceEmail, UpdateInventory)
            event(new OrderPaid($order));

            return true;
        }

        return false;
    });
}
```

---

## 🔒 Security Best Practices Summary

| Practice | Details |
| -------- | ------- |
| **Never Trust User Input** | Always call `validatePayment()` to verify status and amount directly with SSLCommerz. |
| **Verify Currency & Amount** | Always pass your stored order amount to `validatePayment()`, not the amount received in the request. |
| **Use HTTPS** | Ensure all callback URLs use `https://` in production. |
| **Exempt CSRF Carefully** | Only exempt the specific route prefix (e.g. `sslcommerz/*`) from CSRF verification. |
| **Verify Hash on IPN** | Always call `verifyHash($request->all())` before processing IPN notifications. |
| **Idempotency** | Handle multiple callbacks gracefully without double-charging or duplicate fulfillment. |

---

## ⏭️ Next Steps

- Explore [Refunds](04-refunds.md) to manage full or partial customer refunds.
- Learn about advanced options like gateway filtering and custom parameters in [Advanced Configuration](05-advanced-configuration.md).
