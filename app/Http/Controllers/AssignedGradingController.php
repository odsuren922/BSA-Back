<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers;
use App\Models\AssignedGrading;
use App\Models\Score;
use App\Http\Resources\ScoreResource;
use App\Models\GradingComponent;
use App\Http\Resources\AssignedGradingResource;

class AssignedGradingController extends Controller
{
    //
    public function index()
    {
        return AssignedGrading::with(['assignedBy', 'student', 'gradingComponent', 'thesis'])->get();
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'grading_component_id' => 'required|exists:grading_components,id',
            'thesis_cycle_id' => 'required|exists:thesis_cycles,id',
            'thesis_ids' => 'required|array',
            'thesis_ids.*' => 'required|integer|exists:thesis,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'required|integer|exists:students,id',
            'assigned_by_type' => 'required|string',
            'assigned_by_id' => 'required|integer',
        ]);
    
        $component = GradingComponent::find($validated['grading_component_id']);
    
        if (in_array($component->by_who, ['supervisor', 'committee'])) {
            return response()->json([
                'message' => 'This grading component is not assignable manually.',
            ], 403);
        }
    
        $results = [];
    
        foreach ($validated['student_ids'] as $index => $studentId) {
            $thesisId = $validated['thesis_ids'][$index] ?? null;
    
            if (!$thesisId) {
                continue;
            }
    
            $assignment = AssignedGrading::updateOrCreate(
                [
                    'grading_component_id' => $validated['grading_component_id'],
                    'thesis_cycle_id' => $validated['thesis_cycle_id'],
                    'student_id' => $studentId,
                ],
                [ // эдгээр баганууд шинэчилнэ
                    'thesis_id' => $thesisId,
                    'assigned_by_type' => $validated['assigned_by_type'],
                    'assigned_by_id' => $validated['assigned_by_id'],
                ]
            );
    
            $results[] = $assignment;
        }
    
        return response()->json([
            'message' => 'Assignments created or updated successfully.',
            'data' => $results,
        ], 201);
    }
    
    
    public function getByComponentAndCycle($componentId, $cycleId)
{
    $assigned=  AssignedGrading::with([])
        ->where('grading_component_id', $componentId)
        ->where('thesis_cycle_id', $cycleId)
        ->get();

        return AssignedGradingResource::collection($assigned);
}

public function getByAssignedById($teacherId)
{
    $assigned = AssignedGrading::with(['gradingComponent', 'thesis', 'student','thesisCycle','score'])
        ->where('assigned_by_id', $teacherId)
        ->get();

    if ($assigned->isEmpty()) {
        return response()->json(['message' => 'No assignments found'], 404);
    }

    return AssignedGradingResource::collection($assigned);
}

public function getScoreByAssignedById($teacherId)
{
    $assigned = AssignedGrading::where('assigned_by_id', $teacherId)->get();

    if ($assigned->isEmpty()) {
        return response()->json(['message' => 'No assignments found'], 404);
    }

    $componentIds = $assigned->pluck('grading_component_id')->unique();
    $thesisIds = $assigned->pluck('thesis_id')->unique();
    $studentIds = $assigned->pluck('student_id')->unique();

    $score = Score::with(['student', 'component'])
        ->whereIn('component_id', $componentIds)
        ->whereIn('thesis_id', $thesisIds)
        ->whereIn('student_id', $studentIds)
        ->get();

    return ScoreResource::collection($score);
}




    public function checkAssignment(Request $request)
    {
        $validated = $request->validate([
            'grading_component_id' => 'required|exists:grading_components,id',
            'assigned_by_type' => 'required|string',
            'assigned_by_id' => 'required|integer',
            'student_id' => 'required|exists:students,id',
        ]);

        $exists = AssignedGrading::where([
            'grading_component_id' => $validated['grading_component_id'],
            'assigned_by_type' => $validated['assigned_by_type'],
            'assigned_by_id' => $validated['assigned_by_id'],
            'student_id' => $validated['student_id'],
        ])->exists();

        return response()->json(['match' => $exists]);
    }

    public function getGradingAssignments(Request $request)
{
    $schemaId = $request->input('grading_schema_id');
    $thesisId = $request->input('thesis_id');
    $studentId = $request->input('student_id');

    // thesis_id-ээр student_id авах
    if (!$studentId && $thesisId) {
        $thesis = Thesis::find($thesisId);
        if (!$thesis) return response()->json(['message' => 'Thesis not found'], 404);
        $studentId = $thesis->student_id;
    }

    if (!$schemaId || !$studentId) {
        return response()->json(['message' => 'Required parameters missing'], 400);
    }

    // GradingComponents дуудаж шалгах
    $components = GradingComponent::where('grading_schema_id', $schemaId)
        ->with(['gradingCriteria']) // хэрэгтэй бол criteria ч бас ачаална
        ->get();

    $result = $components->map(function ($component) use ($studentId) {
        $data = [
            'component_id' => $component->id,
            'component_name' => $component->name,
            'by_who' => $component->by_who,
        ];

        if ($component->by_who === 'committee') {
            // олж буй student's committee
            $committeeStudent = \App\Models\CommitteeStudent::with('committee')
                ->where('student_id', $studentId)
                ->first();

            $data['committee'] = $committeeStudent?->committee;
            $data['committee_student_id'] = $committeeStudent?->id;
        } elseif ($component->by_who === 'examiner') {
            $assigned=  AssignedGrading::with([])
            ->where('grading_component_id', $component->id)
            ->where('student_id', $studentId)
            ->get();

            $data['assigned_teacher'] = AssignedGradingResource::collection($assigned);
          
        }

        return $data;
    });

    return response()->json($result);
}


    public function destroy(AssignedGrading $assignedGrading)
    {
        $assignedGrading->delete();
        return response()->json(['message' => 'Assignment removed.']);
    }

}
