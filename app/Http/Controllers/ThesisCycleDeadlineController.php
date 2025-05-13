<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ThesisCycleDeadline;

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
    public function getBySchema(Request $request)
{
    $request->validate([
        'thesis_cycle_id' => 'required|exists:thesis_cycles,id',
        'grading_schema_id' => 'required|exists:grading_schemas,id',
    ]);

    $thesisCycleId = $request->thesis_cycle_id;
    $schemaId = $request->grading_schema_id;

    // Grading components under this schema
    $componentIds = \App\Models\GradingComponent::where('grading_schema_id', $schemaId)
        ->pluck('id');

    // Deadlines linked to those components
    $deadlines = \App\Models\ThesisCycleDeadline::with('relatedComponent')
        ->where('thesis_cycle_id', $thesisCycleId)
        ->where('type', 'grading_component')
        ->whereIn('related_id', $componentIds)
        ->get();

    return response()->json([
        'deadlines' => $deadlines,
    ]);
}

}
