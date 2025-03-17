<?php

namespace App\Http\Controllers;
use App\Models\Student;
use App\Models\Supervisor;


use App\Models\Subproject;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

class SubprojectController extends Controller
{
    /**
     * Create a new Subproject linked to a Project
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'sub_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',//байхгүй байж болно
        ]);

        $user = Auth::user();

        $project= Project::find($request->project_id);
        $thesis =$project->thesis;

        $thesis = $project->thesis;
        if (!$thesis || ($user->id !== $thesis->student_id && $user->id !== $thesis->supervisor_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. You are not allowed to create a subproject for this project.',
            ], 403);
        }

        $subproject = Subproject::create([
            'project_id' => $request->project_id,
            'sub_name' => $request->sub_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
        ]);



        return response()->json([
            'status' => true,
            'message' => 'Subproject created successfully',
            'subproject' => $subproject
        ], 201);
    }
//TODO::

    //edit project
    public function updateSubProject(Request $request, $id)
    {
        $subproject = SubProject::findOrFail($id);
    
        $request->validate([
            'sub_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        
        if ($request->has('sub_name')) {
            // Handle sub_name
          
            $subproject->update(['sub_name' => $request->sub_name]);
            // Do something with $subName
        }
        
        if ($request->has('start_date')) {
            // Handle start_date if it was sent
            $subproject->update([ 'start_date' => $request->start_date]);
        }
        
        if ($request->has('end_date')) {
            // Handle end_date

            $subproject->update([
                'end_date' => $request->end_date,
            ]);
        }
        
        if ($request->filled('description')) {
            $subproject->update([
                'description' => $request->description !== '' ? $request->description : null,
            ]);
        } else {
            $subproject->update([
                'description' => null,
            ]);
        }
        
        
    
        return response()->json(['message' => 'Subproject updated successfully', 'subproject' => $subproject]);
    }
    /**
 * Delete a Subproject
 */
public function destroy($id)
{
    $user = Auth::user();
    $subproject = Subproject::find($id);

    if (!$subproject) {
        return response()->json([
            'status' => false,
            'message' => 'Subproject not found.',
        ], 404);
    }

    $project = $subproject->project;
    $thesis = $project->thesis;

    if (!$thesis || ($user->id !== $thesis->student_id && $user->id !== $thesis->supervisor_id)) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized. You are not allowed to delete this subproject.',
        ], 403);
    }

    $subproject->delete();

    return response()->json([
        'status' => true,
        'message' => 'Subproject deleted successfully.',
    ], 200);
}


}
