<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Station;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $reservations = $request->user()
            ->reservations()
            ->with('station')
            ->orderBy('start_time')
            ->get();

        return response()->json($reservations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $station = Station::query()->findOrFail($validated['station_id']);

        if ($station->status !== 'available') {
            return response()->json([
                'message' => 'This station is not available for reservation.'
            ]);
        }

        $hasConflict = $station->reservations()
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time'])
            ->exists();

        if ($hasConflict) {
            return response()->json([
                'message' => 'This station is already reserved for the selected time slot.'
            ]);
        }

        $reservation = $request->user()->reservations()->create([
            'station_id' => $station->id,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Reservation created successfully.',
            'reservation' => $reservation->load(['station', 'user']),
        ], 201);
    }
}
