<?php

namespace App\Services\Notifications;

use App\Models\NotificationTemplate;
use Illuminate\Support\Collection;

class NotificationTemplateService
{
    public function forTenant(?int $tenantId, string $channel = null): Collection
    {
        return NotificationTemplate::where(function ($q) use ($tenantId) {
                $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
            })
            ->when($channel, fn ($q) => $q->where('channel', $channel))
            ->where('status', 'active')
            ->orderBy('type')
            ->get();
    }

    public function find(string $type, string $channel, ?int $tenantId = null): ?NotificationTemplate
    {
        // Prefer tenant-specific template over global
        return NotificationTemplate::where('type', $type)
            ->where('channel', $channel)
            ->where('status', 'active')
            ->orderByRaw('tenant_id IS NULL ASC') // tenant-specific first
            ->when($tenantId, fn ($q) => $q->where(function ($q2) use ($tenantId) {
                $q2->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            }))
            ->first();
    }

    public function create(array $data): NotificationTemplate
    {
        return NotificationTemplate::create($data);
    }

    public function update(NotificationTemplate $template, array $data): NotificationTemplate
    {
        $template->update($data);

        return $template->fresh();
    }

    public function delete(NotificationTemplate $template): void
    {
        $template->delete();
    }

    public function render(string $type, string $channel, array $variables, ?int $tenantId = null): array
    {
        $template = $this->find($type, $channel, $tenantId);

        if (! $template) {
            return ['subject' => $variables['title'] ?? $type, 'body' => $variables['body'] ?? ''];
        }

        return $template->render($variables);
    }
}
