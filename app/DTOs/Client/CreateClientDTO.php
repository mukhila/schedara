<?php

namespace App\DTOs\Client;

readonly class CreateClientDTO
{
    public function __construct(
        public string  $clientName,
        public string  $email,
        public ?string $companyName    = null,
        public ?string $phone          = null,
        public ?string $website        = null,
        public ?string $industry       = null,
        public ?string $logo           = null,
        public string  $timezone       = 'UTC',
        public ?string $workspaceName  = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            clientName:    $data['client_name'],
            email:         $data['email'],
            companyName:   $data['company_name'] ?? null,
            phone:         $data['phone'] ?? null,
            website:       $data['website'] ?? null,
            industry:      $data['industry'] ?? null,
            logo:          $data['logo'] ?? null,
            timezone:      $data['timezone'] ?? 'UTC',
            workspaceName: $data['workspace_name'] ?? null,
        );
    }
}
