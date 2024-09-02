<?php

namespace Raziul\Sslcommerz\Data;

class RefundStatus
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
     * Determine if the refund was successful.
     */
    public function refunded(): bool
    {
        return $this->status() === 'refunded';
    }

    /**
     * Determine if the refund is under processing.
     */
    public function processing(): bool
    {
        return $this->status() === 'processing';
    }

    /**
     * Determine if the refund was cancelled.
     */
    public function cancelled(): bool
    {
        return $this->status() === 'cancelled';
    }

    /**
     * Get the reason of the failure.
     */
    public function reason(): ?string
    {
        return $this->data['errorReason'] ?? null;
    }

    /**
     * Get the initiation datetime.
     */
    public function initiatedAt(): ?string
    {
        return $this->data['initiated_on'] ?? null;
    }

    /**
     * Get the refund datetime.
     */
    public function refundedAt(): ?string
    {
        return $this->data['refunded_on'] ?? null;
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
