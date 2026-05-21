<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardLayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LayoutApiController extends Controller
{
    public function __construct(private DashboardLayoutService $layouts) {}

    public function show(Request $request): JsonResponse
    {
        $user   = $request->user();
        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;
        $layout = $this->layouts->get($user->id, $tenant?->id);

        return response()->json($this->layouts->toArray($layout));
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'order'   => 'array',
            'order.*' => 'string',
            'hidden'  => 'array',
            'hidden.*'=> 'string',
            'config'  => 'array',
        ]);

        $user   = $request->user();
        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;

        $layout = $this->layouts->save(
            $user->id,
            $tenant?->id,
            $request->input('order', []),
            $request->input('hidden', []),
            $request->input('config', []),
        );

        return response()->json($this->layouts->toArray($layout));
    }

    public function reset(Request $request): JsonResponse
    {
        $user   = $request->user();
        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;

        $layout = $this->layouts->reset($user->id, $tenant?->id);

        return response()->json($this->layouts->toArray($layout));
    }
}
