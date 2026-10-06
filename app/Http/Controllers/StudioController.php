<?php

namespace App\Http\Controllers;

use App\Models\Studio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StudioController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Studio::all());
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Studio::findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_hour' => 'required|numeric|decimal:0,2|min:0|max:99999999.99',
            'has_piano' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $studio = Studio::create($data);

        return response()->json($studio, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price_per_hour' => 'sometimes|required|numeric|decimal:0,2|min:0|max:99999999.99',
            'has_piano' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);
        $studio = Studio::findOrFail($id);
        $studio->update($data);

        return response()->json($studio);
    }

    public function destroy(int $id): Response|JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $studio = Studio::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($studio->booking()->exists()) {
                return response()->json([
                    'message' => 'У студии есть бронирования. Вместо удаления отключите её в каталоге.',
                ], 409);
            }
            $studio->delete();

            return response()->noContent();
        });
    }
}
