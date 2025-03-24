<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Thesis;
use App\Models\Department;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class ThesisController extends Controller
{
   
    public function pdf( $id)
    {
        try {
            $user = Auth::user();
        
            $thesis = Thesis::findOrFail($id);

            $student = Student::where('id', $thesis->student_id)->first();
            $supervisor = Teacher::where('id', $thesis->supervisor_id)->first();
    
            if (!$student || !$supervisor) {
                return response()->json([
                    'status' => false,
                    'message' => 'Student or Supervisor not found'
                ], 404);
            }
    
            // Fetch Head of Department
            $head_dep = Teacher::where('dep_id', $student->dep_id)
                               ->where('superior', 'head')
                               ->first();
            //TODO:: START AND END DATE
            return response()->json([
                'status' => true,
                'supervisor' => "{$supervisor->lastname} {$supervisor->firstname}",
                'student' => "{$student->lastname} {$student->firstname}",
                'num_id' => $student->sisi_id,
                'head_dep' => $head_dep ? "{$head_dep->degree}. {$head_dep->lastname} {$head_dep->firstname}" : 'Not assigned',
                'phone' => $student->phone,
                'start_date' => $thesis->start_date ?? '2025-02-03', // Replace with dynamic date
                'end_date' => $thesis->end_date ?? '2025-05-16', // Replace with dynamic date
                'name_of_plan' => '7 хоногийн үйлчлэлсэн төлөвлөгөө',
                'weeks_num' => '15',
                'major_short_name' => "МТ",
                'name_mongolian'=> $thesis->name_mongolian,
                'name_english' => $thesis->name_english,
                'description'=>  $thesis->description,
                'program' => $student->program,
            
                
            ], 200);
    
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error("Error in Thesis Index: " . $e->getMessage());
    
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching thesis data. Please try again later.',
                'error' => $e->getMessage() // Only for debugging, remove in production
            ], 500);
        }
    }

//Thesis awah


public function thesisByRole()
{
    try {
        $user = Auth::user();

        // Хэрэв хэрэглэгч админ бол бүх дипломын ажлыг авах
        // Хэрэв хэрэглэгч supervisor бол зөвхөн өөрийн удирдсан дипломын ажлыг авах
        $query = Thesis::with('student'); // Eager load student relationship

        if ($user->role === 'supervisor') {
            $query->where('supervisor_id', $user->id);
        }

        $thesis = $query->get();

        if ($thesis->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No thesis found.'], 404);
        }

        return response()->json([
            'status' => true,
            'thesis' => $thesis,
            'user' => $user
        ], 200);

    } catch (\Exception $e) {
        \Log::error("Error in thesisByRole: " . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching thesis.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function index($id)
{
    try {
        // Fetch the thesis and associated models
        $thesis = Thesis::findOrFail($id);

        // Use findOrFail for student and supervisor to ensure consistency in error handling
        $student = Student::findOrFail($thesis->student_id);
        $supervisor = Teacher::findOrFail($thesis->supervisor_id);

        // Return response with thesis, student (filtered), and supervisor details
        return response()->json([
            'status' => true,
            'supervisor' => $supervisor,
            'student' => [
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'sisi_id' => $student->sisi_id,
                'mail' => $student->mail,
                'phone' => $student->phone,
                'program' => $student->program,
            ],
            'thesis' => $thesis,
        
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Handle case where a student or supervisor doesn't exist
        return response()->json([
            'status' => false,
            'message' => 'Student or Supervisor not found',
        ], 404);
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error("Error in Thesis Index: " . $e->getMessage());

        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching thesis data. Please try again later.',
        ], 500);
    }
}




public function allTheses()
{
    try {
        // Get all theses including the student relationship
        $theses = Thesis::with('student')->get();

        // Check if any theses are found
        if ($theses->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No thesis found.'], 404);
        }

        return response()->json([
            'status' => true,
            'theses' => $theses
        ], 200);

    } catch (\Exception $e) {
        \Log::error("Error in allTheses: " . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching theses.',
            'error' => $e->getMessage()
        ], 500);
    }
}






    

}
