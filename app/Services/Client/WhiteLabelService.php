<?php

namespace App\Services\Client;

use App\Events\Client\WhiteLabelUpdated;
use App\Models\ClientWorkspace;
use App\Models\WhiteLabelSetting;
use App\Repositories\Contracts\WhiteLabelRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class WhiteLabelService
{
    public function __construct(
        private readonly WhiteLabelRepositoryInterface $whiteLabelRepository,
    ) {}

    public function getSettings(ClientWorkspace $workspace): ?WhiteLabelSetting
    {
        return Cache::remember(
            "wl_settings_{$workspace->id}",
            now()->addHour(),
            fn () => $this->whiteLabelRepository->findByWorkspace($workspace->id)
        );
    }

    public function getSettingsByDomain(string $domain): ?WhiteLabelSetting
    {
        return Cache::remember(
            "wl_domain_{$domain}",
            now()->addHour(),
            fn () => $this->whiteLabelRepository->findByDomain($domain)
        );
    }

    public function updateSettings(ClientWorkspace $workspace, array $data): WhiteLabelSetting
    {
        $settings = $this->whiteLabelRepository->upsert($workspace->id, $data);

        // Bust cache
        Cache::forget("wl_settings_{$workspace->id}");
        if (!empty($data['custom_domain'])) {
            Cache::forget("wl_domain_{$data['custom_domain']}");
        }

        // Sync workspace domain
        if (!empty($data['custom_domain'])) {
            $workspace->update(['domain' => $data['custom_domain']]);
        }

        event(new WhiteLabelUpdated($settings, $workspace));

        return $settings;
    }

    public function verifyDomain(WhiteLabelSetting $settings): bool
    {
        $domain = $settings->custom_domain;
        if (!$domain) {
            return false;
        }

        // DNS TXT record check: looks for schedara-verify=<workspace_id>
        $expected = 'schedara-verify=' . $settings->client_workspace_id;
        $records  = @dns_get_record($domain, DNS_TXT) ?: [];

        foreach ($records as $record) {
            if (isset($record['txt']) && str_contains($record['txt'], $expected)) {
                $settings->update(['domain_verified' => true]);
                Cache::forget("wl_domain_{$domain}");
                return true;
            }
        }

        return false;
    }

    public function generateCssVariables(WhiteLabelSetting $settings): string
    {
        return ":root { {$settings->cssVariables()} }";
    }
}
