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
   
    public function index( $id)
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
                'description'=>  $thesis->description
            
                
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
    

}
