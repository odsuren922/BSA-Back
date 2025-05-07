<?php

namespace App\Http\Controllers\Grading;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\GradingComponent;

class GradingComponentController extends Controller
{
    public function index()
    {
        return GradingComponent::with('gradingCriteria')->get();
    }

    public function show($id)
    {
        return GradingComponent::with('gradingCriteria')->findOrFail($id);
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'grading_schema_id' => 'required|exists:grading_schemas,id',
            'score' => 'required|numeric|min:0|max:100',
            'by_who' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ]);

        return GradingComponent::create($validated);
    }

    public function update(Request $request, $id)
    {
        $gradingComponent = GradingComponent::findOrFail($id);
        $gradingComponent->update($request->all());
        return response()->json($gradingComponent, 200);
    }

    public function destroy($id)
    {
        GradingComponent::findOrFail($id)->delete();
        return response()->json(['message' => 'Grading component deleted'], 200);
    }



}
