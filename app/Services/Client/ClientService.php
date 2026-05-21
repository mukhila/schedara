<?php

namespace App\Services\Client;

use App\DTOs\Client\CreateClientDTO;
use App\DTOs\Client\UpdateClientDTO;
use App\Events\Client\ClientCreated;
use App\Events\Client\ClientOnboarded;
use App\Models\AgencyClient;
use App\Models\ClientOnboarding;
use App\Models\ClientWorkspace;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Contracts\AgencyClientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientService
{
    private const ONBOARDING_STEPS = [
        ['step' => 'profile',   'order' => 1],
        ['step' => 'branding',  'order' => 2],
        ['step' => 'social',    'order' => 3],
        ['step' => 'team',      'order' => 4],
        ['step' => 'content',   'order' => 5],
        ['step' => 'billing',   'order' => 6],
    ];

    public function __construct(
        private readonly AgencyClientRepositoryInterface $clientRepository,
    ) {}

    public function listClients(Tenant $agency, array $filters = []): LengthAwarePaginator
    {
        return $this->clientRepository->allForAgency($agency->id, $filters);
    }

    public function createClient(Tenant $agency, CreateClientDTO $dto): AgencyClient
    {
        return DB::transaction(function () use ($agency, $dto) {
            $client = $this->clientRepository->create($agency->id, [
                'client_name'  => $dto->clientName,
                'company_name' => $dto->companyName,
                'email'        => $dto->email,
                'phone'        => $dto->phone,
                'website'      => $dto->website,
                'industry'     => $dto->industry,
                'logo'         => $dto->logo,
                'timezone'     => $dto->timezone,
                'status'       => 'onboarding',
            ]);

            // Create default workspace
            $workspace = ClientWorkspace::create([
                'agency_client_id' => $client->id,
                'workspace_name'   => $dto->workspaceName ?? ($dto->companyName ?? $dto->clientName) . ' Workspace',
                'status'           => 'active',
                'settings'         => ['timezone' => $dto->timezone],
            ]);

            // Initialize onboarding steps
            $steps = array_map(fn ($s) => [
                'agency_client_id' => $client->id,
                'onboarding_step'  => $s['step'],
                'step_order'       => $s['order'],
                'status'           => $s['order'] === 1 ? 'in_progress' : 'pending',
                'created_at'       => now(),
                'updated_at'       => now(),
            ], self::ONBOARDING_STEPS);

            ClientOnboarding::insert($steps);

            event(new ClientCreated($client, $workspace));

            return $client->load(['workspace', 'onboardingSteps']);
        });
    }

    public function updateClient(AgencyClient $client, UpdateClientDTO $dto): AgencyClient
    {
        return $this->clientRepository->update($client, $dto->toArray());
    }

    public function deleteClient(AgencyClient $client): void
    {
        $this->clientRepository->delete($client);
    }

    public function completeOnboardingStep(AgencyClient $client, string $step, array $stepData = []): bool
    {
        $onboarding = ClientOnboarding::where('agency_client_id', $client->id)
            ->where('onboarding_step', $step)
            ->first();

        if (!$onboarding) {
            return false;
        }

        $onboarding->update([
            'status'       => 'completed',
            'step_data'    => $stepData,
            'completed_at' => now(),
        ]);

        // Advance next pending step to in_progress
        ClientOnboarding::where('agency_client_id', $client->id)
            ->where('status', 'pending')
            ->orderBy('step_order')
            ->first()
            ?->update(['status' => 'in_progress']);

        // Check if all steps done
        $allDone = !ClientOnboarding::where('agency_client_id', $client->id)
            ->whereNotIn('status', ['completed', 'skipped'])
            ->exists();

        if ($allDone) {
            $client->update(['status' => 'active']);
            event(new ClientOnboarded($client));
        }

        return true;
    }

    public function agencyDashboardStats(Tenant $agency): array
    {
        $total      = AgencyClient::where('agency_id', $agency->id)->count();
        $active     = AgencyClient::where('agency_id', $agency->id)->where('status', 'active')->count();
        $onboarding = AgencyClient::where('agency_id', $agency->id)->where('status', 'onboarding')->count();

        return [
            'total_clients'      => $total,
            'active_clients'     => $active,
            'onboarding_clients' => $onboarding,
        ];
    }

    public function inviteUserToWorkspace(ClientWorkspace $workspace, User $user, string $role, User $inviter): void
    {
        $workspace->users()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'role'       => $role,
                'invited_by' => $inviter->id,
                'status'     => 'pending',
                'invited_at' => now(),
            ]
        );
    }
}
