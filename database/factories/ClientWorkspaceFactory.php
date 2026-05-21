<?php

namespace Database\Factories;

use App\Models\AgencyClient;
use App\Models\ClientWorkspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientWorkspaceFactory extends Factory
{
    protected $model = ClientWorkspace::class;

    public function definition(): array
    {
        return [
            'uuid'              => (string) Str::uuid(),
            'agency_client_id'  => AgencyClient::factory(),
            'workspace_name'    => $this->faker->company() . ' Workspace',
            'status'            => 'active',
            'settings'          => ['timezone' => 'UTC'],
        ];
    }
}
