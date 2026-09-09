# পেমেন্ট ভ্যালিডেশন ও সিকিউরিটি (Validation & Security)

[English Guide](../en/03-validation-and-security.md) | **বাংলা গাইড**

পেমেন্ট গেটওয়ে ইন্টিগ্রেশনে সবচেয়ে গুরুত্বপূর্ণ ধাপ হলো লেনদেনের সত্যতা যাচাই (Transaction Validation)। কেবল ব্রাউজারের রিডাইরেক্ট বা আনভেরিফাইড POST ডেটার উপর নির্ভর করে কখনোই কোনো অর্ডারকে পেইড (Paid) হিসেবে চিহ্নিত করবেন না।

---

## 🔒 পেমেন্ট ভ্যালিডেশন কেন বাধ্যতামূলক?

কাস্টমার যখন পেমেন্ট সম্পন্ন করেন:
১. কাস্টমারের ব্রাউজার থেকে আপনার `success` রাউটে একটি POST রিকোয়েস্টের মাধ্যমে রিডাইরেক্ট হয়।
২. যেকোনো হ্যাকার বা অসৎ ব্যবহারকারী ব্রাউজার থেকে ফেক (নকল) ডেটা পাঠিয়ে POST রিকোয়েস্ট তৈরি করতে পারে।
৩. ইন্টারনেট বিভ্রাটের কারণে কাস্টমারের রিডাইরেক্ট আপনার সার্ভারে না-ও পৌঁছাতে পারে।

নিরাপত্তা নিশ্চিত করতে এবং জালিয়াতি প্রতিরোধে **SSLCommerz দুটি সিকিউরিটি ব্যবস্থা প্রদান করে**:
১. **সার্ভার-টু-সার্ভার ভ্যালিডেশন** (`validatePayment`)
২. **MD5 হ্যাশ ভেরিফিকেশন** (`verifyHash`)

---

## 🛡️ ১. সার্ভার ভ্যালিডেশন (`validatePayment`)

`validatePayment()` মেথডটি আপনার সার্ভার থেকে সরাসরি SSLCommerz-এর ভ্যালিডেশন সার্ভারে (`/validator/api/validationserverAPI.php`) রিকোয়েস্ট পাঠায় এবং নিশ্চিত করে:
- `val_id` সঠিক এবং বিদ্যমান।
- ট্রানজ্যাকশন স্ট্যাটাস `INVALID_TRANSACTION` নয়।
- SSLCommerz-এর রেকর্ডে থাকা `tran_id` আপনার ডাটাবেজের অর্ডারের সাথে মিল রয়েছে।
- পরিশোধিত টাকার পরিমাণ (`amount`) এবং কারেন্সি (`currency`) আপনার সিস্টেমের নির্ধারিত মূল্যের সমান।

### মেথড সিনট্যাক্স

```php
$isValid = Sslcommerz::validatePayment(
    array $payload,       // 'val_id' সম্বলিত অ্যারে (যেমন: $request->all())
    string $transactionId,// আপনার অর্ডারের ইউনিক ট্রানজ্যাকশন আইডি (যেমন: 'INV-12345')
    int|float $amount,    // অর্ডারের মোট মূল্যের পরিমাণ
    string $currency = 'BDT' // কারেন্সি (ডিফল্ট 'BDT')
);
```

### ব্যবহারিক উদাহরণ

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

public function success(Request $request)
{
    $tranId = $request->input('tran_id');
    $order = Order::where('transaction_id', $tranId)->firstOrFail();

    // SSLCommerz API-এর মাধ্যমে সরাসরি যাচাই করুন
    $isValid = Sslcommerz::validatePayment(
        payload: $request->all(),
        transactionId: $order->transaction_id,
        amount: $order->amount,
        currency: $order->currency // যেমন: 'BDT' অথবা 'USD'
    );

    if ($isValid) {
        // পেমেন্ট শতভাগ সঠিক এবং নিশ্চিত
        $order->update(['status' => 'completed']);
    } else {
        // পেমেন্ট অবৈধ বা তথ্যে গরমিল রয়েছে
        $order->update(['status' => 'validation_failed']);
    }
}
```

---

## 🔑 ২. হ্যাশ ভেরিফিকেশন (`verifyHash`)

SSLCommerz রেসপন্সের সাথে একটি ডিজিটাল সিগনেচার `verify_sign` এবং কী তালিকা `verify_key` পাঠায়। এই প্যাকেজটি স্বয়ংক্রিয়ভাবে যাচাই করে যে ট্রানজ্যাকশনের ডেটায় মাঝপথে কোনো পরিবর্তন করা হয়েছে কি না।

### এটি যেভাবে কাজ করে

১. প্যাকেজটি আপনার `store_passwd`-এর MD5 হ্যাশ এবং রেসপন্সে আসা প্যারামিটারগুলোকে বর্ণানুক্রমিকভাবে (Alphabetically) সাজায়।
২. সাজানো প্যারামিটারগুলো দিয়ে একটি MD5 হ্যাশ তৈরি করে রেসপন্সের `verify_sign`-এর সাথে মিলিয়ে দেখে।

### মেথড সিনট্যাক্স

```php
$isAuthentic = Sslcommerz::verifyHash(array $data);
```

### ব্যবহারিক উদাহরণ

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

public function ipn(Request $request)
{
    // ১. সিগনেচার যাচাই করুন যে রিকোয়েস্টটি প্রকৃতপক্ষেই SSLCommerz থেকে এসেছে
    if (! Sslcommerz::verifyHash($request->all())) {
        logger()->warning('SSLCommerz IPN hash verification failed', $request->all());
        return response()->json(['error' => 'Invalid signature'], 403);
    }

    // ২. সার্ভার ভ্যালিডেশন সম্পন্ন করুন
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

## ⚡ ডাবল প্রসেসিং ও রেস কন্ডিশন প্রতিরোধ (Idempotency)

একই অর্ডারের জন্য **ইউজার রিডাইরেক্ট** (`success` রাউট) এবং **আইপিএন ওয়েবহুক** (`ipn` রাউট) প্রায় একই সময়ে আপনার সার্ভারে রিকোয়েস্ট পাঠাতে পারে।

যাতে একই অর্ডারের জন্য একাধিকবার কনফার্মেশন ইমেইল বা দুইবার ব্যালেন্স যোগ না হয়, সেজন্য ডাটাবেজ ট্রানজ্যাকশন এবং পেসিমিস্টিক লকিং (`lockForUpdate()`) ব্যবহার করা উত্তম:

```php
use Illuminate\Support\Facades\DB;

public function processSuccessfulPayment(Request $request)
{
    $tranId = $request->input('tran_id');

    return DB::transaction(function () use ($request, $tranId) {
        // রেস কন্ডিশন ঠেকাতে ডাটাবেজ রো লক করুন
        $order = Order::where('transaction_id', $tranId)->lockForUpdate()->first();

        if (! $order) {
            return false;
        }

        // অর্ডারটি যদি ইতিমধ্যে সফল হয়ে থাকে তবে পুনরায় প্রসেস করবেন না
        if ($order->status === 'completed') {
            return true;
        }

        // পেমেন্ট ভ্যালিডেট করুন
        if (Sslcommerz::validatePayment($request->all(), $order->transaction_id, $order->amount, $order->currency)) {
            $order->update([
                'status' => 'completed',
                'val_id' => $request->input('val_id'),
                'bank_tran_id' => $request->input('bank_tran_id'),
                'card_type' => $request->input('card_type'),
                'paid_at' => now(),
            ]);

            // ইভেন্ট ডিসপ্যাচ করুন (যেমন: ইনভয়েস পাঠানো, ইনভেন্টরি আপডেট ইত্যাদি)
            event(new OrderPaid($order));

            return true;
        }

        return false;
    });
}
```

---

## 🔒 সিকিউরিটি বেস্ট প্র্যাকটিস সারসংক্ষেপ

| প্র্যাকটিস | বিবরণ |
| ---------- | ------ |
| **কখনই ইউজারের ডেটা অন্ধভাবে বিশ্বাস করবেন না** | সবসময় `validatePayment()` দিয়ে সরাসরি SSLCommerz থেকে স্ট্যাটাস ও টাকার পরিমাণ যাচাই করুন। |
| **ডাটাবেজের অ্যামাউন্ট ব্যবহার করুন** | রিকোয়েস্টে আসা অ্যামাউন্ট নয়, বরং আপনার ডাটাবেজে সংরক্ষিত অ্যামাউন্ট ভ্যালিডেশন মেথডে পাস করুন। |
| **HTTPS ব্যবহার করুন** | লাইভ প্রোডাকশনে অবশ্যই সকল কলব্যাক রাউটে `https://` ব্যবহার করুন। |
| **IPN-এ হ্যাশ ভেরিফাই করুন** | আইপিএন রিকোয়েস্ট প্রসেস করার আগে `verifyHash()` কল করুন। |
| **আইডেমপোটেন্সি বজায় রাখুন** | রিডাইরেক্ট এবং আইপিএন-এর ডাবল প্রসেসিং রুখতে ডাটাবেজ লকিং ব্যবহার করুন। |

---

## ⏭️ পরবর্তী ধাপ

- রিফান্ড ইস্যু করার নিয়ম জানতে [রিফান্ড ম্যানেজমেন্ট](04-refunds.md) দেখুন।
- গেটওয়ে ফিল্টারিং ও কাস্টম প্যারামিটার জানতে [অ্যাডভান্সড কনফিগারেশন](05-advanced-configuration.md) দেখুন।
