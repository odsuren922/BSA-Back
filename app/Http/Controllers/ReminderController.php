<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Resources\ReminderResource;


class ReminderController extends Controller
{
    /**
     * Store new reminders.
     */
    public function getByCycle($id)
{
    $reminders = Reminder::with(['schedules'])
        ->where('thesis_cycle_id', $id)
        ->get();

    return ReminderResource::collection($reminders);
}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'thesis_cycle_id' => 'required|exists:thesis_cycles,id',
            'component_id' => 'nullable|exists:grading_components,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_type' => 'required|in:all,student,teacher',
            'send_schedules' => 'required|array|min:1',
            'send_schedules.*.datetime' => 'required|string',
        ]);
    
        $reminder = Reminder::create([
            'thesis_cycle_id' => $validated['thesis_cycle_id'],
            'component_id' => $validated['component_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'target_type' => $validated['target_type'],
        ]);
    
        foreach ($validated['send_schedules'] as $schedule) {
            $reminder->schedules()->create([
                'scheduled_at' => $schedule['datetime'],
            ]);
        }
    
        return response()->json([
            'message' => 'Reminder & schedules saved successfully.',
            'data' => new ReminderResource($reminder->load('schedules'))
        ], 201);
    }
    public function update(Request $request, Reminder $reminder)
{
    $validated = $request->validate([
        'thesis_cycle_id' => 'required|exists:thesis_cycles,id',
        'component_id' => 'nullable|exists:grading_components,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'target_type' => 'required|in:all,student,teacher',
        'send_schedules' => 'required|array|min:1',
        'send_schedules.*.datetime' => 'required|string',
    ]);

    // Update main reminder fields
    $reminder->update([
        'thesis_cycle_id' => $validated['thesis_cycle_id'],
        'component_id' => $validated['component_id'],
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'target_type' => $validated['target_type'],
    ]);

    // Delete old schedules and recreate
    $reminder->schedules()->delete();

    foreach ($validated['send_schedules'] as $schedule) {
        $reminder->schedules()->create([
            'scheduled_at' => $schedule['datetime'],
        ]);
    }

    return response()->json([
        'message' => 'Reminder updated successfully.',
        'data' => new ReminderResource($reminder->load('schedules'))
    ], 200);
}

    
    

    /**
     * Optional: Get reminders for a component or cycle
     */
    public function index(Request $request)
    {
        $query = Reminder::query();

        if ($request->has('thesis_cycle_id')) {
            $query->where('thesis_cycle_id', $request->thesis_cycle_id);
        }

        if ($request->has('component_id')) {
            $query->where('component_id', $request->component_id);
        }

        return response()->json($query->latest('scheduled_at')->get());
    }
    public function destroy(Reminder $reminder)
{
    $reminder->schedules()->delete(); // delete related schedules
    $reminder->delete();              // delete the reminder itself

    return response()->json([
        'message' => 'Reminder and its schedules were deleted successfully.'
    ], 200);
}

}
