# API Reference

Complete reference for all classes, methods, parameters, and return types in the `raziul/sslcommerz-laravel` package.

---

## 🏛️ Sslcommerz Facade & SslcommerzClient

Namespace: `\Raziul\Sslcommerz\Facades\Sslcommerz`  
Underlying Client: `\Raziul\Sslcommerz\SslcommerzClient`

### `setOrder()`
Sets the basic order information for the payment session.

```php
public function setOrder(
    int|float $amount,
    string $invoiceId,
    string $productName,
    string $productCategory = ' '
): self
```
- **`$amount`**: Total transaction amount to be charged.
- **`$invoiceId`**: Unique transaction identifier / invoice number.
- **`$productName`**: Name or brief title of the product/service.
- **`$productCategory`**: Category name (e.g. `'Electronics'`, `'Clothing'`). Default is `' '`.
- **Returns**: `$this` (`SslcommerzClient`)

---

### `setCustomer()`
Sets the customer contact and billing information.

```php
public function setCustomer(
    string $name,
    string $email,
    string $phone = ' ',
    string $address = ' ',
    string $city = ' ',
    string $state = ' ',
    string $postal = ' ',
    string $country = 'Bangladesh',
    ?string $fax = null
): self
```
- **`$name`**: Customer's full name.
- **`$email`**: Customer's valid email address.
- **`$phone`**: Customer's phone/mobile number.
- **`$address`**: Customer billing street address.
- **`$city`**: Customer billing city.
- **`$state`**: Customer billing state / division.
- **`$postal`**: Customer postal / ZIP code.
- **`$country`**: Customer country (default `'Bangladesh'`).
- **`$fax`**: Customer fax number (optional, default `null`).
- **Returns**: `$this` (`SslcommerzClient`)

---

### `setShippingInfo()`
Sets the shipping information and delivery address.

```php
public function setShippingInfo(
    int $quantity,
    string $address,
    ?string $name = null,
    ?string $city = null,
    ?string $state = null,
    ?string $postal = null,
    ?string $country = null
): self
```
- **`$quantity`**: Number of items being shipped.
- **`$address`**: Shipping street address.
- **`$name`**: Recipient full name (optional).
- **`$city`**: Shipping city (optional).
- **`$state`**: Shipping state / division (optional).
- **`$postal`**: Shipping postal code (optional).
- **`$country`**: Shipping country (optional).
- **Returns**: `$this` (`SslcommerzClient`)

---

### `setCallbackUrls()`
Overrides the callback URLs dynamically.

```php
public function setCallbackUrls(
    string $successUrl,
    string $failedUrl,
    string $cancelUrl,
    string $ipnUrl
): self
```
- **Returns**: `$this` (`SslcommerzClient`)

---

### `setGateways()`
Restricts the payment gateway options presented on the SSLCommerz payment page.

```php
public function setGateways(array $gateways): self
```
- **`$gateways`**: Array of gateway strings (e.g. `['bkash', 'nagad', 'rocket']`).
- **Returns**: `$this` (`SslcommerzClient`)

---

### `setProductProfile()`
Sets the SSLCommerz product profile.

```php
public function setProductProfile(string $profile): self
```
- **`$profile`**: One of `general`, `physical-goods`, `non-physical-goods`, `airline-tickets`, `travel-vertical`, `telecom-vertical`.
- **Returns**: `$this` (`SslcommerzClient`)

---

### `makePayment()`
Sends the payment initiation request to SSLCommerz API (`/gwprocess/v4/api.php`).

```php
public function makePayment(array $additionalData = []): \Raziul\Sslcommerz\Data\PaymentResponse
```
- **`$additionalData`**: Optional associative array merged with the request payload (e.g. `['value_a' => '123', 'currency' => 'USD']`).
- **Returns**: `PaymentResponse` instance.

---

### `validatePayment()`
Validates a transaction server-to-server against `/validator/api/validationserverAPI.php`.

```php
public function validatePayment(
    array $payload,
    string $transactionId,
    int|float $amount,
    string $currency = 'BDT'
): bool
```
- **`$payload`**: Array containing `val_id` (typically `$request->all()`).
- **`$transactionId`**: Internal transaction/invoice ID to match against SSLCommerz records.
- **`$amount`**: Expected transaction amount.
- **`$currency`**: Expected currency (default `'BDT'`).
- **Returns**: `bool` (`true` if valid and amount matches, `false` otherwise).

---

### `verifyHash()`
Validates the authenticity of the response using MD5 signature verification.

```php
public function verifyHash(array $data): bool
```
- **`$data`**: Callback request data array containing `verify_sign` and `verify_key`.
- **Returns**: `bool` (`true` if valid signature, `false` otherwise).

---

### `refundPayment()`
Initiates a refund request to SSLCommerz.

```php
public function refundPayment(
    string $bankTransactionId,
    int|float $amount,
    string $reason
): \Raziul\Sslcommerz\Data\RefundResponse
```
- **`$bankTransactionId`**: Bank transaction ID (`bank_tran_id`) from payment callback.
- **`$amount`**: Refund amount.
- **`$reason`**: Remarks / reason for refund.
- **Returns**: `RefundResponse` instance.

---

### `checkRefundStatus()`
Queries the status of a previously initiated refund.

```php
public function checkRefundStatus(string $refundRefId): \Raziul\Sslcommerz\Data\RefundStatus
```
- **`$refundRefId`**: Refund Reference ID from previous refund initiation.
- **Returns**: `RefundStatus` instance.

---

## 📦 Data Objects

### `\Raziul\Sslcommerz\Data\PaymentResponse`

| Method | Return Type | Description |
| ------ | ----------- | ----------- |
| `status()` | `?string` | Lowercase response status (e.g. `'success'`, `'failed'`). |
| `success()` | `bool` | `true` if `status()` is `'success'`. |
| `failed()` | `bool` | `true` if failed reason is present. |
| `failedReason()` | `?string` | Reason for failure (`failedreason`). |
| `sessionKey()` | `?string` | SSLCommerz Session Key (`sessionkey`). |
| `gatewayList()` | `?array` | List of available gateways (`gw`). |
| `gatewayPageURL()` | `?string` | Hosted checkout URL to redirect customer to. |
| `redirectGatewayURL()` | `?string` | Direct gateway redirect URL. |
| `directPaymentURLBank()` | `?string` | Direct bank payment URL. |
| `directPaymentURLCard()` | `?string` | Direct card payment URL. |
| `directPaymentURL()` | `?string` | Direct payment URL. |
| `redirectGatewayURLFailed()` | `?string` | Gateway failure redirect URL. |
| `storeBanner()` | `?string` | Store banner URL. |
| `storeLogo()` | `?string` | Store logo URL. |
| `description()` | `?array` | Description array (`desc`). |
| `toArray()` | `?array` | Raw response array from SSLCommerz. |

---

### `\Raziul\Sslcommerz\Data\RefundResponse`

| Method | Return Type | Description |
| ------ | ----------- | ----------- |
| `status()` | `?string` | Lowercase refund response status (`'success'`, `'processing'`, `'failed'`). |
| `success()` | `bool` | `true` if status is `'success'`. |
| `processing()` | `bool` | `true` if status is `'processing'`. |
| `failed()` | `bool` | `true` if status is `'failed'`. |
| `failedReason()` | `?string` | Error reason (`errorReason`). |
| `bankTranId()` | `?string` | Associated Bank Transaction ID (`bank_tran_id`). |
| `transId()` | `?string` | Associated Transaction ID (`trans_id`). |
| `refundRefId()` | `?string` | Unique Refund Reference ID (`refund_ref_id`). |
| `toArray()` | `?array` | Raw response array. |

---

### `\Raziul\Sslcommerz\Data\RefundStatus`

| Method | Return Type | Description |
| ------ | ----------- | ----------- |
| `status()` | `?string` | Lowercase refund status (`'refunded'`, `'processing'`, `'cancelled'`). |
| `refunded()` | `bool` | `true` if status is `'refunded'`. |
| `processing()` | `bool` | `true` if status is `'processing'`. |
| `cancelled()` | `bool` | `true` if status is `'cancelled'`. |
| `reason()` | `?string` | Error or cancellation remarks (`errorReason`). |
| `initiatedAt()` | `?string` | Initiation datetime string (`initiated_on`). |
| `refundedAt()` | `?string` | Refund completed datetime string (`refunded_on`). |
| `bankTranId()` | `?string` | Bank Transaction ID (`bank_tran_id`). |
| `transId()` | `?string` | Transaction ID (`trans_id`). |
| `refundRefId()` | `?string` | Refund Reference ID (`refund_ref_id`). |
| `toArray()` | `?array` | Raw response array. |

---

## ⚠️ Exceptions

### `\Raziul\Sslcommerz\Exceptions\SslcommerzException`

Thrown when SSLCommerz configuration is invalid or missing credentials:
- Thrown when `SSLC_STORE_ID` or `SSLC_STORE_PASSWORD` are missing during client initialization.
- Extends PHP standard `\Exception`.

---

## 💻 Artisan Commands

### `php artisan sslcommerz:install`
Aliases: `php artisan sslcommerz-laravel:install`

- Publishes the `config/sslcommerz.php` configuration file.
- Prompts to star the GitHub repository.
