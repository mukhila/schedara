<?php

namespace App\Repositories\Contracts;

use App\Models\AgencyClient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AgencyClientRepositoryInterface
{
    public function allForAgency(int $agencyId, array $filters = []): LengthAwarePaginator;

    public function findByUuid(string $uuid, int $agencyId): ?AgencyClient;

    public function create(int $agencyId, array $data): AgencyClient;

    public function update(AgencyClient $client, array $data): AgencyClient;

    public function delete(AgencyClient $client): void;

    public function countByAgency(int $agencyId): int;

    public function activeClients(int $agencyId): Collection;
}
