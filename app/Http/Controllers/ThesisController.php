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


class ThesisController extends Controller
{

    public function index(Request $request)
    {  
        //TODO :: sudalgaani ajiliin 
        //Startdate
        //enddate //admin maan oruulj ogno
        //Topic table bolgoh

        
        $user = Auth::user();
        $thesisId = $request->query('thesis_id'); 
    
        $thesis = Thesis::find($thesisId); 
        if (!$thesis) {
            return response()->json([
                'status' => false,
                'message' =>'thesis not found'
            ], 404);
        };

        $student = Student::find($thesis->student_id);
        $supervisor = Teacher::find($thesis->supervisor_id);

        if(!$student || !$supervisor){
            return response()->json([
                'status' => false,
                'message' =>'student or teachher not found'
            ], 404);
        }
   
           // $department =Department::find($student->dep_id);
      
            
            $head_dep = Teacher::where('dep_id',$student->dep_id)
                                    ->where('superior', 'head')
                                    ->first();
     

        //TODO: "weeks_num": "15",
        // "name_of_plan": "7 хоногийн үйлчлэлсэн төлөвлөгөө",
        // "major_short_name": "МТ"

        return response()->json([
            'status' => true,
            'supervisor' => $supervisor->lastname . ' ' . $supervisor->firstname,
            'student' => $student->lastname . ' ' . $student->firstname,
            'num_id' => $student->sisi_id,
            'topic' => $thesis->topic,
            'head_dep' => $head_dep ? $head_dep->degree . '.' . ' '.  $head_dep->lastname . ' ' . $head_dep->firstname : 'Not assigned',
            'phone' => $student->phonenumber,
            'start_date'=> '2025-02-03',
            'end_date'=> '2025-05-16',
            'name_of_plan'=>'7 хоногийн үйлчлэлсэн төлөвлөгөө',
            'weeks_num' =>'16',
            'major_short_name' => "МТ"
          
        
        ], 200);




    }

}
