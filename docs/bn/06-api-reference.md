# এপিআই রেফারেন্স (API Reference)

[English Guide](../en/06-api-reference.md) | **বাংলা গাইড**

`raziul/sslcommerz-laravel` প্যাকেজের সকল ক্লাস, মেথড, প্যারামিটার এবং রিটার্ন টাইপের পূর্ণাঙ্গ রেফারেন্স।

---

## 🏛️ Sslcommerz Facade ও SslcommerzClient

নেমস্পেস: `\Raziul\Sslcommerz\Facades\Sslcommerz`  
মূল ক্লায়েন্ট: `\Raziul\Sslcommerz\SslcommerzClient`

### `setOrder()`
পেমেন্ট সেশনের মূল অর্ডারের তথ্য নির্ধারণ করে।

```php
public function setOrder(
    int|float $amount,
    string $invoiceId,
    string $productName,
    string $productCategory = ' '
): self
```
- **`$amount`**: পরিশোধযোগ্য মোট টাকার পরিমাণ।
- **`$invoiceId`**: ইউনিক ইনভয়েস নম্বর বা ট্রানজ্যাকশন আইডি।
- **`$productName`**: পণ্যের নাম বা সংক্ষিপ্ত বিবরণ।
- **`$productCategory`**: পণ্যের ক্যাটাগরি (ডিফল্ট `' '`)।
- **রিটার্ন**: `$this` (`SslcommerzClient`)

---

### `setCustomer()`
কাস্টমারের তথ্য ও ঠিকানা নির্ধারণ করে।

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
- **`$name`**: কাস্টমারের নাম।
- **`$email`**: কাস্টমারের ইমেইল অ্যাড্রেস।
- **`$phone`**: ফোন বা মোবাইল নম্বর।
- **`$address`**: ঠিকানা।
- **`$city`**: শহর।
- **`$state`**: জেলা বা বিভাগ।
- **`$postal`**: পোস্টকোড।
- **`$country`**: দেশ (ডিফল্ট `'Bangladesh'`)।
- **`$fax`**: ফ্যাক্স নম্বর (ঐচ্ছিক)।
- **রিটার্ন**: `$this` (`SslcommerzClient`)

---

### `setShippingInfo()`
পণ্য ডেলিভারি বা শিপিংয়ের তথ্য নির্ধারণ করে।

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
- **`$quantity`**: আইটেম সংখ্যা।
- **`$address`**: শিপিং ঠিকানা।
- **`$name`**: প্রাপকের নাম (ঐচ্ছিক)।
- **`$city`**: শিপিং শহর (ঐচ্ছিক)।
- **`$state`**: শিপিং বিভাগ (ঐচ্ছিক)।
- **`$postal`**: শিপিং পোস্টকোড (ঐচ্ছিক)।
- **`$country`**: দেশ (ঐচ্ছিক)।
- **রিটার্ন**: `$this` (`SslcommerzClient`)

---

### `setCallbackUrls()`
রানটাইমে কলব্যাক URL সমূহ পরিবর্তন করতে ব্যবহৃত হয়।

```php
public function setCallbackUrls(
    string $successUrl,
    string $failedUrl,
    string $cancelUrl,
    string $ipnUrl
): self
```
- **রিটার্ন**: `$this` (`SslcommerzClient`)

---

### `setGateways()`
SSLCommerz পেজে প্রদর্শিত পেমেন্ট গেটওয়ের তালিকা সীমিত বা ফিল্টার করে।

```php
public function setGateways(array $gateways): self
```
- **`$gateways`**: গেটওয়ে কোডের অ্যারে (যেমন: `['bkash', 'nagad', 'rocket']`)।
- **রিটার্ন**: `$this` (`SslcommerzClient`)

---

### `setProductProfile()`
SSLCommerz প্রোডাক্ট প্রোফাইল নির্ধারণ করে।

```php
public function setProductProfile(string $profile): self
```
- **`$profile`**: প্রোফাইল স্ট্রিং (`general`, `physical-goods`, `non-physical-goods`, `airline-tickets`, `travel-vertical`, `telecom-vertical`)।
- **রিটার্ন**: `$this` (`SslcommerzClient`)

---

### `makePayment()`
SSLCommerz-এ পেমেন্ট রিকোয়েস্ট পাঠায় (`/gwprocess/v4/api.php`)।

```php
public function makePayment(array $additionalData = []): \Raziul\Sslcommerz\Data\PaymentResponse
```
- **`$additionalData`**: অতিরিক্ত প্যারামিটার অ্যারে (যেমন: `['value_a' => '123', 'currency' => 'USD']`)।
- **রিটার্ন**: `PaymentResponse` অবজেক্ট।

---

### `validatePayment()`
সরাসরি সার্ভার-টু-সার্ভার পেমেন্টের সত্যতা যাচাই করে।

```php
public function validatePayment(
    array $payload,
    string $transactionId,
    int|float $amount,
    string $currency = 'BDT'
): bool
```
- **`$payload`**: `val_id` যুক্ত অ্যারে (সাধারণত `$request->all()`)।
- **`$transactionId`**: ডাটাবেজের ট্রানজ্যাকশন/ইনভয়েস আইডি।
- **`$amount`**: প্রত্যাশিত টাকার পরিমাণ।
- **`$currency`**: কারেন্সি (ডিফল্ট `'BDT'`)।
- **রিটার্ন**: `bool` (বৈধ ও টাকার পরিমাণ মিললে `true`, অন্যথায় `false`)।

---

### `verifyHash()`
রেসপন্সের ডিজিটাল সিগনেচার যাচাই করে।

```php
public function verifyHash(array $data): bool
```
- **`$data`**: `verify_sign` এবং `verify_key` যুক্ত কলব্যাক ডেটা।
- **রিটার্ন**: `bool` (বৈধ হলে `true`, অন্যথায় `false`)।

---

### `refundPayment()`
SSLCommerz-এ রিফান্ড রিকোয়েস্ট পাঠায়।

```php
public function refundPayment(
    string $bankTransactionId,
    int|float $amount,
    string $reason
): \Raziul\Sslcommerz\Data\RefundResponse
```
- **`$bankTransactionId`**: ব্যাংক ট্রানজ্যাকশন আইডি (`bank_tran_id`)।
- **`$amount`**: রিফান্ডের টাকার পরিমাণ।
- **`$reason`**: রিফান্ডের কারণ বা মন্তব্য।
- **রিটার্ন**: `RefundResponse` অবজেক্ট।

---

### `checkRefundStatus()`
পূর্বে শুরু করা রিফান্ডের বর্তমান অবস্থা যাচাই করে।

```php
public function checkRefundStatus(string $refundRefId): \Raziul\Sslcommerz\Data\RefundStatus
```
- **`$refundRefId`**: রিফান্ড রেফারেন্স আইডি।
- **রিটার্ন**: `RefundStatus` অবজেক্ট।

---

## 📦 ডাটা অবজেক্টসমূহ (Data Objects)

### `\Raziul\Sslcommerz\Data\PaymentResponse`

| মেথড | রিটার্ন টাইপ | বিবরণ |
| ---- | ------------ | ------ |
| `status()` | `?string` | রেসপন্স স্ট্যাটাস (যেমন: `'success'`, `'failed'`)। |
| `success()` | `bool` | স্ট্যাটাস `'success'` হলে `true`। |
| `failed()` | `bool` | ব্যর্থতার কারণ উপস্থিত থাকলে `true`। |
| `failedReason()` | `?string` | ব্যর্থ হওয়ার কারণ (`failedreason`)। |
| `sessionKey()` | `?string` | SSLCommerz সেশন কী (`sessionkey`)। |
| `gatewayList()` | `?array` | সক্রিয় গেটওয়ের তালিকা (`gw`)। |
| `gatewayPageURL()` | `?string` | কাস্টমারকে পাঠানোর জন্য পেমেন্ট পেজের URL। |
| `redirectGatewayURL()` | `?string` | গেটওয়ে রিডাইরেক্ট URL। |
| `directPaymentURLBank()` | `?string` | সরাসরি ব্যাংক পেমেন্ট URL। |
| `directPaymentURLCard()` | `?string` | সরাসরি কার্ড পেমেন্ট URL। |
| `directPaymentURL()` | `?string` | ডিরেক্ট পেমেন্ট URL। |
| `redirectGatewayURLFailed()` | `?string` | গেটওয়ে ফেইল্ড রিডাইরেক্ট URL। |
| `storeBanner()` | `?string` | স্টোর ব্যানার ইমেজ URL। |
| `storeLogo()` | `?string` | স্টোর লোগো ইমেজ URL। |
| `description()` | `?array` | ডেসক্রিপশন অ্যারে (`desc`)। |
| `toArray()` | `?array` | মূল রেসপন্স অ্যারে। |

---

### `\Raziul\Sslcommerz\Data\RefundResponse`

| মেথড | রিটার্ন টাইপ | বিবরণ |
| ---- | ------------ | ------ |
| `status()` | `?string` | রিফান্ডের প্রাথমিক স্ট্যাটাস (`'success'`, `'processing'`, `'failed'`)। |
| `success()` | `bool` | স্ট্যাটাস `'success'` হলে `true`। |
| `processing()` | `bool` | স্ট্যাটাস `'processing'` হলে `true`। |
| `failed()` | `bool` | স্ট্যাটাস `'failed'` হলে `true`। |
| `failedReason()` | `?string` | ব্যর্থতার কারণ (`errorReason`)। |
| `bankTranId()` | `?string` | ব্যাংক ট্রানজ্যাকশন আইডি (`bank_tran_id`)। |
| `transId()` | `?string` | অর্ডার ট্রানজ্যাকশন আইডি (`trans_id`)। |
| `refundRefId()` | `?string` | ইউনিক রিফান্ড রেফারেন্স আইডি (`refund_ref_id`)। |
| `toArray()` | `?array` | মূল রেসপন্স অ্যারে। |

---

### `\Raziul\Sslcommerz\Data\RefundStatus`

| মেথড | রিটার্ন টাইপ | বিবরণ |
| ---- | ------------ | ------ |
| `status()` | `?string` | রিফান্ডের বর্তমান স্ট্যাটাস (`'refunded'`, `'processing'`, `'cancelled'`)। |
| `refunded()` | `bool` | স্ট্যাটাস `'refunded'` হলে `true`। |
| `processing()` | `bool` | স্ট্যাটাস `'processing'` হলে `true`। |
| `cancelled()` | `bool` | স্ট্যাটাস `'cancelled'` হলে `true`। |
| `reason()` | `?string` | ব্যর্থতা বা বাতিলের কারণ (`errorReason`)। |
| `initiatedAt()` | `?string` | রিফান্ড শুরুর তারিখ ও সময় (`initiated_on`)। |
| `refundedAt()` | `?string` | রিফান্ড সমাপ্তির তারিখ ও সময় (`refunded_on`)। |
| `bankTranId()` | `?string` | ব্যাংক ট্রানজ্যাকশন আইডি (`bank_tran_id`)। |
| `transId()` | `?string` | ট্রানজ্যাকশন আইডি (`trans_id`)। |
| `refundRefId()` | `?string` | রিফান্ড রেফারেন্স আইডি (`refund_ref_id`)। |
| `toArray()` | `?array` | মূল রেসপন্স অ্যারে। |

---

## ⚠️ এক্সেপশন (Exceptions)

### `\Raziul\Sslcommerz\Exceptions\SslcommerzException`

কনফিগারেশনে স্টোর আইডি বা পাসওয়ার্ড না থাকলে এই এক্সেপশনটি ঘটে:
- `SSLC_STORE_ID` অথবা `SSLC_STORE_PASSWORD` অনুপস্থিত থাকলে ক্লায়েন্ট ইনিশিয়ালাইজেশনের সময় এই এরর তৈরি হয়।
- এটি PHP-এর আদর্শ `\Exception` ক্লাস এক্সটেন্ড করে।

---

## 💻 আর্টিসান কমান্ডসমূহ (Artisan Commands)

### `php artisan sslcommerz:install`
উপনাম (Alias): `php artisan sslcommerz-laravel:install`

- `config/sslcommerz.php` কনফিগারেশন ফাইল পাবলিশ করে।
- গিটহাব রিপোজিটরিতে স্টার দেওয়ার জন্য অনুরোধ করে।
