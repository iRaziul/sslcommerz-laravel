# রিফান্ড ম্যানেজমেন্ট (Refunds Management)

[English Guide](../en/04-refunds.md) | **বাংলা গাইড**

SSLCommerz মার্চেন্টদের জন্য সরাসরি API-এর মাধ্যমে কাস্টমারের কার্ড, মোবাইল ব্যাংকিং বা ব্যাংক অ্যাকাউন্টে টাকা রিফান্ড করার সুবিধা প্রদান করে। `sslcommerz-laravel` প্যাকেজে অত্যন্ত সহজ এবং সাবলীল মেথডের সাহায্যে রিফান্ড করা ও রিফান্ডের অগ্রগতি পর্যবেক্ষণ করা যায়।

---

## 💡 SSLCommerz রিফান্ড যেভাবে কাজ করে

- **পূর্বশর্ত**: সফল পেমেন্টের সময় প্রাপ্ত `bank_tran_id` প্রয়োজন।
- **রিফান্ডের প্রকারভেদ**: সম্পূর্ণ টাকা (Full Refund) অথবা আংশিক টাকা (Partial Refund) উভয়টিই করা যায়।
- **কার্যপদ্ধতি**:
  ১. আপনার অ্যাপ্লিকেশন থেকে `bank_tran_id`, `amount`, এবং `reason` দিয়ে `refundPayment()` কল করা হয়।
  ২. SSLCommerz একটি ইউনিক `refund_ref_id` এবং প্রাথমিক স্ট্যাটাস (`success` বা `processing`) প্রদান করে।
  ৩. পরবর্তীতে যেকোনো সময় `checkRefundStatus($refundRefId)` মেথডের মাধ্যমে রিফান্ডের সর্বশেষ অবস্থা জানা যায়।

---

## 💸 রিফান্ড রিকোয়েস্ট পাঠানো

রিফান্ড শুরু করতে `Sslcommerz::refundPayment()` মেথড ব্যবহার করুন:

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

$bankTransactionId = '2405021234567890'; // পেমেন্ট সফল হওয়ার সময় প্রাপ্ত bank_tran_id
$refundAmount = 500.00;                 // সম্পূর্ণ বা আংশিক রিফান্ডের পরিমাণ
$reason = 'ত্রুটিপূর্ণ পণ্য ফেরত দেওয়া হয়েছে';

$refundResponse = Sslcommerz::refundPayment(
    bankTransactionId: $bankTransactionId,
    amount: $refundAmount,
    reason: $reason
);
```

### `RefundResponse` অবজেক্টের মেথডসমূহ

`refundPayment()` মেথডটি `\Raziul\Sslcommerz\Data\RefundResponse` ক্লাসের একটি অবজেক্ট রিটার্ন করে।

| মেথড | রিটার্ন টাইপ | বিবরণ |
| ---- | ------------ | ------ |
| `$refundResponse->success()` | `bool` | রিফান্ড তাৎক্ষণিকভাবে অনুমোদিত বা শুরু হলে `true` রিটার্ন করে। |
| `$refundResponse->processing()` | `bool` | রিফান্ড প্রসেসিং অবস্থায় থাকলে `true` রিটার্ন করে। |
| `$refundResponse->failed()` | `bool` | রিফান্ড রিকোয়েস্ট ব্যর্থ বা প্রত্যাখ্যাত হলে `true` রিটার্ন করে। |
| `$refundResponse->status()` | `?string` | রিফান্ডের বর্তমান স্ট্যাটাস (`success`, `processing`, `failed`)। |
| `$refundResponse->failedReason()` | `?string` | ব্যর্থ হওয়ার কারণ বা এরর মেসেজ। |
| `$refundResponse->refundRefId()` | `?string` | SSLCommerz-এর ইউনিক রিফান্ড রেফারেন্স আইডি (ডাটাবেজে সেভ রাখুন)। |
| `$refundResponse->bankTranId()` | `?string` | সংশ্লিষ্ট ব্যাংক ট্রানজ্যাকশন আইডি। |
| `$refundResponse->transId()` | `?string` | সংশ্লিষ্ট অর্ডার ট্রানজ্যাকশন আইডি। |
| `$refundResponse->toArray()` | `?array` | SSLCommerz API থেকে আসা মূল রেসপন্স অ্যারে। |

---

## 🔍 রিফান্ডের বর্তমান অবস্থা যাচাই করা

ব্যাংকিং চ্যানেলের কারণে কিছু রিফান্ড সম্পন্ন হতে কিছুটা সময় লাগতে পারে। `refund_ref_id` দিয়ে যেকোনো সময় স্ট্যাটাস চেক করতে পারেন:

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

$refundRefId = 'REF-66334a1b89ef'; // পূর্বের রিফান্ড রিকোয়েস্ট থেকে প্রাপ্ত আইডি

$refundStatus = Sslcommerz::checkRefundStatus($refundRefId);
```

### `RefundStatus` অবজেক্টের মেথডসমূহ

`checkRefundStatus()` মেথডটি `\Raziul\Sslcommerz\Data\RefundStatus` ক্লাসের একটি অবজেক্ট রিটার্ন করে।

| মেথড | রিটার্ন টাইপ | বিবরণ |
| ---- | ------------ | ------ |
| `$refundStatus->refunded()` | `bool` | কাস্টমারের অ্যাকাউন্টে টাকা সফলভাবে ক্রেডিট হলে `true` রিটার্ন করে। |
| `$refundStatus->processing()` | `bool` | রিফান্ড এখনো প্রসেসিং অবস্থায় থাকলে `true` রিটার্ন করে। |
| `$refundStatus->cancelled()` | `bool` | রিফান্ড বাতিল হলে `true` রিটার্ন করে। |
| `$refundStatus->status()` | `?string` | বর্তমান স্ট্যাটাস (`refunded`, `processing`, `cancelled`)। |
| `$refundStatus->reason()` | `?string` | বাতিল বা ব্যর্থতার কারণ। |
| `$refundStatus->initiatedAt()` | `?string` | রিফান্ড রিকোয়েস্ট তৈরির সময় ও তারিখ। |
| `$refundStatus->refundedAt()` | `?string` | রিফান্ড সম্পন্ন হওয়ার সময় ও তারিখ। |
| `$refundStatus->bankTranId()` | `?string` | সংশ্লিষ্ট ব্যাংক ট্রানজ্যাকশন আইডি। |
| `$refundStatus->transId()` | `?string` | অর্ডারের ট্রানজ্যাকশন আইডি। |
| `$refundStatus->refundRefId()` | `?string` | রিফান্ড রেফারেন্স আইডি। |
| `$refundStatus->toArray()` | `?array` | মূল রেসপন্স অ্যারে। |

---

## 🛠️ সম্পূর্ণ উদাহরণ: অ্যাডমিন রিফান্ড কন্ট্রোলার

লারাভেল প্রজেক্টে অ্যাডমিন প্যানেল থেকে রিফান্ড পরিচালনার একটি সম্পূর্ণ কন্ট্রোলার উদাহরণ:

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Refund;
use Illuminate\Http\Request;
use Raziul\Sslcommerz\Facades\Sslcommerz;

class OrderRefundController extends Controller
{
    /**
     * অর্ডারের রিফান্ড তৈরি করা
     */
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:' . $order->amount],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if (empty($order->bank_tran_id)) {
            return back()->with('error', 'ব্যাংক ট্রানজ্যাকশন আইডি ছাড়া রিফান্ড করা সম্ভব নয়।');
        }

        $response = Sslcommerz::refundPayment(
            bankTransactionId: $order->bank_tran_id,
            amount: (float) $request->input('amount'),
            reason: $request->input('reason')
        );

        if ($response->success() || $response->processing()) {
            $refund = $order->refunds()->create([
                'refund_ref_id' => $response->refundRefId(),
                'bank_tran_id' => $response->bankTranId() ?? $order->bank_tran_id,
                'amount' => $request->input('amount'),
                'reason' => $request->input('reason'),
                'status' => $response->status(),
                'raw_response' => $response->toArray(),
            ]);

            // সম্পূর্ণ টাকা রিফান্ড হলে অর্ডারের স্ট্যাটাস পরিবর্তন করুন
            if ($request->input('amount') >= $order->amount) {
                $order->update(['status' => 'refunded']);
            }

            return back()->with('success', 'রিফান্ড রিকোয়েস্ট সফল হয়েছে। রেফারেন্স আইডি: ' . $response->refundRefId());
        }

        return back()->with('error', 'রিফান্ড ব্যর্থ হয়েছে: ' . $response->failedReason());
    }

    /**
     * রিফান্ডের সর্বশেষ অবস্থা জানা
     */
    public function checkStatus(Refund $refund)
    {
        $status = Sslcommerz::checkRefundStatus($refund->refund_ref_id);

        $refund->update([
            'status' => $status->status(),
            'refunded_at' => $status->refundedAt(),
            'raw_response' => $status->toArray(),
        ]);

        if ($status->refunded()) {
            return back()->with('success', 'রিফান্ড সফলভাবে কাস্টমারের অ্যাকাউন্টে পৌঁছেছে!');
        }

        if ($status->cancelled()) {
            return back()->with('warning', 'রিফান্ড বাতিল করা হয়েছে। কারণ: ' . $status->reason());
        }

        return back()->with('info', 'রিফান্ডটি বর্তমানে প্রসেসিং অবস্থায় রয়েছে।');
    }
}
```

---

## ⏭️ পরবর্তী ধাপ

- গেটওয়ে ফিল্টারিং ও কাস্টম প্যারামিটার জানতে [অ্যাডভান্সড কনফিগারেশন](05-advanced-configuration.md) দেখুন।
- মেথডের বিস্তারিত সিগনেচার দেখতে [এপিআই রেফারেন্স](06-api-reference.md) দেখুন।
