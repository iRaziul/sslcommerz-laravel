# Complete Implementation Example

This guide walks through a complete, production-ready integration of the SSLCommerz payment gateway in a Laravel application, including database migration, model, routes, controller, and views.

---

## 🗄️ Step 1: Database Migration

Create a migration for the `orders` table:

```bash
php artisan make:migration create_orders_table
```

Update the migration file:

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

Run the migration:

```bash
php artisan migrate
```

---

## 📦 Step 2: Eloquent Model

Create the `Order` model in `app/Models/Order.php`:

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

## 🛣️ Step 3: Route Definitions

In `routes/web.php`:

```php
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Customer Checkout Routes
Route::get('/checkout', [PaymentController::class, 'showCheckout'])->name('checkout.show');
Route::post('/checkout/pay', [PaymentController::class, 'initiatePayment'])->name('checkout.pay');
Route::get('/orders/{order}', [PaymentController::class, 'showOrder'])->name('orders.show');

// SSLCommerz Callback Routes
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
> Don't forget to exempt `sslcommerz/*` from CSRF / `PreventRequestForgery` verification in `bootstrap/app.php` (Laravel 11, 12, 13) or `VerifyCsrfToken.php` (Laravel 10).

---

## 🎮 Step 4: Controller Implementation

Create `app/Http/Controllers/PaymentController.php`:

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
     * Initiate payment session and redirect customer.
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

        // Create pending order record in database
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

        // Initiate payment with SSLCommerz
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
            // Save session key
            $order->update(['session_key' => $response->sessionKey()]);

            // Redirect user to SSLCommerz payment page
            return redirect()->away($response->gatewayPageURL());
        }

        $order->update(['status' => 'failed']);

        return back()->with('error', 'Unable to initiate payment: ' . $response->failedReason());
    }

    /**
     * Handle user redirect upon successful payment.
     */
    public function success(Request $request)
    {
        $tranId = $request->input('tran_id');
        $order = Order::where('transaction_id', $tranId)->firstOrFail();

        // Perform server-to-server validation
        $isValid = Sslcommerz::validatePayment(
            payload: $request->all(),
            transactionId: $order->transaction_id,
            amount: $order->amount,
            currency: $order->currency
        );

        if ($isValid) {
            $this->markOrderAsPaid($order, $request->all());

            return redirect()->route('orders.show', $order)->with('success', 'Payment successful!');
        }

        return redirect()->route('orders.show', $order)->with('error', 'Payment validation failed.');
    }

    /**
     * Handle payment failure callback.
     */
    public function failure(Request $request)
    {
        $tranId = $request->input('tran_id');
        $order = Order::where('transaction_id', $tranId)->first();

        if ($order && $order->status === 'pending') {
            $order->update(['status' => 'failed']);
        }

        return redirect()->route('checkout.show')->with('error', 'Payment failed! Reason: ' . $request->input('error', 'Unknown error'));
    }

    /**
     * Handle payment cancellation callback.
     */
    public function cancel(Request $request)
    {
        $tranId = $request->input('tran_id');
        $order = Order::where('transaction_id', $tranId)->first();

        if ($order && $order->status === 'pending') {
            $order->update(['status' => 'cancelled']);
        }

        return redirect()->route('checkout.show')->with('warning', 'You have cancelled the payment.');
    }

    /**
     * Handle Instant Payment Notification (IPN).
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
     * Helper to mark order as paid with idempotency.
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

## 🎨 Step 5: Blade Views

### Checkout Page (`resources/views/checkout.blade.php`)

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center">Complete Your Purchase</h1>

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>
        @endif
        @if(session('warning'))
            <div class="bg-yellow-100 text-yellow-700 p-3 rounded mb-4">{{ session('warning') }}</div>
        @endif

        <form action="{{ route('checkout.pay') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium">Product</label>
                <input type="text" name="product_name" value="Sample Product" readonly class="w-full border rounded p-2 bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium">Amount (BDT)</label>
                <input type="number" name="amount" value="500" readonly class="w-full border rounded p-2 bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium">Full Name</label>
                <input type="text" name="customer_name" required class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium">Email Address</label>
                <input type="email" name="customer_email" required class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium">Phone Number</label>
                <input type="text" name="customer_phone" required class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium">Delivery Address</label>
                <textarea name="customer_address" required class="w-full border rounded p-2"></textarea>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-700">
                Pay with SSLCommerz
            </button>
        </form>
    </div>
</body>
</html>
```

---

## ⏭️ Next Steps

- Test your integration with sandbox test accounts in [Testing & Troubleshooting](08-testing-and-troubleshooting.md).
