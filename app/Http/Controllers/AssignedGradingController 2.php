<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers;
use App\Models\AssignedGrading;
use App\Models\GradingComponent;
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
    
        // Proceed with assignment
        $results = [];
    
        foreach ($validated['student_ids'] as $index => $studentId) {
            $thesisId = $validated['thesis_ids'][$index] ?? null;
    
            if (!$thesisId) {
                continue;
            }
    
            $assignment = AssignedGrading::firstOrCreate([
                'grading_component_id' => $validated['grading_component_id'],
                'thesis_cycle_id' => $validated['thesis_cycle_id'],
                'student_id' => $studentId,
                'thesis_id' => $thesisId,
                'assigned_by_type' => $validated['assigned_by_type'],
                'assigned_by_id' => $validated['assigned_by_id'],
            ]);
    
            $results[] = $assignment;
        }
    
        return response()->json([
            'message' => 'Multiple assignments created successfully.',
            'data' => $results,
        ], 201);
    }
    
    public function getByComponentAndCycle($componentId, $cycleId)
{
    return AssignedGrading::with([])
        ->where('grading_component_id', $componentId)
        ->where('thesis_cycle_id', $cycleId)
        ->get();


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

    public function destroy(AssignedGrading $assignedGrading)
    {
        $assignedGrading->delete();
        return response()->json(['message' => 'Assignment removed.']);
    }

}
