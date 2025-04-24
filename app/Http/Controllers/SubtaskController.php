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

class SubtaskController extends Controller
{
       /**
     * Create a new subtask linked to a task
     */
    public function store(Request $request)
{
    try {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();

        $task = Task::find($request->task_id);
        if (!$task) {
            return response()->json([
                'status' => false,
                'message' => 'Task not found.',
            ], 404);
        }

        $thesis = $task->thesis;
        // if (!$thesis || ($user->id !== $thesis->student_id && $user->id !== $thesis->supervisor_id)) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Unauthorized. You are not allowed to create a subtask for this task.',
        //     ], 403);
        // }

        $subtask = Subtask::create([
            'task_id' => $request->task_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Subtask created successfully',
            'subtask' => $subtask
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while creating the subtask.',
            'error' => $e->getMessage() // You can remove this in production for security reasons
        ], 500);
    }
}

   //edit project
   public function updateSubTask(Request $request, $id)
   {
       try {
           $subtask = Subtask::findOrFail($id);
   
           $request->validate([
               'name' => 'nullable|string|max:255',
               'start_date' => 'nullable|date',
               'end_date' => 'nullable|date|after_or_equal:start_date',
               'description' => 'nullable|string',
           ]);
   
           // Collect only the provided values for update
           $updateData = [];
   
           if ($request->has('name')) {
               $updateData['name'] = $request->name;
           }
           if ($request->has('start_date')) {
               $updateData['start_date'] = $request->start_date;
           }
           if ($request->has('end_date')) {
               $updateData['end_date'] = $request->end_date;
           }
           if ($request->has('description')) {
               $updateData['description'] = $request->filled('description') ? $request->description : null;
           }
   
           // Update only if there are changes
           if (!empty($updateData)) {
               $subtask->update($updateData);
           }
   
           return response()->json([
               'status' => true,
               'message' => 'Subtask updated successfully',
               'subtask' => $subtask
           ], 200);
       } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
           return response()->json([
               'status' => false,
               'message' => 'Subtask not found',
           ], 404);
       } catch (\Exception $e) {
           return response()->json([
               'status' => false,
               'message' => 'An error occurred while updating the subtask.',
               'error' => $e->getMessage() // Remove in production for security
           ], 500);
       }
   }
   
       /**
 * Delete a subtask
 */
public function destroy($id)
{
    try {
        $user = Auth::user();
        $subtask = Subtask::findOrFail($id); // Throws 404 automatically if not found

        $task = $subtask->task;
        $thesis = $task->thesis;

        // if (!$thesis || ($user->id !== $thesis->student_id && $user->id !== $thesis->supervisor_id)) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Unauthorized. You are not allowed to delete this subtask.',
        //     ], 403);
        // }

        $subtask->delete();

        return response()->json([
            'status' => true,
            'message' => 'Subtask deleted successfully.',
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Subtask not found.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while deleting the subtask.',
            'error' => $e->getMessage(), 
        ], 500);
    }
}




}
