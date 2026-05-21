<?php

namespace App\Repositories\Contracts;

use App\Models\ClientBilling;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ClientBillingRepositoryInterface
{
    public function forClient(int $clientId, array $filters = []): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?ClientBilling;

    public function create(int $clientId, array $data): ClientBilling;

    public function updateStatus(ClientBilling $invoice, string $status): ClientBilling;

    public function nextInvoiceNumber(): string;

    public function revenueForAgency(int $agencyId): array;
}
