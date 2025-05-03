<?php

namespace App\Http\Controllers\Thesis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Thesis;
use App\Models\Department;
use App\Models\ThesisCycle;
use App\Models\ThesisPlanStatus;
use App\Models\Score;
use App\Http\Resources\ThesisResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ThesisController extends Controller
{
    //TODO:: NOT USE AUTH USE ID FROM REQUEST
    public function supervisodThesis()
    {
        try {
            // $user = Auth::user();
            
        
            $user = Teacher::findOrFail(1);


            $thesis = Thesis::with(['student', 'supervisor', 'thesisCycle', 'thesisPlanStatus', 'scores'])
                ->where('supervisor_id', $user->id)
                ->get();

            if ($thesis->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'No thesis found.'], 404);
            }

            return response()->json(
                [
                    'status' => true,
                    'thesis' => ThesisResource::collection($thesis),
                ],
                200,
            );
        } catch (\Exception $e) {
            \Log::error('Error in thesisByRole: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => false,
                    'message' => 'An error occurred while fetching thesis.',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }
//Thesis id gaar thesis iig avna
    public function index($id)
    {
        $thesis = Thesis::with([
            'supervisor',
            'student',
            'thesisCycle.gradingSchema.gradingComponents',
            'thesisPlanStatus',
            // 'scores.teacher',
            'scores',
            'tasks.subtasks',
        ])->find($id); 
    
        if (!$thesis) {
         
            return new ThesisResource(new Thesis()); 
        }
    
        return new ThesisResource($thesis);
    }
    //Student id gaar thesis iig avna
    public function thesisbyStudentId($studentId)
{
    $thesis = Thesis::with([
        'supervisor',
        'student',
        'thesisCycle.gradingSchema.gradingComponents',
        'thesisPlanStatus',
        // 'scores.teacher',
        'scores',
        'tasks.subtasks',
    ])->where('student_id', $studentId)->first(); // 👈 find by student_id

    if (!$thesis) {
        return new ThesisResource(new Thesis()); // or: return response()->json(null);
    }

    return new ThesisResource($thesis);
}


    public function getThesis($id)
    {
        try {
            $thesis = Thesis::with([
                // 'supervisor',
             
                'thesisCycle',
                'tasks.subtasks',
                // 'thesisCycle.gradingSchema.gradingComponents',
                'thesisPlanStatus',
                // 'scores',

            ])->findOrFail($id);

            return new ThesisResource($thesis);
        } catch (\Exception $e) {
            \Log::error('Алдаа гарлаа ' . $e->getMessage());

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Судалгааны ажлын өгөгдөл татахад алдаа гарлаа.',
                ],
                500,
            );
        }
    }
    //TODO::
    public function pdf($id)
    {
        try {
    

            $thesis =Thesis::with([
                'supervisor',
                'student.department.headOfDepartment',

            ])
            ->findOrFail($id);
            return new ThesisResource($thesis);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in Thesis Index: ' . $e->getMessage());

            return response()->json(
                [
                    'status' => false,
                    'message' => 'An error occurred while fetching thesis data. Please try again later.',
                    'error' => $e->getMessage(), // Only for debugging, remove in production
                ],
                500,
            );
        }
    }

    //all thesis where thesis_cycle= thesis_cycle_active
    public function getThesesByCycle($id)
    {
        $thesisCycle = ThesisCycle::findOrFail($id);

        $formattedTheses = $thesisCycle->theses->map(function ($thesis) {
            return [
                'id' => $thesis->id,
                'name_mongolian' => $thesis->name_mongolian,
                'student_info' => [
                    'firstname' => $thesis->student->firstname,
                    'lastname' => $thesis->student->lastname,
                    'program' => $thesis->student->program,
                    'id' => $thesis->student_id,
                    'email' => $thesis->student->mail,
                ],
                'supervisor_info' => [
                    'firstname' => $thesis->supervisor->firstname,
                    'lastname' => $thesis->supervisor->lastname,
                ],
                'status' => $thesis->status,
                'department' => $thesis->student->department->name,
                'plan_status' => $thesis->thesisPlanStatus,
            ];
        });

        // Sort by program, then firstname
        $sortedTheses = $formattedTheses->sortBy([['student_info.program', 'asc'], ['student_info.firstname', 'asc']]);

        return response()->json($sortedTheses->values());
    }

    public function getActiveThesesByCycle($id)
    {
        $thesisCycle = ThesisCycle::findOrFail($id);

        $formattedTheses = $thesisCycle
            ->theses()
            ->where('status', 'active')
            ->with(['student', 'supervisor']) // eager load for speed
            ->get()
            ->map(function ($thesis) {
                return [
                    'student_info' => [
                        'firstname' => $thesis->student->firstname,
                        'lastname' => $thesis->student->lastname,
                        'program' => $thesis->student->program,
                        'id' => $thesis->student_id,
                    ],
                    'supervisor_info' => [
                        'firstname' => $thesis->supervisor->firstname,
                        'lastname' => $thesis->supervisor->lastname,
                    ],
                    'status' => $thesis->status,
                    'plan_status' => $thesis->thesisPlanStatus,
                ];
            });

        // Sort by program, then firstname
        $sortedTheses = $formattedTheses->sortBy([['student_info.program', 'asc'], ['student_info.firstname', 'asc']]);

        return response()->json($sortedTheses->values());
    }

    public function getStudentCountByProgram($id)
    {
        $thesisCycle = ThesisCycle::findOrFail($id);

        $programCounts = $thesisCycle->theses
            ->groupBy('student.program')
            ->map(function ($theses, $program) {
                return [
                    'program' => $program,
                    'student_count' => $theses->count(),
                ];
            })
            ->values(); 

        return response()->json($programCounts);
    }
}
