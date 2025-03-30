<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommitteeMemberResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CommitteeMemberController extends Controller
{
    // Get all members for a committee
    public function index(Committee $committee)
    {
        try {
            $members = $committee->members()
                ->with(['teacher', 'committee'])
                ->paginate(10);

            return CommitteeMemberResource::collection($members);
            
        } catch (\Exception $e) {
            Log::error('Committee members index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve committee members',
                'error' => config('app.env') === 'local' ? $e->getMessage() : null
            ], 500);
        }
    }
// Add new member to committee
public function store(Request $request, Committee $committee)
{
    try {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'role' => 'required|string|max:255',
            'status' => 'sometimes|in:active,inactive',
            'is_chairperson' => 'sometimes|boolean'
        ]);

        $member = $committee->members()->create(array_merge($validated, [
            'assigned_at' => now()
        ]));

        return new CommitteeMemberResource($member->load(['teacher', 'committee']));

    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        Log::error('Committee member store error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to add member to committee',
            'error' => config('app.env') === 'local' ? $e->getMessage() : null
        ], 500);
    }
}

// Update member details
public function update(Request $request, CommitteeMember $member)
{
    try {
        $validated = $request->validate([
            'role' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:active,inactive',
            'is_chairperson' => 'sometimes|boolean'
        ]);

        $member->update($validated);
        return new CommitteeMemberResource($member->fresh()->load(['teacher', 'committee']));

    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        Log::error('Committee member update error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to update committee member',
            'error' => config('app.env') === 'local' ? $e->getMessage() : null
        ], 500);
    }
}

// Remove member from committee
public function destroy(CommitteeMember $member)
{
    try {
        $member->delete();
        return response()->json([
            'message' => 'Member removed from committee successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('Committee member destroy error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to remove member from committee',
            'error' => config('app.env') === 'local' ? $e->getMessage() : null
        ], 500);
    }
}
}
