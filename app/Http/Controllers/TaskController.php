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
    //TODO:: NEED TO EDIT
    //
    /**
     * Create a new Project linked to thesis
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'role' => 'required|string|in:student,supervisor',
            'thesis_id' => 'required|exists:thesis,id',
        ]);
    
        // Get the authenticated user
        $user = Auth::user();
    
        // Fetch the thesis
        $thesis = Thesis::find($request->thesis_id);
    
        // Authorization: Check based on role
        if (!$thesis) {
            return response()->json([
                'status' => false,
                'message' => 'Thesis not found.',
            ], 404);
        }
    
        if ($request->role === 'student' && $user->id !== $thesis->student_id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Only the assigned student can create a project.',
            ], 403);
        }
    
        if ($request->role === 'supervisor' && $user->id !== $thesis->supervisor_id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Only the assigned supervisor can create a project.',
            ], 403);
        }
    
        // Create the project
        $project = Task::create([
            'thesis_id' => $request->thesis_id,
            'name' => '', // Set a default name
        ]);
    
        // Create a default subproject
        Subtask::create([
            'tasks_id' => $project->id,
            'name' => null
        ]);
    
        // Load the subprojects relationship onto the project
        $project->load('subtask');
    
        return response()->json([
            'status' => true,
            'message' => 'Project created successfully.',
            'project' => $project
        ], 201);
    }
    /**
    * update  Project name
    */
   public function updateProject(Request $request, $id)
     {
       $project = Task::findOrFail($id);

       $request->validate([
           'name' => 'nullable|string|max:255',
        ]);

       $project->update(['name' => $request->name]);

       return response()->json(['message' => 'Project updated successfully', 'project' => $project]);
      }


  /**
  * Get only the projects related to the logged-in user
  */

   public function index(Request $request)
   {
       $user = Auth::user();
       $thesisId = $request->query('thesis_id'); // Get thesis_id from request

       // Determine if user is a student or supervisor by checking their email in the respective tables
       $isStudent = Student::where('email', $user->email)->exists();
       $isSupervisor = Teacher::where('email', $user->email)->exists();

       // Query projects based on role
       $projectsQuery = Project::whereHas('thesis', function ($query) use ($user, $isStudent, $isSupervisor) {
           $query->where(function ($q) use ($user, $isStudent, $isSupervisor) {
               if ($isStudent) {
                   $q->orWhere('student_id', $user->id);
                   }
               if ($isSupervisor) {
                   $q->orWhere('supervisor_id', $user->id);
               }
           });
       });

       // Filter by thesis_id if provided
       if ($thesisId) {
           $projectsQuery->where('thesis_id', $thesisId);
       }

       // Fetch the projects with related data
       $projects = $projectsQuery->with(['subprojects'])->orderBy('created_at', 'asc')->get();


       return response()->json([
           'status' => true,
           'projects' => $projects,
           'user' => $user,
           'role' => $isStudent ? 'student' : ($isSupervisor ? 'supervisor' : 'unknown'),
           ], 200);
}

/**
* Delete a Project and its subprojects
*/
        public function destroy($id)
        {
            $user = Auth::user();
            $project = Project::find($id);         

            if (!$project) {
                return response()->json([
                    'status' => false,
                    'message' => 'Project not found.',
                ], 404);
            }         

            $thesis = $project->thesis;         

            // Check if the user is authorized to delete the project
            if (!$thesis || ($user->id !== $thesis->student_id && $user->id !== $thesis->supervisor_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. You are not allowed to delete this project.',
                ], 403);
            }         

            // Delete related subprojects first
            $project->subprojects()->delete();         

            // Delete the project
            $project->delete();         

            return response()->json([
                'status' => true,
                'message' => 'Project and related subprojects deleted successfully.',
            ], 200);
        }         
       
}
