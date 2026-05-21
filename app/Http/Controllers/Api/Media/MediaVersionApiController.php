<?php

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use App\Http\Resources\Media\MediaFileResource;
use App\Models\MediaLibrary;
use App\Models\MediaVersion;
use App\Services\Media\MediaVersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaVersionApiController extends Controller
{
    public function __construct(private readonly MediaVersionService $versionService) {}

    public function index(string $uuid): JsonResponse
    {
        $media    = $this->findMedia($uuid);
        $versions = $media->versions()->with('creator')->get()->map(fn ($v) => [
            'version'     => $v->version,
            'file_url'    => $v->file_url,
            'file_size'   => $v->file_size,
            'change_note' => $v->change_note,
            'created_by'  => $v->creator?->name,
            'created_at'  => $v->created_at->toIso8601String(),
        ]);

        return response()->json([
            'current_version' => $media->version,
            'versions'        => $versions,
        ]);
    }

    public function store(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'file'        => ['required', 'file', 'max:204800'],
            'change_note' => ['nullable', 'string', 'max:500'],
        ]);

        $media   = $this->findMedia($uuid);
        $version = $this->versionService->createVersion(
            $media,
            $request->file('file'),
            auth()->id(),
            $request->change_note
        );

        return response()->json([
            'message'     => 'Version created.',
            'version'     => $version->version,
            'change_note' => $version->change_note,
        ], 201);
    }

    public function restore(Request $request, string $uuid, int $version): MediaFileResource
    {
        $media = $this->findMedia($uuid);
        $media = $this->versionService->restore($media, $version, auth()->id());

        return new MediaFileResource($media);
    }

    private function findMedia(string $uuid): MediaLibrary
    {
        $tenant = app('current.tenant');
        return MediaLibrary::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();
    }
}
