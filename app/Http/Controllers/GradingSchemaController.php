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
        return GradingSchema::with('gradingComponents.gradingCriteria')
            ->orderBy('year', 'desc') // Order by year (newest first)
            ->get();
    }
    public function storeonlySchema(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'step_num' => 'nullable|integer',
        ]);
        // GradingComponent

        return GradingSchema::create($validated);
    }
// store with components
    public function store(Request $request)
{
    // Validate request data
    $validated = $request->validate([
        'year' => 'required|integer',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'step_num' => 'nullable|integer',
        'grading_components' => 'required|array', // Ensure grading_components is an array
        'grading_components.*.name' => 'required_if:grading_components.*.score,!=,null|string|max:255', // Validate name if score is not null
        'grading_components.*.score' => 'nullable|numeric', // Allow score to be nullable but only if the name exists
        'grading_components.*.by_who' => 'nullable|string', // Optional field for who assigns
    ]);

    // If no grading components were sent
    if (empty($validated['grading_components'])) {
        return response()->json(['error' => 'At least one grading component is required.'], 400);
    }

    // Create the GradingSchema
    $gradingSchema = GradingSchema::create([
        'year' => $validated['year'],
        'name' => $validated['name'],
        'description' => $validated['description'] ?? null,
        'step_num' => $validated['step_num'] ?? null,
    ]);

    // Create associated GradingComponents only if they exist
    foreach ($validated['grading_components'] as $component) {
        if (!empty($component['name']) && isset($component['score'])) {
            $gradingSchema->gradingComponents()->create([
                'grading_schema_id' => $gradingSchema->id,
                'name' => $component['name'],
                'score' => $component['score'],
                'by_who' => $component['by_who'] ?? 'Supervisor', // Default to Supervisor if 'by_who' is not provided
            ]);
        }
    }

    return response()->json(['message' => 'Grading Schema and Components created successfully!', 'data' => $gradingSchema], 201);
}



    
public function update(Request $request, $id)
{
    // Validate request data
    $validated = $request->validate([
        'year' => 'required|integer',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'step_num' => 'nullable|integer',
        'grading_components' => 'required|array',
        'grading_components.*.name' => 'required_if:grading_components.*.score,!=,null|string|max:255',
        'grading_components.*.score' => 'nullable|numeric',
        'grading_components.*.by_who' => 'nullable|string',
    ]);

    // Find the existing GradingSchema
    $gradingSchema = GradingSchema::findOrFail($id);

    // Update schema details
    $gradingSchema->update([
        'year' => $validated['year'],
        'name' => $validated['name'],
        'description' => $validated['description'] ?? null,
        'step_num' => $validated['step_num'] ?? null,
    ]);

    // Sync Grading Components
    $existingComponents = $gradingSchema->gradingComponents->keyBy('id');
    $keptComponentIds = [];

    foreach ($validated['grading_components'] as $component) {
        if (!empty($component['id']) && $existingComponents->has($component['id'])) {
            // Update existing component
            $existingComponent = $existingComponents[$component['id']];
            $existingComponent->update([
                'name' => $component['name'],
                'score' => $component['score'],
                'by_who' => $component['by_who'] ?? 'Supervisor',
            ]);
            $keptComponentIds[] = $existingComponent->id;
        } else {
            // Create new component
            $newComponent = $gradingSchema->gradingComponents()->create([
                'name' => $component['name'],
                'score' => $component['score'],
                'by_who' => $component['by_who'] ?? 'Supervisor',
            ]);
            $keptComponentIds[] = $newComponent->id;
        }
    }

    // Remove components that are not processed (updated or created)
    $gradingSchema->gradingComponents()->whereNotIn('id', $keptComponentIds)->delete();

    return response()->json([
        'message' => 'Grading Schema updated successfully!',
        'data' => $gradingSchema->load('gradingComponents'),
    ], 200);
}

    // public function update(Request $request, $schemaId)
    // {
    //     // Validate request
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'year' => 'required|integer|min:2000|max:' . date('Y'),
    //         'grading_components' => 'required|array',
    //         'grading_components.*.id' => 'nullable|exists:grading_components,id',
    //         'grading_components.*.name' => 'required|string|max:255',
    //         'grading_components.*.score' => 'required|numeric|min:0|max:100',
    //         'grading_components.*.by_who' => 'required|string|max:255',
    //     ]);
    
    //     // Find and update the grading schema
    //     $gradingSchema = GradingSchema::findOrFail($schemaId);
    //     $gradingSchema->update([
    //         'name' => $validated['name'],
    //         'year' => $validated['year'],
    //     ]);
    
    //     // Delete old components and create new ones
    //     $gradingSchema->gradingComponents()->delete();
    //     foreach ($validated['grading_components'] as $component) {
    //         $gradingSchema->gradingComponents()->create([
    //             'name' => $component['name'],
    //             'score' => $component['score'],
    //             'by_who' => $component['by_who'],
    //         ]);
    //     }
    
    //     return response()->json([
    //         'message' => 'Грейдинг схем амжилттай шинэчлэгдлээ!',
    //         'grading_schema' => $gradingSchema->load('gradingComponents'),
    //     ], 200);
    // }
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
