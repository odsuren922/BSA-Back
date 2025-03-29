<?php

namespace App\Http\Controllers;

use App\Models\GradingSchema;
use Illuminate\Http\Request;

class GradingSchemaController extends Controller
{
    // public function index()
    // {
  
    //     return GradingSchema::with('gradingComponents')->get();
    // }
   


    public function show($id)
    {
        return GradingSchema::with('gradingComponents')->findOrFail($id);
    }
    public function index()
    {
        return GradingSchema::with('gradingComponents.gradingCriteria')->get();
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'step_num' => 'nullable|integer',
        ]);

        return GradingSchema::create($validated);
    }

    public function updateone(Request $request, $id)
    {
        $gradingSchema = GradingSchema::findOrFail($id);
        $gradingSchema->update($request->all());
        return response()->json($gradingSchema, 200);
    }
    public function update(Request $request, $schemaId)
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:2000|max:' . date('Y'),
            'grading_components' => 'required|array',
            'grading_components.*.id' => 'nullable|exists:grading_components,id',
            'grading_components.*.name' => 'required|string|max:255',
            'grading_components.*.score' => 'required|numeric|min:0|max:100',
            'grading_components.*.by_who' => 'required|string|max:255',
        ]);
    
        // Find and update the grading schema
        $gradingSchema = GradingSchema::findOrFail($schemaId);
        $gradingSchema->update([
            'name' => $validated['name'],
            'year' => $validated['year'],
        ]);
    
        // Delete old components and create new ones
        $gradingSchema->gradingComponents()->delete();
        foreach ($validated['grading_components'] as $component) {
            $gradingSchema->gradingComponents()->create([
                'name' => $component['name'],
                'score' => $component['score'],
                'by_who' => $component['by_who'],
            ]);
        }
    
        return response()->json([
            'message' => 'Грейдинг схем амжилттай шинэчлэгдлээ!',
            'grading_schema' => $gradingSchema->load('gradingComponents'),
        ], 200);
    }
    public function addComponents(Request $request, $schemaId)
{
    // Validate request
    $validated = $request->validate([
        'grading_components' => 'required|array',
        'grading_components.*.name' => 'required|string|max:255',
        'grading_components.*.score' => 'required|numeric|min:0|max:100',
        'grading_components.*.by_who' => 'required|string|max:255',
    ]);

    // Find the grading schema
    $gradingSchema = GradingSchema::findOrFail($schemaId);

    // Add new grading components
    foreach ($validated['grading_components'] as $component) {
        $gradingSchema->gradingComponents()->create([
            'name' => $component['name'],
            'score' => $component['score'],
            'by_who' => $component['by_who'],
        ]);
    }

    return response()->json([
        'message' => 'Бүрэлдэхүүн хэсгүүд амжилттай нэмэгдлээ!',
        'grading_schema' => $gradingSchema->load('gradingComponents'),
    ], 200);
}

    

    public function destroy($id)
    {
        GradingSchema::findOrFail($id)->delete();
        return response()->json(['message' => 'Grading schema deleted'], 200);
    }
}
