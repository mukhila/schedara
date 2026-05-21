<?php

namespace Tests\Feature\Analytics;

use App\Models\AnalyticsReport;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Analytics\ReportGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReportGenerationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User   $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user   = User::factory()->create();
        $this->tenant->users()->attach($this->user, ['role' => 'owner', 'joined_at' => now()]);
    }

    public function test_create_report_persists_and_queues_job(): void
    {
        Queue::fake();

        $service = app(ReportGenerationService::class);
        $report  = $service->create($this->tenant->id, $this->user->id, [
            'name'      => 'May Engagement Report',
            'type'      => 'engagement',
            'date_from' => '2026-05-01',
            'date_to'   => '2026-05-31',
            'format'    => 'pdf',
        ]);

        $this->assertDatabaseHas('analytics_reports', [
            'tenant_id' => $this->tenant->id,
            'name'      => 'May Engagement Report',
            'status'    => 'pending',
        ]);

        Queue::assertPushed(\App\Jobs\Analytics\GenerateReportJob::class);
    }

    public function test_report_expires_in_7_days(): void
    {
        $service = app(ReportGenerationService::class);
        $report  = $service->create($this->tenant->id, $this->user->id, [
            'name'      => 'Expiry Test',
            'date_from' => '2026-05-01',
            'date_to'   => '2026-05-15',
        ]);

        $this->assertNotNull($report->expires_at);
        $this->assertGreaterThan(now(), $report->expires_at);
    }

    public function test_mark_ready_updates_status(): void
    {
        $report  = AnalyticsReport::create([
            'tenant_id'  => $this->tenant->id,
            'created_by' => $this->user->id,
            'name'       => 'Test',
            'type'       => 'custom',
            'status'     => 'processing',
            'date_from'  => '2026-05-01',
            'date_to'    => '2026-05-31',
        ]);

        $service = app(ReportGenerationService::class);
        $service->markReady($report);

        $this->assertEquals('ready', $report->fresh()->status);
        $this->assertNotNull($report->fresh()->generated_at);
    }

    public function test_report_api_lists_tenant_reports(): void
    {
        $this->actingAs($this->user, 'sanctum');

        AnalyticsReport::create([
            'tenant_id'  => $this->tenant->id,
            'created_by' => $this->user->id,
            'name'       => 'Visible Report',
            'type'       => 'custom',
            'date_from'  => '2026-05-01',
            'date_to'    => '2026-05-31',
        ]);

        $this->getJson('/api/analytics/reports', ['X-Tenant-ID' => $this->tenant->id])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Visible Report']);
    }
}
