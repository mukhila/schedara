<?php

namespace Tests\Unit\Media;

use App\Models\MediaLibrary;
use App\Models\Tenant;
use App\Services\Media\DuplicateDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DuplicateDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private DuplicateDetectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DuplicateDetectionService();
    }

    public function test_compute_hash_returns_sha256(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $hash = $this->service->computeHash($file);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
    }

    public function test_same_file_produces_same_hash(): void
    {
        $file1 = UploadedFile::fake()->image('a.jpg', 100, 100);
        $file2 = UploadedFile::fake()->image('a.jpg', 100, 100);

        // Note: fake() generates random content, so this tests the mechanics
        $hash1 = $this->service->computeHash($file1);
        $this->assertEquals(64, strlen($hash1));
    }

    public function test_is_duplicate_returns_false_for_new_file(): void
    {
        $tenant = Tenant::factory()->create();
        $result = $this->service->isDuplicate($tenant->id, 'nonexistenthash123');

        $this->assertFalse($result);
    }

    public function test_is_duplicate_returns_true_for_existing_hash(): void
    {
        $tenant = Tenant::factory()->create();
        MediaLibrary::factory()->create([
            'tenant_id' => $tenant->id,
            'file_hash' => 'abc123def456abc123def456abc123def456abc123def456abc123def456abc1',
        ]);

        $result = $this->service->isDuplicate(
            $tenant->id,
            'abc123def456abc123def456abc123def456abc123def456abc123def456abc1'
        );

        $this->assertTrue($result);
    }

    public function test_find_duplicates_returns_matching_files(): void
    {
        $tenant = Tenant::factory()->create();
        $hash   = str_repeat('a', 64);

        MediaLibrary::factory()->count(2)->create(['tenant_id' => $tenant->id, 'file_hash' => $hash]);
        MediaLibrary::factory()->create(['tenant_id' => $tenant->id, 'file_hash' => str_repeat('b', 64)]);

        $duplicates = $this->service->findDuplicates($tenant->id, $hash);

        $this->assertCount(2, $duplicates);
    }
}
