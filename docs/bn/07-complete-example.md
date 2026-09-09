# সম্পূর্ণ প্রজেক্ট উদাহরণ (Complete Example)

[English Guide](../en/07-complete-example.md) | **বাংলা গাইড**

এই নির্দেশিকায় একটি লারাভেল অ্যাপ্লিকেশনে SSLCommerz পেমেন্ট গেটওয়ের বাস্তবভিত্তিক সম্পূর্ণ ইন্টিগ্রেশন (ডাটাবেজ মাইগ্রেশন, মডেল, রাউট, কন্ট্রোলার এবং ভিউ) দেখানো হয়েছে।

---

## 🗄️ ধাপ ১: ডাটাবেজ মাইগ্রেশন তৈরি

`orders` টেবিলের জন্য মাইগ্রেশন তৈরি করুন:

```bash
php artisan make:migration create_orders_table
```

মাইগ্রেশন ফাইলে নিচের স্কিমা যুক্ত করুন:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('session_key')->nullable();
            $table->string('bank_tran_id')->nullable();
            $table->string('val_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->text('customer_address');
            $table->string('product_name');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('BDT');
            $table->string('card_type')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

মাইগ্রেশন রান করুন:

```bash
php artisan migrate
```

---

## 📦 ধাপ ২: এলোকোয়েন্ট মডেল (Eloquent Model)

`app/Models/Order.php` ফাইলটি তৈরি বা আপডেট করুন:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'session_key',
        'bank_tran_id',
        'val_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'product_name',
        'amount',
        'currency',
        'card_type',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];
}
```

---

## 🛣️ ধাপ ৩: রাউট ডেফিনিশন (Routes)

`routes/web.php` ফাইলে রাউটগুলো যুক্ত করুন:

```php
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// চেকআউট ও অর্ডার পেজ রাউট
Route::get('/checkout', [PaymentController::class, 'showCheckout'])->name('checkout.show');
Route::post('/checkout/pay', [PaymentController::class, 'initiatePayment'])->name('checkout.pay');
Route::get('/orders/{order}', [PaymentController::class, 'showOrder'])->name('orders.show');

// SSLCommerz কলব্যাক রাউটসমূহ
Route::prefix('sslcommerz')
    ->name('sslc.')
    ->controller(PaymentController::class)
    ->group(function () {
        Route::post('success', 'success')->name('success');
        Route::post('failure', 'failure')->name('failure');
        Route::post('cancel', 'cancel')->name('cancel');
        Route::post('ipn', 'ipn')->name('ipn');
    });
```

> [!IMPORTANT]
> `bootstrap/app.php` (লারাভেল ১১, ১২, ১৩) অথবা `VerifyCsrfToken.php` (লারাভেল ১০) ফাইলে `sslcommerz/*` রাউটকে অবশ্যই CSRF / `PreventRequestForgery` থেকে এক্সেম্পট করুন।

---

## 🎮 ধাপ ৪: কন্ট্রোলার ইমপ্লিমেন্টেশন (Controller)

`app/Http/Controllers/PaymentController.php` তৈরি করুন:

```php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Raziul\Sslcommerz\Facades\Sslcommerz;

class PaymentController extends Controller
{
    public function showCheckout()
    {
        return view('checkout');
    }

    public function showOrder(Order $order)
    {
        return view('order-summary', compact('order'));
    }

    /**
     * পেমেন্ট শুরু এবং SSLCommerz-এ রিডাইরেক্ট করা
     */
    public function initiatePayment(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'required|email|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:255',
            'product_name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:10',
        ]);

        $tranId = 'TXN-' . strtoupper(Str::random(10));

        // ডাটাবেজে পেন্ডিং অর্ডার তৈরি করুন
        $order = Order::create([
            'transaction_id' => $tranId,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'],
            'product_name' => $validated['product_name'],
            'amount' => $validated['amount'],
            'currency' => 'BDT',
            'status' => 'pending',
        ]);

        // SSLCommerz-এ পেমেন্ট সেশন ইনিশিয়েট করুন
        $response = Sslcommerz::setOrder($order->amount, $order->transaction_id, $order->product_name)
            ->setCustomer(
                name: $order->customer_name,
                email: $order->customer_email,
                phone: $order->customer_phone,
                address: $order->customer_address
            )
            ->setShippingInfo(
                quantity: 1,
                address: $order->customer_address
            )
            ->makePayment();

        if ($response->success()) {
            // সেশন কী সংরক্ষণ করুন
            $order->update(['session_key' => $response->sessionKey()]);

            // SSLCommerz-এর পেমেন্ট পেজে রিডাইরেক্ট করুন
            return redirect()->away($response->gatewayPageURL());
        }

        $order->update(['status' => 'failed']);

        return back()->with('error', 'পেমেন্ট শুরু করা যায়নি: ' . $response->failedReason());
    }

    /**
     * পেমেন্ট সফল হলে কাস্টমার রিডাইরেক্ট রিসিভ করা
     */
    public function success(Request $request)
    {
        $tranId = $request->input('tran_id');
        $order = Order::where('transaction_id', $tranId)->firstOrFail();

        // সার্ভার-টু-সার্ভার পেমেন্ট যাচাই করুন
        $isValid = Sslcommerz::validatePayment(
            payload: $request->all(),
            transactionId: $order->transaction_id,
            amount: $order->amount,
            currency: $order->currency
        );

        if ($isValid) {
            $this->markOrderAsPaid($order, $request->all());

            return redirect()->route('orders.show', $order)->with('success', 'পেমেন্ট সফলভাবে সম্পন্ন হয়েছে!');
        }

        return redirect()->route('orders.show', $order)->with('error', 'পেমেন্ট ভ্যালিডেশন ব্যর্থ হয়েছে।');
    }

    /**
     * পেমেন্ট ব্যর্থ হওয়ার কলব্যাক
     */
    public function failure(Request $request)
    {
        $tranId = $request->input('tran_id');
        $order = Order::where('transaction_id', $tranId)->first();

        if ($order && $order->status === 'pending') {
            $order->update(['status' => 'failed']);
        }

        return redirect()->route('checkout.show')->with('error', 'পেমেন্ট সম্পন্ন হয়নি! কারণ: ' . $request->input('error', 'অজানা ত্রুটি'));
    }

    /**
     * পেমেন্ট বাতিল করার কলব্যাক
     */
    public function cancel(Request $request)
    {
        $tranId = $request->input('tran_id');
        $order = Order::where('transaction_id', $tranId)->first();

        if ($order && $order->status === 'pending') {
            $order->update(['status' => 'cancelled']);
        }

        return redirect()->route('checkout.show')->with('warning', 'আপনি পেমেন্ট বাতিল করেছেন।');
    }

    /**
     * ইনস্ট্যান্ট পেমেন্ট নোটিফিকেশন (IPN)
     */
    public function ipn(Request $request)
    {
        if (! Sslcommerz::verifyHash($request->all())) {
            return response()->json(['message' => 'Invalid Signature'], 400);
        }

        $tranId = $request->input('tran_id');
        $order = Order::where('transaction_id', $tranId)->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (Sslcommerz::validatePayment($request->all(), $order->transaction_id, $order->amount, $order->currency)) {
            $this->markOrderAsPaid($order, $request->all());

            return response()->json(['message' => 'IPN Processed Successfully'], 200);
        }

        return response()->json(['message' => 'Payment Validation Failed'], 400);
    }

    /**
     * অর্ডার সফল হিসেবে মার্ক করার সহায়ক মেথড (ডাবল প্রসেসিং প্রতিরোধে)
     */
    private function markOrderAsPaid(Order $order, array $data): void
    {
        DB::transaction(function () use ($order, $data) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            if ($lockedOrder->status !== 'completed') {
                $lockedOrder->update([
                    'status' => 'completed',
                    'bank_tran_id' => $data['bank_tran_id'] ?? null,
                    'val_id' => $data['val_id'] ?? null,
                    'card_type' => $data['card_type'] ?? null,
                    'paid_at' => now(),
                ]);
            }
        });
    }
}
```

---

## 🎨 ধাপ ৫: ব্লেড ভিউ (Blade View)

### চেকআউট পেজ (`resources/views/checkout.blade.php`)

```html
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>চেকআউট</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center">পেমেন্ট সম্পন্ন করুন</h1>

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>
        @endif
        @if(session('warning'))
            <div class="bg-yellow-100 text-yellow-700 p-3 rounded mb-4">{{ session('warning') }}</div>
        @endif

        <form action="{{ route('checkout.pay') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium">পণ্যের নাম</label>
                <input type="text" name="product_name" value="স্মার্ট ওয়াচ" readonly class="w-full border rounded p-2 bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium">মূল্য (টাকা)</label>
                <input type="number" name="amount" value="500" readonly class="w-full border rounded p-2 bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium">আপনার নাম</label>
                <input type="text" name="customer_name" required class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium">ইমেইল</label>
                <input type="email" name="customer_email" required class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium">মোবাইল নম্বর</label>
                <input type="text" name="customer_phone" required class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium">ডেলিভারি ঠিকানা</label>
                <textarea name="customer_address" required class="w-full border rounded p-2"></textarea>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-700">
                SSLCommerz-এর মাধ্যমে পে করুন
            </button>
        </form>
    </div>
</body>
</html>
```

---

## ⏭️ পরবর্তী ধাপ

- টেস্ট অ্যাকাউন্ট দিয়ে ট্রানজ্যাকশন পরীক্ষা করতে [টেস্টিং ও ট্রাবলশুটিং](08-testing-and-troubleshooting.md) দেখুন।
