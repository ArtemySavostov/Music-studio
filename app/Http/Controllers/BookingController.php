<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function store(
        Request $request,
        BookingService $bookingService
    ): JsonResponse {
        $data = $request->validate([
            'studio_id' => 'required|integer|exists:studios,id',
            'starts_at' => 'required|date_format:Y-m-d H:i:s|after:now',
            'ends_at' => 'required|date_format:Y-m-d H:i:s|after:starts_at',
        ]);

        $booking = $bookingService->create(
            $request->user(),
            $data
        );

        return response()->json($booking, 201);
    }
}
