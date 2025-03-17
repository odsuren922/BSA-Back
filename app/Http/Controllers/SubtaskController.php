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
    //TODO:: ADD THIS CODE


       /**
     * Create a new subtask linked to a task
     */
    public function store(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',//байхгүй байж болно
        ]);

        $user = Auth::user();

        $task= Task::find($request->task_id);
        $thesis =$task->thesis;

        $thesis = $task->thesis;
        if (!$thesis || ($user->id !== $thesis->student_id && $user->id !== $thesis->supervisor_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. You are not allowed to create a subtask for this task.',
            ], 403);
        }

        $subtask = Subtask::create([
            'task_id' => $request->task_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
        ]);



        return response()->json([
            'status' => true,
            'message' => 'subtask created successfully',
            'subtask' => $subtask
        ], 201);
    }


    

}
