<?php

namespace App\Http\Controllers\Committee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Committee;
use App\Models\CommitteeStudent;
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
            $students = $committee
                ->students()
                ->with(['student', 'committee'])
                ->paginate(10);

            return CommitteeStudentResource::collection($students);
        } catch (\Exception $e) {
            Log::error('Committee students index error: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Failed to retrieve committee students',
                    'error' => config('app.env') === 'local' ? $e->getMessage() : null,
                ],
                500,
            );
        }
    }

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
                $assigned[] = $committee->students()->create([
                    'student_id' => $studentId,
                    'joined_at' => now(),
                ]);
                // $assigned[] = $studentId;
            }
        }
        return CommitteeStudentResource::collection($assigned);

        // return response()->json([
        //     'message' => 'Students assigned successfully',
        //     'assigned' => $assigned,
        // ]);
    }

    // Update student status
    public function update(Request $request, CommitteeStudent $committeeStudent)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|max:255',
            ]);

            $committeeStudent->update($validated);
            return new CommitteeStudentResource($committeeStudent->fresh()->load(['student', 'committee']));
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ],
                422,
            );
        } catch (\Exception $e) {
            Log::error('Committee student update error: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Failed to update student status',
                    'error' => config('app.env') === 'local' ? $e->getMessage() : null,
                ],
                500,
            );
        }
    }

    // Remove student from committee
    // public function destroy(CommitteeStudent $committeeStudent)
    // {
    //     try {
    //         $committeeStudent->delete();
    //         return response()->json([
    //             'message' => 'Student removed from committee successfully'
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Committee student destroy error: ' . $e->getMessage());
    //         return response()->json([
    //             'message' => 'Failed to remove student from committee',
    //             'error' => config('app.env') === 'local' ? $e->getMessage() : null
    //         ], 500);
    //     }
    // }

    public function destroy($committeeId, $studentId)
    {
        try {
            $committeeStudent = CommitteeStudent::where('committee_id', $committeeId)->where('id', $studentId)->firstOrFail();

            $committeeStudent->delete();

            return response()->json([
                'message' => 'Student removed from committee successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Committee student destroy error: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Failed to remove student from committee',
                    'error' => config('app.env') === 'local' ? $e->getMessage() : null,
                ],
                500,
            );
        }
    }
}
