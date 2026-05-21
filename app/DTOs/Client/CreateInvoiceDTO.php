<?php

namespace App\DTOs\Client;

readonly class CreateInvoiceDTO
{
    public function __construct(
        public string  $subscriptionPlan,
        public int     $amount,
        public int     $tax             = 0,
        public string  $currency        = 'USD',
        public string  $provider        = 'stripe',
        public array   $lineItems       = [],
        public ?string $dueDate         = null,
        public ?string $notes           = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            subscriptionPlan: $data['subscription_plan'],
            amount:           (int) $data['amount'],
            tax:              (int) ($data['tax'] ?? 0),
            currency:         $data['currency'] ?? 'USD',
            provider:         $data['provider'] ?? 'stripe',
            lineItems:        $data['line_items'] ?? [],
            dueDate:          $data['due_date'] ?? null,
            notes:            $data['notes'] ?? null,
        );
    }
}
