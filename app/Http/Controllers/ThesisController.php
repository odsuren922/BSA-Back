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
use App\Models\ThesisCycle;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ThesisController extends Controller
{
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
                    // 'department' => $thesis->supervisor->department->name
                ],
                'status' => $thesis->status,
                'department' => $thesis->student->department->name,
                // 'plan_status'=> [
                //     'student'=> $thesis->status->student_sent,
                //     'teacher'=>$thesis->status->teacher_status,
                // ]
            ];
        });

        // Sort by department name first, then student first name
        $sortedTheses = $formattedTheses->sortBy([['department', 'asc'], ['student_info.firstname', 'asc']]);

        return response()->json($sortedTheses->values()); // Re-index after sorting
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

    //Thesis awah

    public function supervisodThesis()
    {
        try {
            $user = Auth::user();
            $query = Thesis::with('student');

            $query->where('supervisor_id', $user->id);

            $thesis = $query->get();

            if ($thesis->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'No thesis found.'], 404);
            }

            return response()->json(
                [
                    'status' => true,
                    'thesis' => $thesis,
                    'user' => $user,
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

    public function index($id)
    {
        try {
            // Fetch the thesis and associated models
            $thesis = Thesis::findOrFail($id);

            // Use findOrFail for student and supervisor to ensure consistency in error handling
            $student = Student::findOrFail($thesis->student_id);
            $supervisor = Teacher::findOrFail($thesis->supervisor_id);

            // Return response with thesis, student (filtered), and supervisor details
            return response()->json(
                [
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
                ],
                200,
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle case where a student or supervisor doesn't exist
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Student or Supervisor not found',
                ],
                404,
            );
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in Thesis Index: ' . $e->getMessage());

            return response()->json(
                [
                    'status' => false,
                    'message' => 'An error occurred while fetching thesis data. Please try again later.',
                ],
                500,
            );
        }
    }

    public function getThesis($id)
    {
        try {
            $thesis = Thesis::findOrFail($id);

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

    public function getStudentByThesis($thesis_id)
    {
        try {
            $thesis = Thesis::findOrFail($thesis_id);
            $student = Student::findOrFail($thesis->student_id);

            return response()->json(
                [
                    'status' => true,
                    'data' => [
                        'firstname' => $student->firstname,
                        'lastname' => $student->lastname,
                        'sisi_id' => $student->sisi_id,
                        'mail' => $student->mail,
                        'phone' => $student->phone,
                        'program' => $student->program,
                    ],
                ],
                200,
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Thesis or Student not found',
                ],
                404,
            );
        } catch (\Exception $e) {
            \Log::error('Error in fetching student by thesis: ' . $e->getMessage());

            return response()->json(
                [
                    'status' => false,
                    'message' => 'An error occurred while fetching the student.',
                ],
                500,
            );
        }
    }

    public function getSupervisorByThesis($thesis_id)
    {
        try {
            $thesis = Thesis::findOrFail($thesis_id);
            $supervisor = Teacher::findOrFail($thesis->supervisor_id);

            return response()->json(
                [
                    'status' => true,
                    'data' => $supervisor,
                ],
                200,
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Thesis or Supervisor not found',
                ],
                404,
            );
        } catch (\Exception $e) {
            \Log::error('Error in fetching supervisor by thesis: ' . $e->getMessage());

            return response()->json(
                [
                    'status' => false,
                    'message' => 'An error occurred while fetching the supervisor.',
                ],
                500,
            );
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

            return response()->json(
                [
                    'status' => true,
                    'theses' => $theses,
                ],
                200,
            );
        } catch (\Exception $e) {
            \Log::error('Error in allTheses: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => false,
                    'message' => 'An error occurred while fetching theses.',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }
}
