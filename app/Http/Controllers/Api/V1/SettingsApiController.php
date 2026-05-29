<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\StudioSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsApiController extends BaseApiController
{
    public function show(StudioSettingsService $settings): JsonResponse
    {
        return $this->success($settings->all(), 'Studio settings loaded.');
    }

    public function update(Request $request, StudioSettingsService $settings): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable'],
        ]);

        $settings->setMany($validated['settings']);

        return $this->success($settings->all(), 'Studio settings updated.');
    }
}
