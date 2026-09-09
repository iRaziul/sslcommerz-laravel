<a href="https://github.com/iRaziul/sslcommerz-laravel">
<img style="width: 100%; max-width: 100%;" alt="Sslcommerz Laravel Package" src="/art/sslcommerz-laravel.webp" >
</a>

# SSLCommerz Laravel প্যাকেজ

<p align="center">
    <a href="README.md">🇬🇧 English</a> •
    <a href="README.bn.md">🇧🇩 <strong>বাংলা</strong></a>
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/raziul/sslcommerz-laravel.svg?style=flat-square)](https://packagist.org/packages/raziul/sslcommerz-laravel)
[![Laravel Compatibility](https://badge.laravel.cloud/badge/raziul/sslcommerz-laravel?style=flat)](https://packagist.org/packages/raziul/sslcommerz-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/iRaziul/sslcommerz-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/iRaziul/sslcommerz-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/raziul/sslcommerz-laravel.svg?style=flat-square)](https://packagist.org/packages/raziul/sslcommerz-laravel)
[![License](https://img.shields.io/packagist/l/raziul/sslcommerz-laravel.svg?style=flat-square)](https://packagist.org/packages/raziul/sslcommerz-laravel)

এই প্যাকেজটির মাধ্যমে আপনার **Laravel** অ্যাপ্লিকেশনে খুব সহজে এবং দ্রুততম সময়ে **SSLCommerz** পেমেন্ট গেটওয়ে যুক্ত করতে পারবেন। পেমেন্ট প্রসেসিং, সার্ভার ভ্যালিডেশন, রিফান্ড ম্যানেজমেন্ট এবং সিকিউরিটি হ্যাশ ভেরিফিকেশনের মতো সুবিধাসমূহ এই প্যাকেজে অত্যন্ত সহজ এপিআই (API) দিয়ে তৈরি করা হয়েছে।

---

## 📑 সূচিপত্র

- [বৈশিষ্ট্যসমূহ](#-বৈশিষ্ট্যসমূহ)
- [প্রয়োজনীয় রিকোয়ারমেন্টস](#-প্রয়োজনীয়-রিকোয়ারমেন্টস)
- [ইনস্টলেশন](#-ইনস্টলেশন)
- [কনফিগারেশন](#-কনফিগারেশন)
- [ব্যবহারের নিয়ম](#-ব্যবহারের-নিয়ম)
- [ডকুমেন্টেশন](#-ডকুমেন্টেশন)
- [লাইসেন্স](#-লাইসেন্স)

---

## ✨ বৈশিষ্ট্যসমূহ

- 🚀 দারুণ ডেভেলপার এক্সপেরিয়েন্স এবং সহজ ফ্লুয়েন্ট এপিআই
- 💳 SSLCommerz গেটওয়ের মাধ্যমে দ্রুত পেমেন্ট সেশন তৈরি
- 🔄 Success, Failure, Cancel এবং IPN কলব্যাকের স্বয়ংক্রিয় হ্যান্ডলিং
- 🛡️ সার্ভার-টু-সার্ভার পেমেন্ট ট্রানজ্যাকশন ভ্যালিডেশন
- 💸 ফুল ও পার্শিয়াল রিফান্ড এবং রিফান্ড স্ট্যাটাস ট্র্যাকিং
- 🔐 ডিজিটাল সিগনেচার ও MD5 হ্যাশ ভেরিফিকেশন
- 🧪 স্যান্ডবক্স (Sandbox) এবং লাইভ (Live) প্রোডাকশন সাপোর্ট
- 💱 মাল্টি-কারেন্সি সাপোর্ট (BDT, USD, EUR ইত্যাদি)
- 🎯 নির্দিষ্ট গেটওয়ে ফিল্টারিং (যেমন: বিকাশ, নগদ ইত্যাদি)

---

## 📋 প্রয়োজনীয় রিকোয়ারমেন্টস

- **PHP**: `8.2+`
- **Laravel**: `10.0+`, `11.0+`, `12.0+`, `13.0+`
- **SSLCommerz মার্চেন্ট অ্যাকাউন্ট** (স্যান্ডবক্স বা লাইভ)

---

## 📦 ইনস্টলেশন

Composer-এর মাধ্যমে প্যাকেজটি ইনস্টল করুন:

```bash
composer require raziul/sslcommerz-laravel
```

লারাভেলের প্যাকেজ অটো-ডিসকভারি স্বয়ংক্রিয়ভাবে সার্ভিস প্রোভাইডার রেজিস্টার করবে।

---

## ⚙️ কনফিগারেশন

### ১. আর্টিসান ইনস্টল কমান্ড চালান

```bash
php artisan sslcommerz:install
```

### ২. এনভায়রনমেন্ট ভেরিয়েবল যোগ করুন

আপনার `.env` ফাইলে ক্রেডেনশিয়াল যুক্ত করুন:

```env
SSLC_SANDBOX=true # লাইভ প্রোডাকশনের জন্য false
SSLC_STORE_ID=your_store_id
SSLC_STORE_PASSWORD=your_store_password
SSLC_STORE_CURRENCY=BDT
```

---

## 💡 ব্যবহারের নিয়ম

### ১. পেমেন্ট শুরু করা

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

$response = Sslcommerz::setOrder($amount, $invoiceId, 'Order #' . $invoiceId)
    ->setCustomer($name, $email, $phone, $address)
    ->setShippingInfo(1, $address)
    ->makePayment();

if ($response->success()) {
    // SSLCommerz হোস্টেড পেমেন্ট পেজে রিডাইরেক্ট করুন
    return redirect($response->gatewayPageURL());
}
```

### ২. পেমেন্ট ভ্যালিডেশন

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

$isValid = Sslcommerz::validatePayment($request->all(), $transactionId, $amount);

if ($isValid) {
    // পেমেন্ট সফল ও ভ্যালিড
    $order->update(['status' => 'completed']);
}
```

---

## 📖 ডকুমেন্টেশন

- [শুরু করার নির্দেশিকা](docs/bn/01-getting-started.md) ([English](docs/en/01-getting-started.md))
- [ব্যবহার ও কাজের ধারা](docs/bn/02-basic-usage.md) ([English](docs/en/02-basic-usage.md))
- [পেমেন্ট ভ্যালিডেশন ও সিকিউরিটি](docs/bn/03-validation-and-security.md) ([English](docs/en/03-validation-and-security.md))
- [রিফান্ড ম্যানেজমেন্ট](docs/bn/04-refunds.md) ([English](docs/en/04-refunds.md))
- [অ্যাডভান্সড কনফিগারেশন](docs/bn/05-advanced-configuration.md) ([English](docs/en/05-advanced-configuration.md))
- [এপিআই রেফারেন্স](docs/bn/06-api-reference.md) ([English](docs/en/06-api-reference.md))
- [সম্পূর্ণ প্রজেক্ট উদাহরণ](docs/bn/07-complete-example.md) ([English](docs/en/07-complete-example.md))
- [টেস্টিং ও ট্রাবলশুটিং](docs/bn/08-testing-and-troubleshooting.md) ([English](docs/en/08-testing-and-troubleshooting.md))

---

## 👥 অবদান ও কৃতজ্ঞতা

- [Raziul Islam](https://github.com/iRaziul)
- [সকল অবদানকারী](../../contributors)

## 📄 লাইসেন্স

MIT License (MIT)। বিস্তারিত তথ্যের জন্য [License File](LICENSE.md) দেখুন।
