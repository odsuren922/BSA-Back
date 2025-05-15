<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Committee;
use App\Models\Schedule;
use App\Http\Controllers\Controller;
use App\Models\ThesisCycleDeadline;
use App\Http\Resources\ScheduleResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Arr;


class ScheduleController extends Controller
{
    // Тухайн хорооны бүх хуваарийг авах
    public function index(Committee $committee)
    {
        try {
            $schedules = $committee->schedules()->with('committee')->paginate(10);

            return ScheduleResource::collection($schedules);
        } catch (\Exception $e) {
            Log::error('Хуваарь авахад алдаа гарлаа: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Хуваарийг татаж чадсангүй',
                    'error' => config('app.env') === 'local' ? $e->getMessage() : null,
                ],
                500,
            );
        }
    }

    
    public function store(Request $request, Committee $committee)
{
    try {
        $validated = $request->validate([
            'event_type' => 'required|string|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
            'location' => 'required|string|max:255',
            'room' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        
            'deadline_start' => 'required|string',
            'deadline_end' => 'required|string',
        ]);

      
        $schedule = $committee->schedules()->create($validated);

        $thesisCycle = $committee->thesis_cycle()->first();

      
                ThesisCycleDeadline::create([
                    'thesis_cycle_id' => $thesisCycle->id,
                    'type' => 'committee', // Та хүсвэл өөрчлөлт хийж болно
                    'related_id' => $committee->id,
                    'related_type' => Committee::class,
                    'title' => null, // хэрэгтэй бол илгээж болно
                    'description' => null,
                    'start_date' => $validated['deadline_start'],
                    'end_date' => $validated['deadline_end'],
                ]);
         

        return new ScheduleResource($schedule->load('committee'));
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Баталгаажуулалт амжилтгүй боллоо',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Хуваарь үүсгэхэд алдаа гарлаа: ' . $e->getMessage());
        return response()->json([
            'message' => 'Хуваарийг үүсгэж чадсангүй',
            'error' => config('app.env') === 'local' ? $e->getMessage() : null,
        ], 500);
    }
}



    // Хуваарийг шинэчлэх
    public function update(Request $request, Schedule $schedule)
    {
        try {
            $validated = $request->validate([
                'event_type' => 'sometimes|string|max:255',
                'start_datetime' => 'required|date',
                'end_datetime' => 'nullable|date|after:start_datetime',
                'location' => 'sometimes|string|max:255',
                'room' => 'nullable|string|max:50',
                'notes' => 'nullable|string',
                'deadline_start' => 'required_with:deadline_end|string',
                'deadline_end' => 'required_with:deadline_start|string',
            ]);
    
            // Schedule update
            $schedule->update($validated);
    
            // Update or create ThesisCycleDeadline
            if (isset($validated['deadline_start']) && isset($validated['deadline_end'])) {
                $committee = $schedule->committee;
                $thesisCycle = $committee->thesis_cycle()->first();
    
                if ($thesisCycle) {
                    ThesisCycleDeadline::updateOrCreate(
                        [
                            'thesis_cycle_id' => $thesisCycle->id,
                            'type' => 'committee',
                            'related_id' => $committee->id,
                            'related_type' => Committee::class,
                        ],
                        [
                            'title' => null,
                            'description' => null,
                            'start_date' => Carbon::parse($validated['deadline_start'])->toDateTimeString(),
                            'end_date' => Carbon::parse($validated['deadline_end'])->toDateTimeString(),
                        ]
                    );
                }
            }
    
            return new ScheduleResource($schedule->fresh()->load('committee'));
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Баталгаажуулалт амжилтгүй боллоо',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Хуваарь шинэчлэхэд алдаа гарлаа: ' . $e->getMessage());
            return response()->json([
                'message' => 'Хуваарийг шинэчилж чадсангүй',
                'error' => config('app.env') === 'local' ? $e->getMessage() : null,
            ], 500);
        }
    }
    
    
    // Хуваарийг устгах
    public function destroy(Committee $committee, Schedule $schedule)
    {
        try {
            $schedule->delete();
            return response()->json(['message' => 'Хуваарь амжилттай устгагдлаа']);
        } catch (\Exception $e) {
            Log::error('Хуваарь устгахад алдаа гарлаа: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Хуваарийг устгаж чадсангүй',
                    'error' => config('app.env') === 'local' ? $e->getMessage() : null,
                ],
                500,
            );
        }
    }
}
