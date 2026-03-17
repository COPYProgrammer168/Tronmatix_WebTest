<?php

// app/Http/Controllers/Api/DeliveryScheduleController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliverySchedule;
use Illuminate\Http\Request;

class DeliveryScheduleController extends Controller
{
    // ── GET /api/delivery-schedules ───────────────────────────────────────────
    public function index()
    {
        // FIX [1]: use model accessors — no manual day_name mapping
        $schedules = DeliverySchedule::available()
            ->orderBy('day_of_week')
            ->orderBy('time_start')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'day_of_week' => $s->day_of_week,
                'day_name' => $s->day_name,       // FIX [1]: accessor
                'time_start' => $s->time_start,
                'time_end' => $s->time_end,
                'slot_label' => $s->slot_label,     // FIX [1]: accessor "08:00 – 12:00"
                'full_label' => $s->full_label,     // FIX [1]: "Monday: 08:00 – 12:00"
                'is_available' => $s->is_available,
            ]);

        return response()->json(['success' => true, 'data' => $schedules]);
    }

    // ── GET /api/delivery-schedules/day/{day} ─────────────────────────────────
    // Used by checkout date picker to get available slots for a given weekday
    public function forDay(int $dayOfWeek)
    {
        $schedules = DeliverySchedule::forDay($dayOfWeek) // scopeForDay added to model
            ->orderBy('time_start')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'time_start' => $s->time_start,
                'time_end' => $s->time_end,
                'slot_label' => $s->slot_label,
                'full_label' => $s->full_label,
            ]);

        return response()->json(['success' => true, 'data' => $schedules]);
    }

    // ── FIX [2]: Admin CRUD endpoints ─────────────────────────────────────────

    // POST /api/delivery-schedules
    public function store(Request $request)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|integer|min:0|max:6',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'is_available' => 'boolean',
        ]);

        $schedule = DeliverySchedule::create($validated);

        return response()->json(['success' => true, 'data' => $schedule], 201);
    }

    // PUT /api/delivery-schedules/{id}
    public function update(Request $request, DeliverySchedule $deliverySchedule)
    {
        $validated = $request->validate([
            'day_of_week' => 'sometimes|required|integer|min:0|max:6',
            'time_start' => 'sometimes|required|date_format:H:i',
            'time_end' => 'sometimes|required|date_format:H:i',
            'is_available' => 'boolean',
        ]);

        $deliverySchedule->update($validated);

        return response()->json(['success' => true, 'data' => $deliverySchedule->fresh()]);
    }

    // DELETE /api/delivery-schedules/{id}
    public function destroy(DeliverySchedule $deliverySchedule)
    {
        $deliverySchedule->delete();

        return response()->json(['success' => true]);
    }
}
