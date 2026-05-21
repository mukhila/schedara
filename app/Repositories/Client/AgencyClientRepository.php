<?php

namespace App\Repositories\Client;

use App\Models\AgencyClient;
use App\Repositories\Contracts\AgencyClientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AgencyClientRepository implements AgencyClientRepositoryInterface
{
    public function allForAgency(int $agencyId, array $filters = []): LengthAwarePaginator
    {
        $query = AgencyClient::with(['workspace', 'onboardingSteps'])
            ->where('agency_id', $agencyId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', $search)
                  ->orWhere('company_name', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }

        if (!empty($filters['industry'])) {
            $query->where('industry', $filters['industry']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function findByUuid(string $uuid, int $agencyId): ?AgencyClient
    {
        return AgencyClient::with(['workspace.whiteLabelSettings', 'onboardingSteps', 'billing'])
            ->where('uuid', $uuid)
            ->where('agency_id', $agencyId)
            ->first();
    }

    public function create(int $agencyId, array $data): AgencyClient
    {
        return AgencyClient::create(array_merge($data, ['agency_id' => $agencyId]));
    }

    public function update(AgencyClient $client, array $data): AgencyClient
    {
        $client->update($data);

        return $client->fresh();
    }

    public function delete(AgencyClient $client): void
    {
        $client->delete();
    }

    public function countByAgency(int $agencyId): int
    {
        return AgencyClient::where('agency_id', $agencyId)->count();
    }

    public function activeClients(int $agencyId): Collection
    {
        return AgencyClient::where('agency_id', $agencyId)
            ->where('status', 'active')
            ->with('workspace')
            ->get();
    }
}
