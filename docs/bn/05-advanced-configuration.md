# অ্যাডভান্সড কনফিগারেশন (Advanced Configuration)

[English Guide](../en/05-advanced-configuration.md) | **বাংলা গাইড**

`sslcommerz-laravel` প্যাকেজটি অত্যন্ত নমনীয়ভাবে তৈরি করা হয়েছে। আপনি চাইলে কাস্টম মেটাডাটা পাঠানো, নির্দিষ্ট পেমেন্ট গেটওয়ে ফিল্টার করা, প্রোডাক্ট প্রোফাইল নির্ধারণ করা এবং মাল্টি-কারেন্সিতে লেনদেন পরিচালনা করতে পারেন।

---

## 🏷️ কাস্টম প্যারামিটার (`value_a`, `value_b`, `value_c`, `value_d`)

SSLCommerz প্রতিটি পেমেন্ট রিকোয়েস্টের সাথে সর্বোচ্চ ৪টি কাস্টম স্ট্রিং প্যারামিটার (`value_a`, `value_b`, `value_c`, `value_d`) পাঠানোর সুযোগ দেয়। পেমেন্ট সম্পন্ন হওয়ার পর কলব্যাক এবং IPN রেসপন্সে এই মানগুলো অপরিবর্তিত অবস্থায় ফেরত আসে।

`makePayment()` মেথডে `$additionalData` অ্যারের মাধ্যমে এগুলো পাঠানো যায়:

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

$response = Sslcommerz::setOrder(1500, 'INV-1001', 'Pro Membership')
    ->setCustomer('করিম রহমান', 'karim@example.com')
    ->makePayment([
        'value_a' => (string) auth()->id(),    // ইউজার আইডি
        'value_b' => 'monthly_plan',           // সাবস্ক্রিপশন প্ল্যান কোড
        'value_c' => 'PROMO20',                // কুপন কোড
        'value_d' => tenant('id') ?? 'main',   // মাল্টি-টেন্যান্ট আইডেন্টিফায়ার
    ]);
```

### কলব্যাকে কাস্টম ডেটা রিসিভ করা

```php
public function success(Request $request)
{
    $userId = $request->input('value_a');
    $planCode = $request->input('value_b');
    $coupon = $request->input('value_c');
    $tenantId = $request->input('value_d');
    
    // বিজনেস লজিক অনুযায়ী কাজ করুন...
}
```

---

## 💳 গেটওয়ে ফিল্টারিং (`setGateways`)

ডিফল্টভাবে SSLCommerz আপনার মার্চেন্ট অ্যাকাউন্টে সক্রিয় থাকা সকল পেমেন্ট অপশন কাস্টমারকে দেখায়। আপনি যদি নির্দিষ্ট কিছু গেটওয়ে (যেমন: শুধু বিকাশ ও নগদ) দেখাতে চান, তবে `setGateways()` মেথড ব্যবহার করুন:

```php
// শুধুমাত্র বিকাশ এবং নগদ প্রদর্শন করতে
$response = Sslcommerz::setOrder(500, 'INV-1002', 'Course Fee')
    ->setCustomer('রহিম আলী', 'rahim@example.com')
    ->setGateways(['bkash', 'nagad'])
    ->makePayment();
```

### পরিচিত গেটওয়ে কোডসমূহ

| গেটওয়ে কোড | বিবরণ |
| ----------- | ------ |
| `bkash` | বিকাশ মোবাইল ব্যাংকিং |
| `nagad` | নগদ মোবাইল ব্যাংকিং |
| `rocket` | ডাচ-বাংলা রকেট |
| `upay` | ইউসিবি উপায় |
| `dbbl_nexus` | ডিবিবিএল নেক্সাস কার্ড |
| `visa` | ভিসা কার্ড |
| `master` | মাস্টারকার্ড |
| `amex` | আমেরিকান এক্সপ্রেস |
| `ibbl` | ইসলামী ব্যাংক ইন্টারনেট ব্যাংকিং / এমক্যাশ |
| `city` | সিটি ব্যাংক সিটিটাচ |
| `ebl` | ইস্টার্ন ব্যাংক স্কাইব্যাংকিং |
| `tap` | ট্রাস্ট আজিয়াটা ট্যাপ |

> [!TIP]
> সকল গেটওয়ে দেখাতে চাইলে `setGateways()` কল করা এড়িয়ে চলুন অথবা প্যারামিটার হিসেবে `null` পাস করুন।

---

## 📦 প্রোডাক্ট প্রোফাইল নির্ধারণ (`setProductProfile`)

SSLCommerz লেনদেনের প্রকৃতি অনুযায়ী বিভিন্ন প্রোডাক্ট প্রোফাইল ব্যবহার করে। প্যাকেজটিতে ডিফল্টভাবে `general` সেট করা থাকে। আপনি `config/sslcommerz.php` ফাইলে অথবা রানটাইমে এটি পরিবর্তন করতে পারেন:

```php
$response = Sslcommerz::setOrder(2400, 'INV-1003', 'ঢাকা থেকে কক্সবাজার বিমান টিকেট')
    ->setCustomer('ফারহান করিম', 'farhan@example.com')
    ->setProductProfile('airline-tickets')
    ->makePayment();
```

### সাপোর্টেড প্রোফাইলসমূহ

| প্রোফাইল নেইম | ব্যবহারের ক্ষেত্র |
| ------------- | ----------------- |
| `general` | সাধারণ পণ্য বা সেবা (ডিফল্ট) |
| `physical-goods` | ফিজিক্যাল ডেলিভারিযুক্ত ই-কমার্স পণ্য |
| `non-physical-goods` | ডিজিটাল ডাউনলোড, সফটওয়্যার লাইসেন্স, ইবুক |
| `airline-tickets` | বিমানের টিকিট ও বুকিং |
| `travel-vertical` | হোটেল বুকিং, হলিডে প্যাকেজ, ট্যুর |
| `telecom-vertical` | মোবাইল রিচার্জ, ইন্টারনেট বিল, ইউটিলিটি বিল |

---

## 💱 মাল্টি-কারেন্সি সাপোর্ট (Multi-Currency)

বাংলাদেশি টাকার (`BDT`) পাশাপাশি অন্যান্য আন্তর্জাতিক কারেন্সিতে (যেমন: `USD`, `EUR`, `GBP`) পেমেন্ট গ্রহণ করতে:

### ১. ভিন্ন কারেন্সিতে পেমেন্ট শুরু করা

```php
$response = Sslcommerz::setOrder(
        amount: 25.00, // ইউএসডি ডলার অ্যামাউন্ট
        invoiceId: 'INV-USD-1004',
        productName: 'International Software License'
    )
    ->setCustomer('Sarah Smith', 'sarah@example.com')
    ->makePayment([
        'currency' => 'USD',
    ]);
```

### ২. নন-বিডিটি পেমেন্ট ভ্যালিডেশন

নন-বিডিটি কারেন্সি ভ্যালিডেশনের সময় কারেন্সি কোড পাস করুন:

```php
$isValid = Sslcommerz::validatePayment(
    payload: $request->all(),
    transactionId: $tranId,
    amount: 25.00,
    currency: 'USD'
);
```

> [!NOTE]
> `BDT`-এর ক্ষেত্রে প্যাকেজটি মূল `amount` মিলিয়ে দেখে। অন্যান্য আন্তর্জাতিক কারেন্সির ক্ষেত্রে এটি SSLCommerz থেকে প্রাপ্ত `currency_type` এবং `currency_amount` উভয়টি যাচাই করে।

---

## 🌐 ডাইনামিক কলব্যাক URL (`setCallbackUrls`)

কনফিগারেশন ফাইলের বাইরে গিয়ে কোনো নির্দিষ্ট রিকোয়েস্টে নিজস্ব কলব্যাক URL ব্যবহার করতে চাইলে:

```php
$response = Sslcommerz::setCallbackUrls(
        successUrl: url('/tenant-a/payment/success'),
        failedUrl: url('/tenant-a/payment/failure'),
        cancelUrl: url('/tenant-a/payment/cancel'),
        ipnUrl: url('/api/v1/tenant-a/webhooks/sslcommerz')
    )
    ->setOrder(1000, 'INV-1005', 'Custom URL Order')
    ->setCustomer('আলী', 'ali@example.com')
    ->makePayment();
```

---

## ⏭️ পরবর্তী ধাপ

- মেথডের বিস্তারিত দেখতে [এপিআই রেফারেন্স](06-api-reference.md) দেখুন।
- বাস্তব প্রজেক্ট কোড দেখতে [সম্পূর্ণ উদাহরণ](07-complete-example.md) দেখুন।
