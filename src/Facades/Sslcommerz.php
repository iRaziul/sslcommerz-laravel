<?php

namespace Raziul\Sslcommerz\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Raziul\Sslcommerz\SslcommerzClient setOrder(int|float $amount, string $invoiceId, string $productName, string $productCategory = ' ')
 * @method static \Raziul\Sslcommerz\SslcommerzClient setCustomer(string $name, string $email, string $phone = ' ', string $address = ' ', string $city = ' ', string $state = ' ', string $postal = ' ', string $country = 'Bangladesh', string $fax = null)
 * @method static \Raziul\Sslcommerz\SslcommerzClient setShippingInfo(int $quantity, string $address, string $name = null, string $city = null, string $state = null, string $postal = null, string $country = null)
 * @method static \Raziul\Sslcommerz\SslcommerzClient setCallbackUrls(string $successUrl, string $failedUrl, string $cancelUrl, string $ipnUrl)
 * @method static \Raziul\Sslcommerz\SslcommerzClient setGateways(array $gateways)
 * @method static \Raziul\Sslcommerz\SslcommerzClient setProductProfile(string $profile)
 * @method static \Raziul\Sslcommerz\Data\PaymentResponse makePayment(array $additionalData = [])
 * @method static bool validatePayment(array $payload, string $transactionId, int|float $amount, string $currency = 'BDT')
 * @method static bool verifyHash(array $data)
 * @method static \Raziul\Sslcommerz\Data\RefundResponse refundPayment(string $bankTransactionId, int|float $amount, string $reason)
 * @method static \Raziul\Sslcommerz\Data\RefundStatus checkRefundStatus(string $refundRefId)
 *
 * @see \Raziul\Sslcommerz\SslcommerzClient
 */
class Sslcommerz extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Raziul\Sslcommerz\SslcommerzClient::class;
    }
}
