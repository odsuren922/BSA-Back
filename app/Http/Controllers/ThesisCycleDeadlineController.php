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
            'start_time' => 'nullable|date_format:H:i:s',
            'end_time' => 'nullable|date_format:H:i:s',
        ]);

        $deadline = ThesisCycleDeadline::updateOrCreate(
            [
                'thesis_cycle_id' => $validated['thesis_cycle_id'],
                'related_id' => $validated['related_id'],
                'type' => 'grading_component',
            ],
            [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ]
        );

        return response()->json([
            'message' => 'Deadline saved successfully.',
            'data' => $deadline
        ], 200);
    }
}
