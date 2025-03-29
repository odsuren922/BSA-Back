<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ThesisCycle;
use App\Models\GradingSchema;

class ThesisCycleController extends Controller
{
    // Get all thesis cycles
    public function index()
    {
        return response()->json(
            ThesisCycle::with('gradingSchema')
                ->where('status', '!=', 'Устгах')
                ->get()
        );
    }
    
    public function active()
    {
        //TODO::
        $activeThesis = ThesisCycle::with('gradingSchema')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    
   
    
        return response()->json($activeThesis);
    }
    
    // Create a new thesis cycle
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'year' => 'required|integer',
            'semester' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'grading_schema_id' => 'nullable|exists:grading_schemas,id'
        ]);

        // if (!$request->grading_schema_id) {
        //     // Create a new grading schema if not provided
        //     $gradingSchema = GradingSchema::create([
        //         'year' => $request->year,
        //         'name' => 'Default Schema for ' . $request->name,
        //         'step_num' => 3, // Default step count
        //     ]);
        //     $request->merge(['grading_schema_id' => $gradingSchema->id]);
        // }

        $thesisCycle = ThesisCycle::create($request->all());

        return response()->json($thesisCycle, 201);
    }

    // Get a specific thesis cycle
    public function show($id)
    {
        return response()->json(ThesisCycle::with('gradingSchema')->findOrFail($id));
    }

    // Update a thesis cycle
    //TODO:: ONLY ADMIN CAN DO IT
    public function update(Request $request, $id)
    {
        $thesisCycle = ThesisCycle::findOrFail($id);
        $thesisCycle->update($request->all());
        return response()->json($thesisCycle);
    }

    // Delete a thesis cycle
    public function destroy($id)
    {
        ThesisCycle::destroy($id);
        return response()->json(['message' => 'Thesis cycle deleted']);
    }
}
