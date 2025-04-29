<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\Thesis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Teacher;

class TaskController extends Controller
{
    public function saveAll(Request $request)
    {
        $validated = $request->validate([
            'thesis_id' => 'required|exists:thesis,id',
            'tasks' => 'required|array',

            'tasks.*.name' => 'required|string|max:255',
            'tasks.*.subtasks' => 'required|array',

            'tasks.*.subtasks.*.name' => 'required|string|max:255',
            'tasks.*.subtasks.*.start_date' => 'required|date',
            'tasks.*.subtasks.*.end_date' => 'required|date',

            'deleted_ids' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['tasks'] as &$taskData) {
                // Save or update task
                if (str_starts_with($taskData['id'], 'temp-')) {
                    $task = Task::create([
                        'name' => $taskData['name'],
                        'thesis_id' => $validated['thesis_id'],
                    ]);
                    $taskData['id'] = $task->id;
                } else {
                    $task = Task::findOrFail($taskData['id']);
                    $task->update(['name' => $taskData['name']]);
                }

                foreach ($taskData['subtasks'] as $subtaskData) {
                    if (str_starts_with($subtaskData['id'], 'temp-')) {
                        Subtask::create([
                            'task_id' => $task->id,
                            'name' => $subtaskData['name'],
                            'start_date' => $subtaskData['start_date'],
                            'end_date' => $subtaskData['end_date'],
                            'description' => $subtaskData['description'],
                        ]);
                    } else {
                        $subtask = Subtask::findOrFail($subtaskData['id']);
                        $subtask->update([
                            'name' => $subtaskData['name'],
                            'start_date' => $subtaskData['start_date'],
                            'end_date' => $subtaskData['end_date'],
                            'description' => $subtaskData['description'],
                        ]);
                    }
                }
            }

            if (!empty($validated['deleted_ids'])) {
                Subtask::whereIn('id', $validated['deleted_ids'])->delete();
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Амжилттай хадгаллаа.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Хадгалах үед алдаа гарлаа.',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }
    /**
     * Create a new task linked to thesis
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'role' => 'required|string|in:student,supervisor',
                'thesis_id' => 'required|exists:thesis,id',
            ]);

            // $user = Auth::user();
            $thesis = Thesis::findOrFail($validatedData['thesis_id']);

            // Authorization: Check based on role
            // if ($request->role === 'student' && $user->id !== $thesis->student_id) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Unauthorized. Only the assigned student can create a task.',
            //     ], 403);
            // }

            // if ($request->role === 'supervisor' && $user->id !== $thesis->supervisor_id) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Unauthorized. Only the assigned supervisor can create a task.',
            //     ], 403);
            // }

            // Create the task with default name
            $task = Task::create([
                'thesis_id' => $validatedData['thesis_id'],
                'name' => '',
            ]);

            // Create a default subtask
            Subtask::create([
                'task_id' => $task->id,
                'name' => '',
            ]);

            // Load the subtasks relationship onto the task
            $task->load('subtasks');

            return response()->json(
                [
                    'status' => true,
                    'message' => 'task created successfully.',
                    'task' => $task,
                ],
                201,
            );
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Validation error.',
                    'errors' => $e->errors(),
                ],
                422,
            );
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Thesis not found.',
                ],
                404,
            );
        } catch (Exception $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'An unexpected error occurred.',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * update  task name
     */

    public function updateTask(Request $request, $id)
    {
        try {
            // Find the task or fail
            $task = Task::findOrFail($id);

            // Validate the request
            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
            ]);

            // Update the task
            $task->update(['name' => $validatedData['name'] ?? null]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'task updated successfully',
                    'task' => $task,
                ],
                200,
            );
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Validation error.',
                    'errors' => $e->errors(),
                ],
                422,
            );
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'task not found.',
                ],
                404,
            );
        } catch (Exception $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'An unexpected error occurred.',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Delete a task and its subtasks
     */
    public function destroy($id)
    {
        try {
            // $user = Auth::user();

            // Find the task or fail
            $task = Task::find($id);
            if (!$task) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'task not found.',
                    ],
                    404,
                );
            }

            // Find the associated thesis
            $thesis = Thesis::find($task->thesis_id);
            if (!$thesis) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Thesis not found.',
                    ],
                    404,
                );
            }

            // Authorization check
            if (!$thesis || ($user->id !== $thesis->student_id && $user->id !== $thesis->supervisor_id)) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Unauthorized. You are not allowed to delete this task.',
                    ],
                    403,
                );
            }

            // Delete the task and related subtasks
            $task->subtasks()->delete();
            $task->delete();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'task deleted successfully.',
                ],
                200,
            );
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'task not found.',
                ],
                404,
            );
        } catch (Exception $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'An unexpected error occurred.',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }
}
