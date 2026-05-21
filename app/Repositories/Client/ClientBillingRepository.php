<?php

namespace App\Repositories\Client;

use App\Models\ClientBilling;
use App\Repositories\Contracts\ClientBillingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClientBillingRepository implements ClientBillingRepositoryInterface
{
    public function forClient(int $clientId, array $filters = []): LengthAwarePaginator
    {
        $query = ClientBilling::where('agency_client_id', $clientId);

        if (!empty($filters['status'])) {
            $query->where('payment_status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function findByUuid(string $uuid): ?ClientBilling
    {
        return ClientBilling::where('uuid', $uuid)->first();
    }

    public function create(int $clientId, array $data): ClientBilling
    {
        return ClientBilling::create(array_merge($data, [
            'agency_client_id' => $clientId,
            'invoice_number'   => $data['invoice_number'] ?? $this->nextInvoiceNumber(),
        ]));
    }

    public function updateStatus(ClientBilling $invoice, string $status): ClientBilling
    {
        $updates = ['payment_status' => $status];

        if ($status === 'paid') {
            $updates['paid_at'] = now();
        }

        $invoice->update($updates);

        return $invoice->fresh();
    }

    public function nextInvoiceNumber(): string
    {
        $year  = now()->format('Y');
        $month = now()->format('m');
        $count = ClientBilling::whereYear('created_at', $year)
                     ->whereMonth('created_at', $month)
                     ->count() + 1;

        return sprintf('INV-%s%s-%04d', $year, $month, $count);
    }

    public function revenueForAgency(int $agencyId): array
    {
        return DB::table('client_billing')
            ->join('agency_clients', 'agency_clients.id', '=', 'client_billing.agency_client_id')
            ->where('agency_clients.agency_id', $agencyId)
            ->where('client_billing.payment_status', 'paid')
            ->whereNull('client_billing.deleted_at')
            ->selectRaw('
                SUM(client_billing.total) as total_revenue,
                SUM(CASE WHEN MONTH(client_billing.paid_at) = MONTH(NOW()) AND YEAR(client_billing.paid_at) = YEAR(NOW()) THEN client_billing.total ELSE 0 END) as this_month,
                COUNT(DISTINCT client_billing.agency_client_id) as paying_clients
            ')
            ->first(['total_revenue', 'this_month', 'paying_clients']);
    }
}
