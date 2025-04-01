<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Committee;
use App\Models\CommitteeStudent;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommitteeStudentResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CommitteeStudentController extends Controller
{
    //
    // List all students in a committee
    public function index(Committee $committee)
    {
        try {
            $students = $committee->students()
                ->with(['student', 'committee'])
                ->paginate(10);

            return CommitteeStudentResource::collection($students);

        } catch (\Exception $e) {
            Log::error('Committee students index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve committee students',
                'error' => config('app.env') === 'local' ? $e->getMessage() : null
            ], 500);
        }
    }

    // Add student to committee
    // public function store(Request $request, Committee $committee)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'student_id' => 'required|exists:students,id',
    //             'status' => 'sometimes|string|max:255'
    //         ]);

    //         // Prevent duplicate entries
    //         if($committee->students()->where('student_id', $validated['student_id'])->exists()) {
    //             return response()->json([
    //                 'message' => 'Student already exists in this committee'
    //             ], 409);
    //         }

    //         $committeeStudent = $committee->students()->create(array_merge($validated, [
    //             'joined_at' => now()
    //         ]));

    //         return new CommitteeStudentResource($committeeStudent->load(['student', 'committee']));

    //     } catch (ValidationException $e) {
    //         return response()->json([
    //             'message' => 'Validation failed',
    //             'errors' => $e->errors()
    //         ], 422);
            
    //     } catch (\Exception $e) {
    //         Log::error('Committee student store error: ' . $e->getMessage());
    //         return response()->json([
    //             'message' => 'Failed to add student to committee',
    //             'error' => config('app.env') === 'local' ? $e->getMessage() : null
    //         ], 500);
    //     }
    // }
    public function store(Request $request, Committee $committee)
{
    $validated = $request->validate([
        'student_ids' => 'required|array',
        'student_ids.*' => 'exists:students,id',
    ]);

    $assigned = [];

    foreach ($validated['student_ids'] as $studentId) {
        $exists = $committee->students()->where('student_id', $studentId)->exists();
        if (!$exists) {
            $committee->students()->create([
                'student_id' => $studentId,
                'joined_at' => now()
            ]);
            $assigned[] = $studentId;
        }
    }

    return response()->json([
        'message' => 'Students assigned successfully',
        'assigned' => $assigned,
    ]);
}


    // Update student status
    public function update(Request $request, CommitteeStudent $committeeStudent)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|max:255'
            ]);

            $committeeStudent->update($validated);
            return new CommitteeStudentResource($committeeStudent->fresh()->load(['student', 'committee']));

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Committee student update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update student status',
                'error' => config('app.env') === 'local' ? $e->getMessage() : null
            ], 500);
        }
    }

    // Remove student from committee
    public function destroy(CommitteeStudent $committeeStudent)
    {
        try {
            $committeeStudent->delete();
            return response()->json([
                'message' => 'Student removed from committee successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Committee student destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to remove student from committee',
                'error' => config('app.env') === 'local' ? $e->getMessage() : null
            ], 500);
        }
    }
}
