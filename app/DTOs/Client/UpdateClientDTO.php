<?php

namespace App\DTOs\Client;

readonly class UpdateClientDTO
{
    public function __construct(
        public ?string $clientName  = null,
        public ?string $companyName = null,
        public ?string $email       = null,
        public ?string $phone       = null,
        public ?string $website     = null,
        public ?string $industry    = null,
        public ?string $logo        = null,
        public ?string $timezone    = null,
        public ?string $status      = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            clientName:  $data['client_name'] ?? null,
            companyName: $data['company_name'] ?? null,
            email:       $data['email'] ?? null,
            phone:       $data['phone'] ?? null,
            website:     $data['website'] ?? null,
            industry:    $data['industry'] ?? null,
            logo:        $data['logo'] ?? null,
            timezone:    $data['timezone'] ?? null,
            status:      $data['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'client_name'  => $this->clientName,
            'company_name' => $this->companyName,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'website'      => $this->website,
            'industry'     => $this->industry,
            'logo'         => $this->logo,
            'timezone'     => $this->timezone,
            'status'       => $this->status,
        ], fn ($v) => $v !== null);
    }
}
