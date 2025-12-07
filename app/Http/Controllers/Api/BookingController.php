<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // Validate the booking request
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'start_at' => 'required|date|after:now',
            'client_email' => 'required|email'
        ]);

        $service = Service::findOrFail($request->service_id);
        $startAt = Carbon::parse($request->start_at);
        $endAt = $startAt->copy()->addMinutes($service->duration_minutes);

        // Use transaction to prevent race conditions
        return DB::transaction(function () use ($request, $service, $startAt, $endAt) {
            // Check for overlapping appointments
            $conflict = Appointment::where('service_id', $service->id)
                ->where(function($query) use ($startAt, $endAt) {
                    $query->where('start_at', '<', $endAt)
                          ->where('end_at', '>', $startAt);
                })
                ->first();

            if ($conflict) {
                // TODO: Add more specific error message about when the slot is taken
                return response()->json([
                    'message' => 'Time slot is already booked'
                ], 409);
            }

            // Create the appointment
            $appointment = Appointment::create([
                'service_id' => $service->id,
                'client_email' => $request->client_email,
                'start_at' => $startAt,
                'end_at' => $endAt
            ]);

            return response()->json($appointment, 201);
        });
    }
}
