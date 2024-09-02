<?php

namespace Raziul\Sslcommerz\Data;

class PaymentResponse
{
    public function __construct(
        protected ?array $data = null
    ) {
    }

    /**
     * Get the status of the payment response in lowercase.
     */
    public function status(): ?string
    {
        return isset($this->data['status']) ? strtolower($this->data['status']) : null;
    }

    /**
     * Determine if the payment was successful.
     */
    public function success(): bool
    {
        return $this->status() === 'success';
    }

    /**
     * Determine if the payment failed.
     */
    public function failed(): bool
    {
        return $this->failedReason() !== null;
    }

    /**
     * Get the reason for the payment failure.
     */
    public function failedReason(): ?string
    {
        return $this->data['failedreason'] ?? null;
    }

    /**
     * Get the session key of the payment response.
     */
    public function sessionKey(): ?string
    {
        return $this->data['sessionkey'] ?? null;
    }

    /**
     * Get the list of available gateways.
     */
    public function gatewayList(): ?array
    {
        return $this->data['gw'] ?? null;
    }

    /**
     * Get the gateway page URL where the user can complete the payment.
     * User should be redirected to this URL.
     */
    public function gatewayPageURL(): ?string
    {
        return $this->data['GatewayPageURL'] ?? null;
    }

    /**
     * Get the URL to redirect to the gateway.
     */
    public function redirectGatewayURL(): ?string
    {
        return $this->data['redirectGatewayURL'] ?? null;
    }

    /**
     * Get the direct payment URL for bank payments.
     */
    public function directPaymentURLBank(): ?string
    {
        return $this->data['directPaymentURLBank'] ?? null;
    }

    /**
     * Get the direct payment URL for card payments.
     */
    public function directPaymentURLCard(): ?string
    {
        return $this->data['directPaymentURLCard'] ?? null;
    }

    /**
     * Get the direct payment URL.
     */
    public function directPaymentURL(): ?string
    {
        return $this->data['directPaymentURL'] ?? null;
    }

    /**
     * Get the URL to redirect to if the gateway fails.
     */
    public function redirectGatewayURLFailed(): ?string
    {
        return $this->data['redirectGatewayURLFailed'] ?? null;
    }

    /**
     * Get the store banner URL.
     */
    public function storeBanner(): ?string
    {
        return $this->data['storeBanner'] ?? null;
    }

    /**
     * Get the store logo URL.
     */
    public function storeLogo(): ?string
    {
        return $this->data['storeLogo'] ?? null;
    }

    /**
     * Get the description array.
     */
    public function description(): ?array
    {
        return $this->data['desc'] ?? null;
    }

    /**
     * Get the raw response.
     */
    public function toArray(): ?array
    {
        return $this->data;
    }
}
