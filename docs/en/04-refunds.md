# Refunds Management

SSLCommerz provides merchant APIs to issue refunds directly back to the customer's card, mobile banking, or bank account. The `sslcommerz-laravel` package provides an intuitive, fluent API to initiate and track refund requests.

---

## 💡 How SSLCommerz Refunds Work

- **Requirement**: You need the `bank_tran_id` received when the transaction was completed.
- **Types of Refunds**: Both full and partial refunds are supported.
- **Process**:
  1. Your application calls `refundPayment()` with the `bank_tran_id`, `amount`, and `reason`.
  2. SSLCommerz responds with a `refund_ref_id` and initial status (`success` or `processing`).
  3. You can periodically check the progress of the refund using `checkRefundStatus($refundRefId)`.

---

## 💸 Initiating a Refund

To issue a refund, call `Sslcommerz::refundPayment()`:

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

$bankTransactionId = '2405021234567890'; // Stored from success callback ($request->input('bank_tran_id'))
$refundAmount = 500.00;                 // Can be full or partial amount
$reason = 'Customer requested return of defective item';

$refundResponse = Sslcommerz::refundPayment(
    bankTransactionId: $bankTransactionId,
    amount: $refundAmount,
    reason: $reason
);
```

### Working with `RefundResponse`

The `refundPayment()` method returns an instance of `\Raziul\Sslcommerz\Data\RefundResponse`.

| Method | Return Type | Description |
| ------ | ----------- | ----------- |
| `$refundResponse->success()` | `bool` | `true` if refund was immediately approved or initiated. |
| `$refundResponse->processing()` | `bool` | `true` if refund request is in progress. |
| `$refundResponse->failed()` | `bool` | `true` if the refund request was rejected or failed. |
| `$refundResponse->status()` | `?string` | Status in lowercase (`success`, `processing`, `failed`). |
| `$refundResponse->failedReason()` | `?string` | Error reason message if failed (`errorReason`). |
| `$refundResponse->refundRefId()` | `?string` | Unique Refund Reference ID from SSLCommerz (save to DB). |
| `$refundResponse->bankTranId()` | `?string` | The associated Bank Transaction ID. |
| `$refundResponse->transId()` | `?string` | The associated Order Transaction ID. |
| `$refundResponse->toArray()` | `?array` | Raw response payload from SSLCommerz API. |

---

## 🔍 Checking Refund Status

Because some refunds take time to settle through banking channels, you can query the status anytime using the `refund_ref_id`:

```php
use Raziul\Sslcommerz\Facades\Sslcommerz;

$refundRefId = 'REF-66334a1b89ef'; // Retrieved from initial refund response

$refundStatus = Sslcommerz::checkRefundStatus($refundRefId);
```

### Working with `RefundStatus`

The `checkRefundStatus()` method returns an instance of `\Raziul\Sslcommerz\Data\RefundStatus`.

| Method | Return Type | Description |
| ------ | ----------- | ----------- |
| `$refundStatus->refunded()` | `bool` | `true` if the refund was successfully credited. |
| `$refundStatus->processing()` | `bool` | `true` if the refund is still being processed. |
| `$refundStatus->cancelled()` | `bool` | `true` if the refund was cancelled / rejected. |
| `$refundStatus->status()` | `?string` | Current status string in lowercase (`refunded`, `processing`, `cancelled`). |
| `$refundStatus->reason()` | `?string` | Error or cancellation remarks (`errorReason`). |
| `$refundStatus->initiatedAt()` | `?string` | Date & time when refund was requested (`initiated_on`). |
| `$refundStatus->refundedAt()` | `?string` | Date & time when refund was executed (`refunded_on`). |
| `$refundStatus->bankTranId()` | `?string` | The associated Bank Transaction ID. |
| `$refundStatus->transId()` | `?string` | The associated Order Transaction ID. |
| `$refundStatus->refundRefId()` | `?string` | The Refund Reference ID. |
| `$refundStatus->toArray()` | `?array` | Raw response payload from SSLCommerz API. |

---

## 🛠️ Complete Example: Admin Refund Controller

Here is a practical controller implementation demonstrating refund processing in a Laravel application:

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
     * Issue a refund for an existing order.
     */
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:' . $order->amount],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if (empty($order->bank_tran_id)) {
            return back()->with('error', 'Cannot refund an order without a Bank Transaction ID.');
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

            // Update order status if full refund
            if ($request->input('amount') >= $order->amount) {
                $order->update(['status' => 'refunded']);
            }

            return back()->with('success', 'Refund initiated successfully. Ref ID: ' . $response->refundRefId());
        }

        return back()->with('error', 'Refund failed: ' . $response->failedReason());
    }

    /**
     * Check current status of a refund.
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
            return back()->with('success', 'Refund has been completed and credited!');
        }

        if ($status->cancelled()) {
            return back()->with('warning', 'Refund was cancelled. Reason: ' . $status->reason());
        }

        return back()->with('info', 'Refund is still in processing state.');
    }
}
```

---

## ⏭️ Next Steps

- Check out [Advanced Configuration](05-advanced-configuration.md) for custom data, multi-currency, and gateway filtering.
- Review the [API Reference](06-api-reference.md) for full method signatures.
