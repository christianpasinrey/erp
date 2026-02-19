<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Nnjeim\World\World;

class WorldController extends Controller
{
    public function countries(): JsonResponse
    {
        $result = World::countries([
            'fields' => 'id,name,iso2',
            'filters' => ['is_active' => true],
        ]);

        return response()->json($result->data ?? []);
    }

    public function states(string $countryCode): JsonResponse
    {
        $result = World::countries([
            'fields' => 'id',
            'filters' => ['iso2' => $countryCode],
        ]);

        $country = $result->data->first();
        if (! $country) {
            return response()->json([]);
        }

        $statesResult = World::states([
            'fields' => 'id,name',
            'filters' => ['country_id' => $country->id],
        ]);

        return response()->json($statesResult->data ?? []);
    }

    public function cities(int $stateId): JsonResponse
    {
        $result = World::cities([
            'fields' => 'id,name',
            'filters' => ['state_id' => $stateId],
        ]);

        return response()->json($result->data ?? []);
    }
}
