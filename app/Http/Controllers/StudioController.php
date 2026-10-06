<?php

namespace App\Http\Controllers;

use App\Models\Studio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Type\Decimal;

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
            'price_per_hour' => 'required|numeric|min:0',
        ]);

        $studio = Studio::create($data);
        return response()->json($studio, 201); 
    }
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price_per_hour' => 'sometimes|required|numeric|min:0',
        ]);
        $studio = Studio::findOrFail($id);
        $studio->update($data);

        return response()->json($studio);
    }
    public function destroy(int $id): Response
    {
        $studio = Studio::findOrFail($id);
        $studio->delete();

        return response()->noContent();
    }

}
