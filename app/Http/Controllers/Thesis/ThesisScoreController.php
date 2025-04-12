<?php

namespace App\Http\Controllers\Thesis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Thesis;
use App\Models\GradingSchema;
use App\Models\ThesisScore;

class ThesisScoreController extends Controller
{
    //
    public function getThesisScores($thesisId)
    {
        $thesis = Thesis::with('thesisCycle.gradingSchema.gradingComponents')->findOrFail($thesisId);
    
        $components = $thesis->thesisCycle->gradingSchema->gradingComponents;
        $result = [];
    
        foreach ($components as $component) {
            if ($component->by_who === 'committee') {
                $scores = ThesisScore::where('thesis_id', $thesisId)
                    ->where('grading_component_id', $component->id)
                    ->where('given_by', 'committee')
                    ->with('teacher')
                    ->get();
    
                $avgScore = $scores->avg('score');
    
                $result[] = [
                    'component_id' => $component->id,
                    'component_name' => $component->name,
                    'by_who' => $component->by_who,
                    'max_score' => $component->max_score,
                    'score' => $avgScore,
                    'member_scores' => $scores->map(fn($s) => [
                        'teacher_name' => $s->teacher->fullname,
                        'score' => $s->score
                    ])
                ];
            } else {
                $scoreObj = ThesisScore::where('thesis_id', $thesisId)
                    ->where('grading_component_id', $component->id)
                    ->where('given_by', $component->by_who)
                    ->first();
    
                $result[] = [
                    'component_id' => $component->id,
                    'component_name' => $component->name,
                    'by_who' => $component->by_who,
                    'max_score' => $component->max_score,
                    'score' => optional($scoreObj)->score,
                    'comment' => optional($scoreObj)->comment,
                ];
            }
        }
    
        return response()->json($result);
    }
    

    public function storeScore(Request $request)
{
    $validated = $request->validate([
        'thesis_id' => 'required|exists:thesis,id',
        'grading_component_id' => 'required|exists:grading_components,id',
        'teacher_id' => 'required|exists:teachers,id',
        'score' => 'required|numeric|min:0',
        'comment' => 'nullable|string',
        'given_by' => 'required|in:supervisor,committee,teacher',
        'committee_id' => 'nullable|exists:committees,id'
    ]);

    ThesisScore::updateOrCreate(
        [
            'thesis_id' => $validated['thesis_id'],
            'grading_component_id' => $validated['grading_component_id'],
            'teacher_id' => $validated['teacher_id'],
        ],
        [
            'score' => $validated['score'],
            'comment' => $validated['comment'],
            'given_by' => $validated['given_by'],
            'committee_id' => $validated['committee_id'],
        ]
    );

    return response()->json(['message' => 'Score saved successfully']);
}
}
