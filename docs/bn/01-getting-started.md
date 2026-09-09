# শুরু করার নির্দেশিকা (Getting Started)

[English Guide](../en/01-getting-started.md) | **বাংলা গাইড**

**SSLCommerz Laravel** (`raziul/sslcommerz-laravel`) প্যাকেজের বিস্তারিত ডকুমেন্টেশনে আপনাকে স্বাগতম। এই প্যাকেজটির মাধ্যমে আপনি আপনার লারাভেল (Laravel) অ্যাপ্লিকেশনে খুব সহজে এবং দ্রুততম সময়ে বাংলাদেশের জনপ্রিয় পেমেন্ট গেটওয়ে **SSLCommerz** ইন্টিগ্রেট করতে পারবেন।

---

## 📋 প্রয়োজনীয় রিকোয়ারমেন্টস (Requirements)

প্যাকেজটি ব্যবহার শুরু করার আগে আপনার প্রজেক্টের রিকোয়ারমেন্টসগুলো নিশ্চিত করে নিন:

| রিকোয়ারমেন্ট | সাপোর্টেড ভার্সন |
| ------------ | ---------------- |
| **PHP** | `8.2`, `8.3`, `8.4`, `8.5` |
| **Laravel** | `10.x`, `11.x`, `12.x`, `13.x` |
| **PHP এক্সটেনশন** | `cURL`, `OpenSSL`, `JSON`, `mbstring` |
| **SSLCommerz অ্যাকাউন্ট** | স্যান্ডবক্স অথবা লাইভ স্টোর ক্রেডেনশিয়ালস |

---

## 📦 ইনস্টলেশন (Installation)

কম্পোজার (Composer)-এর মাধ্যমে আপনার লারাভেল প্রজেক্টে প্যাকেজটি ইনস্টল করুন:

```bash
composer require raziul/sslcommerz-laravel
```

লারাভেলের প্যাকেজ ডিসকভারি সুবিধা স্বয়ংক্রিয়ভাবে `Raziul\Sslcommerz\SslcommerzServiceProvider` এবং `Sslcommerz` ফ্যাসাড (Facade) রেজিস্টার করে নেবে।

---

## ⚙️ কনফিগারেশন সেটআপ (Configuration Setup)

### ১. ইনস্টল কমান্ড রান করুন

প্যাকেজের কনফিগারেশন ফাইল পাবলিশ করার জন্য নিচের আর্টিসান কমান্ডটি রান করুন:

```bash
php artisan sslcommerz:install
```

> [!NOTE]
> আপনি চাইলে `php artisan sslcommerz-laravel:install` অথবা সরাসরি ভেন্ডর পাবলিশ কমান্ডও ব্যবহার করতে পারেন:
> ```bash
> php artisan vendor:publish --tag=sslcommerz-config
> ```

এই কমান্ডটি আপনার অ্যাপ্লিকেশনের `config` ডিরেক্টরিতে `sslcommerz.php` ফাইলটি তৈরি করবে।

---

### ২. এনভায়রনমেন্ট ভেরিয়েবল (`.env`)

আপনার প্রজেক্টের `.env` ফাইলে নিচের ভেরিয়েবলগুলো যুক্ত করুন:

```env
# SSLCommerz মোড: টেস্টিংয়ের জন্য true, লাইভ বা প্রোডাকশনের জন্য false
SSLC_SANDBOX=true

# SSLCommerz থেকে প্রাপ্ত স্টোর ক্রেডেনশিয়ালস
SSLC_STORE_ID=your_store_id
SSLC_STORE_PASSWORD=your_store_password

# ডিফল্ট কারেন্সি (BDT, USD, EUR ইত্যাদি)
SSLC_STORE_CURRENCY=BDT

# SSLCommerz কলব্যাক রাউট নেইমস (ঐচ্ছিক, ডিফল্ট নিচে দেওয়া হলো)
SSLC_ROUTE_SUCCESS=sslc.success
SSLC_ROUTE_FAILURE=sslc.failure
SSLC_ROUTE_CANCEL=sslc.cancel
SSLC_ROUTE_IPN=sslc.ipn
```

---

### ৩. `config/sslcommerz.php` ফাইলের পরিচিতি

পাবলিশ হওয়া কনফিগারেশন ফাইলটির বিস্তারিত বিবরণ:

```php
return [
    /**
     * স্যান্ডবক্স মোড চালু/বন্ধ (Enable/Disable Sandbox)
     * true  => https://sandbox.sslcommerz.com
     * false => https://securepay.sslcommerz.com
     */
    'sandbox' => env('SSLC_SANDBOX', true),

    /**
     * SSLCommerz থেকে প্রাপ্ত স্টোর ক্রেডেনশিয়ালস
     */
    'store' => [
        'id' => env('SSLC_STORE_ID'),
        'password' => env('SSLC_STORE_PASSWORD'),
        'currency' => env('SSLC_STORE_CURRENCY', 'BDT'),
    ],

    /**
     * success/failure/cancel/ipn কলব্যাকের জন্য রাউট নেইমস
     * প্যাকেজটি লারাভেলের route() হেল্পারের মাধ্যমে স্বয়ংক্রিয়ভাবে এগুলো নির্ধারণ করে।
     */
    'route' => [
        'success' => env('SSLC_ROUTE_SUCCESS', 'sslc.success'),
        'failure' => env('SSLC_ROUTE_FAILURE', 'sslc.failure'),
        'cancel' => env('SSLC_ROUTE_CANCEL', 'sslc.cancel'),
        'ipn' => env('SSLC_ROUTE_IPN', 'sslc.ipn'),
    ],

    /**
     * SSLCommerz-এর রিকোয়ার্ড প্রোডাক্ট প্রোফাইল
     * ডিফল্ট: "general"
     *
     * অন্যান্য প্রোফাইল:
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

## 🧪 স্যান্ডবক্স ক্রেডেনশিয়াল সংগ্রহ করা

লোকাল ডেভেলপমেন্ট ও টেস্টিংয়ের জন্য আপনার স্যান্ডবক্স ক্রেডেনশিয়াল প্রয়োজন:

১. **রেজিস্ট্রেশন করুন**: [SSLCommerz Developer Registration](https://developer.sslcommerz.com/registration/) পেজে গিয়ে রেজিস্ট্রেশন ফর্ম পূরণ করুন।
২. **ক্রেডেনশিয়াল গ্রহণ করুন**: রেজিস্ট্রেশনের পর আপনার ইমেইলে নিচের তথ্যগুলো পেয়ে যাবেন:
   - **Store ID** (যেমন: `testb64...`)
   - **Store Password** (যেমন: `testb64...@ssl`)
৩. **.env ফাইলে সেট করুন**: প্রাপ্ত `Store ID` ও `Store Password` আপনার `.env` ফাইলে `SSLC_STORE_ID` এবং `SSLC_STORE_PASSWORD`-এ বসিয়ে দিন এবং `SSLC_SANDBOX=true` রাখুন।

> [!IMPORTANT]
> **প্রোডাকশন চেকলিস্ট**:
> লাইভ সার্ভারে ডিপ্লয় করার সময়:
> ১. প্রোডাকশন `.env` ফাইলে `SSLC_SANDBOX=false` সেট করুন।
> ২. লাইভ মার্চেন্ট `Store ID` এবং `Store Password` প্রদান করুন।
> ৩. আপনার ডোমেইনে ভ্যালিড SSL সার্টিফিকেট (HTTPS) সক্রিয় থাকা নিশ্চিত করুন।
> ৪. IPN রাউটটি যেন বাহিরের ইন্টারনেট থেকে এক্সেসযোগ্য হয় তা নিশ্চিত করুন।

---

## ⏭️ পরবর্তী ধাপ

- কলব্যাক রাউট তৈরি, CSRF এক্সেম্পশন এবং পেমেন্ট রিকোয়েস্ট তৈরি করতে [ব্যবহার ও কাজের ধারা (Basic Usage)](02-basic-usage.md) দেখুন।
- পেমেন্টের সত্যতা যাচাই করতে [পেমেন্ট ভ্যালিডেশন ও সিকিউরিটি](03-validation-and-security.md) দেখুন।
