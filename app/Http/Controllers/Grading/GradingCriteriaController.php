<?php

namespace App\Http\Controllers\Grading;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GradingCriteriaController extends Controller
{
    public function index()
    {
        return GradingCriteria::all();
    }

    public function show($id)
    {
        return GradingCriteria::findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grading_component_id' => 'required|exists:grading_components,id',
            'name' => 'required|string|max:255',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        return GradingCriteria::create($validated);
    }

    public function update(Request $request, $id)
    {
        $gradingCriteria = GradingCriteria::findOrFail($id);
        $gradingCriteria->update($request->all());
        return response()->json($gradingCriteria, 200);
    }

    public function destroy($id)
    {
        GradingCriteria::findOrFail($id)->delete();
        return response()->json(['message' => 'Grading criteria deleted'], 200);
    }
}

