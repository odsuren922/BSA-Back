<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Committee;
use App\Models\Schedule;
use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    // Get all schedules for a committee
    public function index(Committee $committee)
    {
        try {
            $schedules = $committee->schedules()
                ->with('committee')
                ->orderBy('date')
                ->paginate(10);

            return ScheduleResource::collection($schedules);

        } catch (\Exception $e) {
            Log::error('Schedule index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve schedules',
                'error' => config('app.env') === 'local' ? $e->getMessage() : null
            ], 500);
        }
    }

    // Create new schedule
    public function store(Request $request, Committee $committee)
    {
        try {
            $validated = $request->validate([
                'event_type' => 'required|string|max:255',
                'date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
                'location' => 'required|string|max:255',
                'room' => 'nullable|string|max:50',
                'notes' => 'nullable|string'
            ]);

            $schedule = $committee->schedules()->create($validated);
            return new ScheduleResource($schedule->load('committee'));

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Schedule store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create schedule',
                'error' => config('app.env') === 'local' ? $e->getMessage() : null
            ], 500);
        }
    }

    // Update schedule
    public function update(Request $request, Committee $committee, Schedule $schedule)
    {
        try {
            $validated = $request->validate([
                'event_type' => 'sometimes|string|max:255',
                'date' => 'sometimes|date',
                'start_time' => 'sometimes|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
                'location' => 'sometimes|string|max:255',
                'room' => 'nullable|string|max:50',
                'notes' => 'nullable|string'
            ]);

            $schedule->update($validated);
            return new ScheduleResource($schedule->fresh()->load('committee'));

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Schedule update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update schedule',
                'error' => config('app.env') === 'local' ? $e->getMessage() : null
            ], 500);
        }
    }

    // Delete schedule
    public function destroy(Committee $committee, Schedule $schedule)
    {
        try {
            $schedule->delete();
            return response()->json(['message' => 'Schedule deleted successfully']);

        } catch (\Exception $e) {
            Log::error('Schedule destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete schedule',
                'error' => config('app.env') === 'local' ? $e->getMessage() : null
            ], 500);
        }
    }
}