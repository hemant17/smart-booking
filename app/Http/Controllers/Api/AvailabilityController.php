<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\WorkingRule;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'service_id' => 'required|exists:services,id'
        ]);

        $date = Carbon::parse($request->date);
        $service = Service::findOrFail($request->service_id);

        $workingRules = WorkingRule::where('active', true)
            ->where(function($query) use ($date) {
                $query->where(function($q) use ($date) {
                    $q->where('type', 'weekly')
                      ->where('weekday', $date->dayOfWeekIso);
                })->orWhere(function($q) use ($date) {
                    $q->where('type', 'date')
                      ->where('date', $date->toDateString());
                });
            })
            ->get();

        $availableSlots = [];
        $now = Carbon::now();

        foreach ($workingRules as $rule) {
            $startDateTime = $date->copy()->setTimeFrom($rule->start_time);
            $endDateTime = $date->copy()->setTimeFrom($rule->end_time);

            $period = new CarbonPeriod(
                $startDateTime,
                "{$rule->slot_interval} minutes",
                $endDateTime
            );

            foreach ($period as $slotStart) {
                $slotEnd = $slotStart->copy()->addMinutes($service->duration_minutes);

                if ($slotEnd->greaterThan($endDateTime)) {
                    break;
                }

                if ($date->isToday() && $slotStart->lessThan($now)) {
                    continue;
                }

                $existingAppointment = Appointment::where('service_id', $service->id)
                    ->where('start_at', '<', $slotEnd)
                    ->where('end_at', '>', $slotStart)
                    ->first();

                if (!$existingAppointment) {
                    $availableSlots[] = [
                        'start' => $slotStart->toDateTimeString(),
                        'end' => $slotEnd->toDateTimeString()
                    ];
                }
            }
        }

        return response()->json($availableSlots);
    }
}
