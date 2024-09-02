<?php

namespace Raziul\Sslcommerz\Data;

class RefundResponse
{
    public function __construct(
        protected ?array $data
    ) {}

    /**
     * Get the status of the transaction.
     */
    public function status(): ?string
    {
        return isset($this->data['status']) ? strtolower($this->data['status']) : null;
    }

    /**
     * Determine if the refund request initiated successfully.
     */
    public function success(): bool
    {
        return $this->status() === 'success';
    }

    /**
     * Determine if the refund has been initiated already.
     */
    public function processing(): bool
    {
        return $this->status() === 'processing';
    }

    /**
     * Determine if refund is failed to initiate.
     */
    public function failed(): bool
    {
        return $this->status() === 'failed';
    }

    /**
     * Get the reason of the failure.
     */
    public function failedReason(): ?string
    {
        return $this->data['errorReason'] ?? null;
    }

    /**
     * Get the bank transaction ID.
     */
    public function bankTranId(): ?string
    {
        return $this->data['bank_tran_id'] ?? null;
    }

    /**
     * Get the transaction ID.
     */
    public function transId(): ?string
    {
        return $this->data['trans_id'] ?? null;
    }

    /**
     * Get the refund reference ID.
     */
    public function refundRefId(): ?string
    {
        return $this->data['refund_ref_id'] ?? null;
    }

    /**
     * Get the raw response.
     */
    public function toArray(): ?array
    {
        return $this->data;
    }
}
