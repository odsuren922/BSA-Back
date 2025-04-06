<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Committee;
use App\Models\Schedule;
use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

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

    // Шинэ хуваарь үүсгэх
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
            ]);
    
            // ✅ Convert UTC to Asia/Ulaanbaatar
            $validated['start_datetime'] = Carbon::parse($validated['start_datetime'])->timezone('Asia/Ulaanbaatar');
            $validated['end_datetime'] = Carbon::parse($validated['end_datetime'])->timezone('Asia/Ulaanbaatar');
    
            $schedule = $committee->schedules()->create($validated);
            return new ScheduleResource($schedule->load('committee'));
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'message' => 'Баталгаажуулалт амжилтгүй боллоо',
                    'errors' => $e->errors(),
                ],
                422,
            );
        } catch (\Exception $e) {
            Log::error('Хуваарь үүсгэхэд алдаа гарлаа: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Хуваарийг үүсгэж чадсангүй',
                    'error' => config('app.env') === 'local' ? $e->getMessage() : null,
                ],
                500,
            );
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
            ]);
    
            // ✅ Convert UTC to Asia/Ulaanbaatar
            $validated['start_datetime'] = Carbon::parse($validated['start_datetime'])->timezone('Asia/Ulaanbaatar');
            $validated['end_datetime'] = Carbon::parse($validated['end_datetime'])->timezone('Asia/Ulaanbaatar');
    
            $schedule->update($validated);
            return new ScheduleResource($schedule->fresh()->load('committee'));
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'message' => 'Баталгаажуулалт амжилтгүй боллоо',
                    'errors' => $e->errors(),
                ],
                422,
            );
        } catch (\Exception $e) {
            Log::error('Хуваарь шинэчлэхэд алдаа гарлаа: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Хуваарийг шинэчилж чадсангүй',
                    'error' => config('app.env') === 'local' ? $e->getMessage() : null,
                ],
                500,
            );
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
