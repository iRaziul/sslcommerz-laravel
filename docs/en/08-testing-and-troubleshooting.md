# Testing & Troubleshooting

This guide provides test cards, mobile banking simulator details, local webhook testing instructions, and solutions to common errors encountered during integration.

---

## 💳 Sandbox Test Credentials

SSLCommerz provides a test environment with simulated cards and mobile financial services (MFS).

### Test Cards (Visa, Mastercard, Amex)

| Card Type | Card Number | Expiry Date | CVV | OTP / PIN |
| --------- | ----------- | ----------- | --- | --------- |
| **Visa (Success)** | `4111 1111 1111 1111` | Any future date (e.g. `12/28`) | `123` | `123456` or any 4-6 digits |
| **Mastercard (Success)** | `5105 1051 0510 5100` | Any future date (e.g. `12/28`) | `123` | `123456` |
| **Amex (Success)** | `3782 8224 6310 005` | Any future date (e.g. `12/28`) | `1234` | `123456` |
| **Visa (Fail / Decline)** | `4000 0000 0000 0002` | Any future date | `123` | N/A (Simulates card decline) |

---

### Mobile Banking Test Accounts (bKash, Nagad, Rocket)

In Sandbox mode, mobile banking simulations do not deduct real money:

| Gateway | Test Wallet Number | Test OTP | Test PIN |
| ------- | ------------------ | -------- | -------- |
| **bKash** | Any valid 11-digit number (e.g. `01711111111`) | `123456` | `12345` |
| **Nagad** | Any valid 11-digit number (e.g. `01811111111`) | `123456` | `1234` |
| **Rocket** | Any valid 12-digit number (e.g. `019111111111`) | `1234` | `12345` |
| **Upay** | Any valid 11-digit number (e.g. `01611111111`) | `123456` | `1234` |

---

## 🌐 Testing IPN / Webhooks on Localhost

SSLCommerz servers cannot send HTTP POST requests directly to `http://localhost:8000`. To test IPN webhooks during local development:

### Using Ngrok

1. Start your Laravel application:
   ```bash
   php artisan serve
   ```
2. In a separate terminal, expose port 8000 using ngrok:
   ```bash
   ngrok http 8000
   ```
3. Copy the secure forwarding URL (e.g. `https://a1b2-3c4d.ngrok-free.app`).
4. Update your `APP_URL` in `.env`:
   ```env
   APP_URL=https://a1b2-3c4d.ngrok-free.app
   ```
5. SSLCommerz will now successfully send IPN requests to your local application.

---

## 🛠️ Common Issues & Troubleshooting

### 1. `419 Page Expired` on Redirect
- **Cause**: Laravel's CSRF / `PreventRequestForgery` (Laravel 13) middleware blocks external POST requests from SSLCommerz.
- **Solution**: Exempt the callback routes:
  ```php
  // Laravel 13 (bootstrap/app.php)
  $middleware->preventRequestForgery(except: ['sslcommerz/*']);

  // Laravel 11 & 12 (bootstrap/app.php)
  $middleware->validateCsrfTokens(except: ['sslcommerz/*']);

  // Laravel 10 (app/Http/Middleware/VerifyCsrfToken.php)
  protected $except = ['sslcommerz/*'];
  ```

---

### 2. `SslcommerzException: SSLCommerz store credentials are not set.`
- **Cause**: `SSLC_STORE_ID` or `SSLC_STORE_PASSWORD` is empty or missing in your `.env`.
- **Solution**: Add valid store credentials to `.env` and run:
  ```bash
  php artisan config:clear
  ```

---

### 3. `Route [sslc.success] not defined.`
- **Cause**: The package tries to resolve route names configured in `config/sslcommerz.php`, but those named routes are missing in `routes/web.php`.
- **Solution**: Ensure your routes have the matching names:
  ```php
  Route::post('success', [PaymentController::class, 'success'])->name('sslc.success');
  ```
  Or change the route names in `config/sslcommerz.php` to match your existing routes.

---

### 4. `validatePayment()` Returns `false`
- **Check 1: `val_id` Missing**: Ensure `$request->input('val_id')` is present in the callback payload.
- **Check 2: Amount Mismatch**: Make sure the amount passed to `validatePayment()` matches the order amount in your database.
- **Check 3: Currency**: If charging in non-BDT currencies (e.g. `USD`), pass the currency parameter:
  ```php
  Sslcommerz::validatePayment($payload, $tranId, $amount, 'USD');
  ```
- **Check 4: Store Credentials**: Ensure the credentials used to initiate the payment match the credentials in `.env`.

---

### 5. `cURL error 60: SSL certificate problem` (Local Development)
- **Note**: The package's internal client uses `Http::withoutVerifying()` to avoid local SSL certificate issues on development machines. However, on production servers, always ensure cURL CA certificates are properly updated in your PHP installation.

---

### 6. Empty Customer or Shipping Information Errors
- **Cause**: SSLCommerz requires certain mandatory customer fields (`cus_name`, `cus_email`, `cus_phone`, `cus_add1`).
- **Solution**: Always provide non-empty values for `setCustomer()`:
  ```php
  Sslcommerz::setCustomer(
      name: $user->name ?? 'Customer',
      email: $user->email ?? 'customer@example.com',
      phone: $user->phone ?? '01700000000',
      address: $user->address ?? 'Dhaka, Bangladesh'
  );
  ```
