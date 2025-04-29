<?php

namespace App\Http\Controllers\Committee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Committee;
use App\Models\CommitteeMember;
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
            ->orderBy('assigned_at', 'desc') // Order by the date they were assigned
            ->orderByRaw("FIELD(role, 'leader', 'secretary', 'member')") // Custom order for rol
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

    // Add new member(s) to committee
    public function store(Request $request, Committee $committee)
    {
        try {
            $validated = $request->validate([
                'teacher_ids' => 'required|array',
                'teacher_ids.*' => 'exists:teachers,id',
                'role' => 'nullable|string|max:255',
                'status' => 'nullable|in:active,inactive',
                'is_chairperson' => 'nullable|boolean'
            ]);

            $existingMembers = $committee->members()
                ->whereIn('teacher_id', $validated['teacher_ids'])
                ->pluck('teacher_id')
                ->toArray();

            if (!empty($existingMembers)) {
                return response()->json([
                    'message' => 'Some teachers are already in the committee',
                    'duplicates' => $existingMembers,
                ], 422);
            }

            $members = [];
            foreach ($validated['teacher_ids'] as $teacherId) {
                $members[] = $committee->members()->create([
                    'teacher_id' => $teacherId,
                    'role' => $validated['role'] ?? 'member',
                    'status' => $validated['status'] ?? 'active',
                    'is_chairperson' => $validated['is_chairperson'] ?? false,
                    'assigned_at' => now(),
                ]);
            }

            return CommitteeMemberResource::collection($members);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Committee member store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to add members to committee',
                'error' => config('app.env') === 'local' ? $e->getMessage() : null
            ], 500);
        }
    }

    // Update member details (general update)
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

    // PATCH: Update only the role of a committee member
    public function patchRole(Request $request, CommitteeMember $member)
    {
        try {
            $validated = $request->validate([
                'role' => 'required|string|max:255'
            ]);

            $member->update(['role' => $validated['role']]);
            return new CommitteeMemberResource($member->fresh()->load(['teacher', 'committee']));
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Committee member patch role error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update member role',
                'error' => config('app.env') === 'local' ? $e->getMessage() : null
            ], 500);
        }
    }

    // Remove member from committee
    public function destroy($id)
    {
        $member = CommitteeMember::findOrFail($id);
        $member->delete();
        return response()->json([
            'status' => true,
            'message' => 'Member removed from committee successfully'
        ], 200);
    }
}
