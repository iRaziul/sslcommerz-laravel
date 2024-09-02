<?php

namespace Raziul\Sslcommerz;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Raziul\Sslcommerz\Data\PaymentResponse;
use Raziul\Sslcommerz\Data\RefundResponse;
use Raziul\Sslcommerz\Data\RefundStatus;

class SslcommerzClient
{
    /**
     * The payload data to be sent with the request.
     */
    protected array $data = [];

    /**
     * The order info for the payment request.
     */
    protected array $order = [];

    /**
     * The customer info for the payment request.
     */
    protected array $customer = [];

    /**
     * The shipping info for the payment request.
     */
    protected array $shipping = [];

    /**
     * Create a new instance of Sslcommerz.
     */
    public function __construct(
        protected string $storeId,
        protected string $storePassword,
        protected string $currency,
        protected bool $sandbox,
    ) {
        $this->data = [
            'store_id' => $storeId,
            'store_passwd' => $storePassword,
            'currency' => $currency,
        ];
    }

    /**
     * Set the callback URLs for the payment.
     */
    public function setCallbackUrls(
        string $successUrl,
        string $failedUrl,
        string $cancelUrl,
        string $ipnUrl
    ): self {
        $this->data['success_url'] = $successUrl;
        $this->data['fail_url'] = $failedUrl;
        $this->data['cancel_url'] = $cancelUrl;
        $this->data['ipn_url'] = $ipnUrl;

        return $this;
    }

    /**
     * Set the gateways to be displayed in the payment page.
     * To display all gateways, pass `null` as the argument.
     */
    public function setGateways(array $gateways): self
    {
        $this->data['multi_card_name'] = implode(',', $gateways);

        return $this;
    }

    /**
     * Set the product profile for the payment.
     *
     * Possible values are:
     *     - general
     *     - physical-goods
     *     - non-physical-goods
     *     - airline-tickets
     *     - travel-vertical
     *     - telecom-vertical
     */
    public function setProductProfile(string $profile): self
    {
        $this->data['product_profile'] = $profile;

        return $this;
    }

    /**
     * Set the order details for the payment.
     */
    public function setOrder(
        int|float $amount,
        string $invoiceId,
        string $productName,
        string $productCategory = ' ',
    ): self {
        $this->order = [
            'total_amount' => $amount,
            'tran_id' => $invoiceId,
            'product_name' => $productName,
            'product_category' => $productCategory,
        ];

        return $this;
    }

    /**
     * Set the customer details for the payment.
     */
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
    ): self {
        $this->customer = [
            'cus_name' => $name,
            'cus_email' => $email,
            'cus_phone' => $phone,
            'cus_add1' => $address,
            'cus_city' => $city,
            'cus_state' => $state,
            'cus_postcode' => $postal,
            'cus_country' => $country,
            'cus_fax' => $fax,
        ];

        return $this;
    }

    /**
     * Set the shipping information for the payment.
     */
    public function setShippingInfo(
        int $quantity,
        string $address,
        ?string $name = null,
        ?string $city = null,
        ?string $state = null,
        ?string $postal = null,
        ?string $country = null
    ): self {
        $this->shipping = [
            'shipping_method' => 'NO',
            'num_of_item' => $quantity,
            'ship_name' => $name,
            'ship_add1' => $address,
            'ship_city' => $city,
            'ship_postcode' => $postal,
            'ship_state' => $state,
            'ship_country' => $country,
        ];

        return $this;
    }

    /**
     * Make a payment through SSLCommerz.
     */
    public function makePayment(array $additionalData = []): PaymentResponse
    {
        $response = $this->client()
            ->asForm()
            ->post('/gwprocess/v4/api.php', $this->mergeData($additionalData))
            ->json();

        return new PaymentResponse($response);
    }

    /**
     * Validate a payment through SSLCommerz validator.
     */
    public function validatePayment(array $payload, string $transactionId, int|float $amount, string $currency = 'BDT')
    {
        if (empty($payload['val_id'])) {
            return false;
        }

        $response = $this->client()->get('/validator/api/validationserverAPI.php', [
            'val_id' => $payload['val_id'],
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'format' => 'json',
        ])->json();

        if (empty($response)) {
            return false;
        }

        if (! isset($response['status'], $response['tran_id'], $response['amount']) || $response['status'] === 'INVALID_TRANSACTION ') {
            return false;
        }

        if (trim($transactionId) !== trim($response['tran_id'])) {
            return false;
        }

        if ($currency === 'BDT') {
            return abs($amount - $response['amount']) < 1;
        }

        return trim($currency) === trim($response['currency_type']) && abs($amount - $response['currency_amount']) < 1;
    }

    /**
     * Verify the hash from SSLCommerz response.
     */
    public function verifyHash(array $data): bool
    {
        if (empty($data['verify_sign']) || empty($data['verify_key'])) {
            return false;
        }

        $preDefinedKeys = explode(',', $data['verify_key']);
        $dataToHash = [
            'store_passwd' => md5($this->storePassword),
        ];

        foreach ($preDefinedKeys as $key) {
            $dataToHash[$key] = $data[$key] ?? '';
        }

        ksort($dataToHash);
        $hashString = http_build_query($dataToHash, '', '&');

        return md5($hashString) === $data['verify_sign'];
    }

    /**
     * Refund a payment through SSLCommerz.
     */
    public function refundPayment(string $bankTransactionId, int|float $amount, string $reason): RefundResponse
    {
        $response = $this->client()->get('/validator/api/merchantTransIDvalidationAPI.php', [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'bank_tran_id' => $bankTransactionId,
            'refund_amount' => $amount,
            'refund_remarks' => $reason,
            'format' => 'json',
        ])->json();

        return new RefundResponse($response);
    }

    /**
     * Check the refund status through SSLCommerz.
     */
    public function checkRefundStatus(string $refundRefId): RefundStatus
    {
        $response = $this->client()->get('/validator/api/merchantTransIDvalidationAPI.php', [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'refund_ref_id' => $refundRefId,
        ])->json();

        return new RefundStatus($response);
    }

    /**
     * Merge and return all the data needed for making payment.
     */
    private function mergeData(array $additionalData = []): array
    {
        return $this->data + $this->order + $this->customer + $this->shipping + $additionalData;
    }

    /**
     * Instance of the HTTP client
     */
    private function client(): PendingRequest
    {
        return Http::withoutVerifying()
            ->baseUrl(
                $this->sandbox
                ? 'https://sandbox.sslcommerz.com'
                : 'https://securepay.sslcommerz.com'
            )
            ->timeout(60);
    }
}
