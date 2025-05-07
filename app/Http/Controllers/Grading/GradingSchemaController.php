<?php

namespace App\Http\Controllers\Grading;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\GradingSchema;
use App\Models\ThesisCycle;

class GradingSchemaController extends Controller
{
    public function show($id)
    {
        return GradingSchema::with('gradingComponents')->findOrFail($id);
    }

    public function index(Request $request)
    {
        $depId = $request->query('dep_id');
        return GradingSchema::with('gradingComponents.gradingCriteria')
        ->when($depId, function ($query, $depId) {
            return $query->where('dep_id', $depId);
        })
            ->orderBy('year', 'desc') // Order by year (newest first)
            ->get();
    }
    public function showByThesisCycle($thesisCycleId)
    {
        // First, find the ThesisCycle by its ID
        $thesisCycle = ThesisCycle::find($thesisCycleId);

        // If ThesisCycle exists, fetch the associated GradingSchema
        if ($thesisCycle) {
            return GradingSchema::where('id', $thesisCycle->grading_schema_id) // Filter GradingSchema by the ID from ThesisCycle
                ->with('gradingComponents.gradingCriteria') // Include grading components and criteria
                ->orderBy('year', 'desc') // Sort by year
                ->get();
        }

        // If ThesisCycle doesn't exist, return an empty response or handle as needed
        return response()->json(['message' => 'Thesis Cycle not found'], 404);
    }


    

    public function filteredGradingSchema($thesisCycleId)
{
    $thesisCycle = ThesisCycle::find($thesisCycleId);

    if ($thesisCycle) {
        return GradingSchema::where('id', $thesisCycle->grading_schema_id)
            ->with(['gradingComponents' => function ($query) {
                $query->whereNotIn('by_who', ['supervisor', 'committee']);
            }, 'gradingComponents.gradingCriteria'])
            ->orderBy('year', 'desc')
            ->get();
    }

    return response()->json(['message' => 'Thesis Cycle not found'], 404);
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
            'dep_id' => 'nullable|exists:departments,id',
            'grading_components' => 'required|array|min:1',
            'grading_components.*.name' => 'required|string|max:255',
            'grading_components.*.score' => 'required|numeric',
            'grading_components.*.by_who' => 'nullable|string',
            'grading_components.*.scheduled_week' => 'nullable|numeric',
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
            'dep_id' => $validated['dep_id'] ?? null,
        ]);

        // Create associated GradingComponents only if they exist
        foreach ($validated['grading_components'] as $component) {
            if (!empty($component['name']) && isset($component['score'])) {
                $gradingSchema->gradingComponents()->create([
                    'grading_schema_id' => $gradingSchema->id,
                    'name' => $component['name'],
                    'score' => $component['score'],
                    'dep_id' => $validated['dep_id'] ?? null,
                    'by_who' => $component['by_who'] ?? 'Supervisor', // Default to Supervisor if 'by_who' is not provided
                    'scheduled_week' => $component['scheduled_week'],
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
            'grading_components.*.scheduled_week' => 'nullable|numeric',
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
                    'scheduled_week' => $component['scheduled_week'],
                ]);
                $keptComponentIds[] = $existingComponent->id;
            } else {
                // Create new component
                $newComponent = $gradingSchema->gradingComponents()->create([
                    'name' => $component['name'],
                    'score' => $component['score'],
                    'by_who' => $component['by_who'] ?? 'Supervisor',
                    'scheduled_week' => $component['scheduled_week'],
                ]);
                $keptComponentIds[] = $newComponent->id;
            }
        }

        // Remove components that are not processed (updated or created)
        $gradingSchema->gradingComponents()->whereNotIn('id', $keptComponentIds)->delete();

        return response()->json(
            [
                'message' => 'Grading Schema updated successfully!',
                'data' => $gradingSchema->load('gradingComponents'),
            ],
            200,
        );
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

        return response()->json(
            [
                'message' => 'Бүрэлдэхүүн хэсгүүд амжилттай нэмэгдлээ!',
                'grading_schema' => $gradingSchema->load('gradingComponents'),
            ],
            200,
        );
    }

    public function destroy($id)
    {

        $isUsed = ThesisCycle::where('grading_schema_id', $id)->exists();
        
        if ($isUsed) {
            return response()->json([
                'message' => 'Энэ үнэлгээний схемийг нэг буюу хэд хэдэн дипломын ажлын циклд ашиглаж байгаа тул устгах боломжгүй'
            ], 422); // 422 = Unprocessable Entity
        }
        
        // If not used, proceed with deletion
        GradingSchema::findOrFail($id)->delete();
        
        return response()->json([
            'message' => 'Үнэлгээний схемийг амжилттай устгалаа'
        ], 200);
    }
}
