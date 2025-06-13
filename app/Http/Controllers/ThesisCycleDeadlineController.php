<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ThesisCycleDeadline;
use App\Models\ThesisCycle;
use App\Models\GradingComponent;
class ThesisCycleDeadlineController extends Controller
{
    //

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'thesis_cycle_id' => 'required|exists:thesis_cycles,id',
            'related_id' => 'required|exists:grading_components,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
          
            'type' => 'required|string',
     
        ]);

        $deadline = ThesisCycleDeadline::updateOrCreate(
            [
                'thesis_cycle_id' => $validated['thesis_cycle_id'],
                'related_id' => $validated['related_id'],
                'related_type' => 'App\Models\GradingComponent',
                'type' => 'grading_component',
            ],
            [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            
            ]
        );

        return response()->json([
            'message' => 'Deadline saved successfully.',
            'data' => $deadline
        ], 200);
    }
//     public function getBySchema(Request $request)
// {
//     $request->validate([
//         'thesis_cycle_id' => 'required|exists:thesis_cycles,id',
//         'grading_schema_id' => 'required|exists:grading_schemas,id',
//     ]);

//     $thesisCycleId = $request->thesis_cycle_id;
//     $schemaId = $request->grading_schema_id;

//     // Grading components under this schema
//     $componentIds = \App\Models\GradingComponent::where('grading_schema_id', $schemaId)
//         ->pluck('id');

//     // Deadlines linked to those components
//     $deadlines = \App\Models\ThesisCycleDeadline::with('relatedComponent')
//         ->where('thesis_cycle_id', $thesisCycleId)
//         ->where('type', 'grading_component')
//         ->whereIn('related_id', $componentIds)
//         ->get();

//     return response()->json([
//         'deadlines' => $deadlines,
//     ]);
// }

public function getBySchema(Request $request)
{
    $request->validate([
        'thesis_cycle_id' => 'required|exists:thesis_cycles,id',
    ]);

    $thesisCycleId = $request->thesis_cycle_id;

    // Step 1: Find the grading schema from the thesis cycle
    $cycle = \App\Models\ThesisCycle::with('gradingSchema')->findOrFail($thesisCycleId);

    if (!$cycle->gradingSchema) {
        return response()->json(['message' => 'Grading schema not found for this thesis cycle.'], 404);
    }

    $schemaId = $cycle->grading_schema_id;

    // Step 2: Get all component IDs under the schema
    $componentIds = \App\Models\GradingComponent::where('grading_schema_id', $schemaId)
        ->pluck('id');

    // Step 3: Get all deadlines related to those components
    $deadlines = \App\Models\ThesisCycleDeadline::with('relatedComponent')
        ->where('thesis_cycle_id', $thesisCycleId)
        ->where('type', 'grading_component')
        ->whereIn('related_id', $componentIds)
        ->get();

    return response()->json([
        'deadlines' => $deadlines,
    ]);
}

public function getActiveCycleBySchema(Request $request)
{
    // Step 1: Find the active cycle with its grading schema
    $cycle = ThesisCycle::with('gradingSchema')
        ->where('status', 'Идэвхитэй')
        ->where('dep_id', $request->user()->dep_id)
        ->first();

    if (!$cycle) {
        return response()->json(['message' => 'Active thesis cycle not found.'], 404);
    }

    if (!$cycle->gradingSchema) {
        return response()->json(['message' => 'Grading schema not found for this thesis cycle.'], 404);
    }

    $schemaId = $cycle->grading_schema_id;

    // Step 2: Get component IDs under this schema
    $componentIds = GradingComponent::where('grading_schema_id', $schemaId)
        ->pluck('id');

    // Step 3: Get deadlines for those components in this cycle
    $deadlines = ThesisCycleDeadline::with('relatedComponent')
        ->where('thesis_cycle_id', $cycle->id)
        ->where('type', 'grading_component')
        ->whereIn('related_id', $componentIds)
        ->get();

    return response()->json([
        'thesis_cycle_id' => $cycle->id,
        'grading_schema_id' => $schemaId,
        'deadlines' => $deadlines,
    ]);
}



}
