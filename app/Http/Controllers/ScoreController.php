<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function index(Request $request)
    {
        $query = Score::query();

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        if ($request->has('committee_id')) {
            $query->where('committee_id', $request->committee_id);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer',
            'teacher_id' => 'nullable|integer',
            'committee_id' => 'nullable|integer',
            'grading_component_id' => 'required|exists:grading_components,id',
            'score_got' => 'required|numeric|min:0|max:100',
        ]);

        return Score::create($validated);
    }

    public function update(Request $request, $id)
    {
        $score = Score::findOrFail($id);
        $score->update($request->all());
        return response()->json($score, 200);
    }

    public function destroy($id)
    {
        Score::findOrFail($id)->delete();
        return response()->json(['message' => 'Score deleted'], 200);
    }
}
