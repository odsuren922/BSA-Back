<?php

namespace App\Http\Controllers\Thesis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Thesis;
use App\Models\Department;
use App\Models\ThesisCycle;
use App\Models\ThesisPlanStatus;
use App\Http\Resources\ThesisResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ThesisController extends Controller
{


    //Thesis awah
    public function supervisodThesis()
{
    try {
        $user = Auth::user();

        $thesis = Thesis::with([
            'student',
            'supervisor',
            'thesisCycle',
            'thesisPlanStatus',
            'scores',
          
        ])->where('supervisor_id', $user->id)->get();

        if ($thesis->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No thesis found.'], 404);
        }

        return response()->json([
            'status' => true,
            'thesis' => ThesisResource::collection($thesis),
    
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Error in thesisByRole: ' . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching thesis.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function index($id){
    $thesis =Thesis::with([
        'supervisor',
        'student',
       // 'thesisCycle',
        'thesisCycle.gradingSchema.gradingComponents',
        'thesisPlanStatus',
        'scores'
    ])->findOrFail($id);

    return new ThesisResource($thesis);
}
    //TODO::
    public function pdf($id)
    {
        try {
            $user = Auth::user();

            $thesis = Thesis::findOrFail($id);

            $student = Student::where('id', $thesis->student_id)->first();
            $supervisor = Teacher::where('id', $thesis->supervisor_id)->first();

            if (!$student || !$supervisor) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Student or Supervisor not found',
                    ],
                    404,
                );
            }

            // Fetch Head of Department
            $head_dep = Teacher::where('dep_id', $student->dep_id)->where('superior', 'head')->first();
            //TODO:: START AND END DATE
            return response()->json(
                [
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
                    'major_short_name' => 'МТ',
                    'name_mongolian' => $thesis->name_mongolian,
                    'name_english' => $thesis->name_english,
                    'description' => $thesis->description,
                    'program' => $student->program,
                ],
                200,
            );
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
            ->values(); // Re-index after grouping

        return response()->json($programCounts);
    }





    public function getThesis($id)
    {
        try {
            $thesis = Thesis::with('thesisCycle', 'thesisPlanStatus')->findOrFail($id);

            return response()->json(
                [
                    'status' => true,
                    'data' => $thesis,
                ],
                200,
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Thesis not found',
                ],
                404,
            );
        } catch (\Exception $e) {
            \Log::error('Error in fetching thesis: ' . $e->getMessage());

            return response()->json(
                [
                    'status' => false,
                    'message' => 'An error occurred while fetching the thesis.',
                ],
                500,
            );
        }
    }




}

