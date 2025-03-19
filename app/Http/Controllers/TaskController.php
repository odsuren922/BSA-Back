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
    /**
     * Create a new task linked to thesis
     */
    public function store(Request $request)
{
    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'role' => 'nullable|string|in:student,supervisor',
            'thesis_id' => 'required|exists:thesis,id',
        ]);

        $user = Auth::user();
        $thesis = Thesis::findOrFail($validatedData['thesis_id']);

        // Authorization: Check based on role
        if ($request->role === 'student' && $user->id !== $thesis->student_id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Only the assigned student can create a task.',
            ], 403);
        }

        if ($request->role === 'supervisor' && $user->id !== $thesis->supervisor_id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Only the assigned supervisor can create a task.',
            ], 403);
        }

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

        return response()->json([
            'status' => true,
            'message' => 'task created successfully.',
            'task' => $task
        ], 201);
    } catch (ValidationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error.',
            'errors' => $e->errors()
        ], 422);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Thesis not found.',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An unexpected error occurred.',
            'error' => $e->getMessage(),
        ], 500);
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
    
            return response()->json([
                'status' => true,
                'message' => 'task updated successfully',
                'task' => $task
            ], 200);
    
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'task not found.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    


  /**
  * Get only the tasks related to the logged-in user
  */

 public function index(Request $request)
{
    try {
        // Validate the request
        $validatedData = $request->validate([
            'thesis_id' => 'required|exists:thesis,id',
        ]);

        $thesis = Thesis::find($validatedData['thesis_id']);
        if (!$thesis) {
            return response()->json([
                'status' => false,
                'message' => 'Thesis not found.',
            ], 404);
        }

        // Fetch tasks related to the thesis
        // $tasks = Task::where('thesis_id', $validatedData['thesis_id'])
        //                 ->with('subtasks') // Load related subtasks
        //                 ->orderBy('created_at', 'asc')
        //                 ->get();
        $tasks = Task::where('thesis_id', $validatedData['thesis_id'])
            ->with(['subtasks' => function ($query) {
            $query->orderBy('created_at', 'asc'); // Subtasks-ийг ascending буюу эрт үүссэнээс сүүлд үүссэн дарааллаар
         }])
            ->orderBy('created_at', 'asc') // Tasks-ийг бас үүссэн хугацаагаар эрэмбэлэх
            ->get();


        return response()->json([
            'status' => true,
            'message' => 'tasks retrieved successfully.',
            'tasks' => $tasks
        ], 200);

    } catch (ValidationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error.',
            'errors' => $e->errors()
        ], 422);
    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An unexpected error occurred.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

  

/**
* Delete a task and its subtasks
*/
public function destroy($id)
{
    try {
        $user = Auth::user();

        // Find the task or fail
        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'status' => false,
                'message' => 'task not found.',
            ], 404);
        }

        // Find the associated thesis
        $thesis = Thesis::find($task->thesis_id);
        if (!$thesis) {
            return response()->json([
                'status' => false,
                'message' => 'Thesis not found.',
            ], 404);
        }

        // Authorization check
        if (!$thesis || ($user->id !== $thesis->student_id && $user->id !== $thesis->supervisor_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. You are not allowed to delete this task.',
            ], 403);
        }

        // Delete the task and related subtasks
        $task->subtasks()->delete();
        $task->delete();

        return response()->json([
            'status' => true,
            'message' => 'task deleted successfully.'
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status' => false,
            'message' => 'task not found.',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An unexpected error occurred.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
      
       
}
