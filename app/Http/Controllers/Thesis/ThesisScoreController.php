<?php

namespace App\Http\Controllers\Thesis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Thesis;
use App\Models\GradingSchema;
use App\Models\ThesisScore;
use App\Models\Committee;
use App\Http\Resources\ThesisScoreResource;
use App\Http\Resources\ThesisResource;
use Illuminate\Support\Facades\DB;


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
                    'scheduled_week' =>$component->scheduled_week,
                    'by_who' => $component->by_who,
                    'max_score' => $component->score,
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
                    'scheduled_week' =>$component->scheduled_week,
                    'by_who' => $component->by_who,
                    'max_score' => $component->score,
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


public function storeMultipleScores(Request $request, $thesisId)
{
    $validated = $request->validate([
        'scores' => 'required|array',
        'scores.*.grading_component_id' => 'required|exists:grading_components,id',
        'scores.*.teacher_id' => 'required|exists:teachers,id',
        'scores.*.score' => 'required|numeric|min:0',
        'scores.*.comment' => 'nullable|string',
        'scores.*.given_by' => 'required|in:supervisor,committee,teacher',
        'scores.*.committee_id' => 'nullable|exists:committees,id',
    ]);

    foreach($validated['scores'] as $score) {
        ThesisScore::updateOrCreate(
            [
                'thesis_id' => $thesisId,
                'grading_component_id' => $score['grading_component_id'],
                'teacher_id' => $score['teacher_id'],
            ],
            [
                'score' => $score['score'],
                'comment' => $score['comment'],
                'given_by' => $score['given_by'],
                'committee_id' => $score['committee_id'],
            ]
        );
    }

    return response()->json(['message' => 'All scores saved successfully!']);
}




public function storeBulk(Request $request)
{
    $validated = $request->validate([
        'data' => 'required|array',
        'data.*.student_id' => 'required|exists:students,id',
        'data.*.teacher_id' => 'required|exists:teachers,id',
        'data.*.component_id' => 'required|exists:grading_components,id',
        'data.*.score' => 'required|numeric|min:0|max:100',
        'data.*.committee_id' => 'nullable|exists:committees,id',
    ]);

    DB::beginTransaction();

    try {
        $updatedCount = 0;
        $unchangedCount = 0;
        
        foreach ($validated['data'] as $scoreData) {
            $thesis = Thesis::where('student_id', $scoreData['student_id'])->first();

            if (!$thesis) {
                throw new \Exception('Thesis not found for student_id: '.$scoreData['student_id']);
            }

            // First try to find existing record
            $existingScore = ThesisScore::where([
                'thesis_id' => $thesis->id,
                'teacher_id' => $scoreData['teacher_id'],
                'grading_component_id' => $scoreData['component_id'],
                'given_by' => 'committee',
                'committee_id' => $scoreData['committee_id'],
            ])->first();

            if ($existingScore) {
                // Only update if score has changed
                if ((float)$existingScore->score !== (float)$scoreData['score']) {
                    $existingScore->update(['score' => $scoreData['score']]);
                    $updatedCount++;
                } else {
                    $unchangedCount++;
                }
            } else {
                // Create new record if doesn't exist
                ThesisScore::create([
                    'thesis_id' => $thesis->id,
                    'teacher_id' => $scoreData['teacher_id'],
                    'grading_component_id' => $scoreData['component_id'],
                    'given_by' => 'committee',
                    'committee_id' => $scoreData['committee_id'],
                    'score' => $scoreData['score'],
                ]);
                $updatedCount++;
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Scores processed successfully',
            'stats' => [
                'updated' => $updatedCount,
                'unchanged' => $unchangedCount,
                'total' => count($validated['data'])
            ]
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Error saving scores', 
            'error' => $e->getMessage()
        ], 500);
    }
}
// ThesisScoreController.php

public function getCommitteeStudentScores(Committee $committee)
{
    $students = $committee->students()
        ->with('student')
        ->get();

    foreach($students as $student) {
        $student->scores = ThesisScore::whereHas('thesis', function($q) use ($student) {
                $q->where('student_id', $student->student_id);
            })
            ->where('grading_component_id', $committee->grading_component_id)
            ->where('given_by', 'committee')
            ->get(['teacher_id', 'score', 'committee_id']);
    }

    return response()->json($students);
}

public function index($id)
{
    $thesis = Thesis::with([
        'scores.teacher',
        // Keep this to load committee info but don't include in individual scores.
        'thesisCycle.gradingSchema.gradingComponents',
    ])->findOrFail($id);

    $gradingComponents = $thesis->thesisCycle->gradingSchema->gradingComponents->map(function ($component) use ($thesis) {
        $componentScores = $thesis->scores->where('grading_component_id', $component->id);

        if (in_array($component->by_who, ['teacher', 'supervisor'])) {
            $average = $componentScores->avg('score');
        
            $component->given_score = [
                'average_score' => $average,
            ];
                 // Just add the first score for supervisor/teacher.
        } elseif ($component->by_who === 'committee') {
            $firstScoreWithCommittee = $componentScores->firstWhere('committee_id', '!=', null);

            if ($firstScoreWithCommittee && $firstScoreWithCommittee->committee) {
                $committee = $firstScoreWithCommittee->committee;
                $committeeId = $firstScoreWithCommittee->committee_id;

                $committeeScores = $componentScores->where('committee_id', $committeeId);
                $memberCount = $committee->members->count();
                $submittedCount = $committeeScores->count();
                $average = $committeeScores->avg('score');

                // Store committee scores separately under `given_score` field in the component
                $component->given_score = [
                    'committee_scores' => $committeeScores->values(),
                    'average_score' => $submittedCount < $memberCount ? null : $average, // if not all gave score -> null
                    'submitted_count' => $submittedCount,
                    'member_count' => $memberCount,
                    'note' => $submittedCount < $memberCount 
                        ? "Only $submittedCount of $memberCount committee members have submitted scores." 
                        : null,
                ];
                
            } else {
                // No committee scores submitted yet
                $component->given_score = [
                    'committee_scores' => [],
                    'average_score' => null,
                    'note' => "No committee scores submitted yet.",
                ];
            }
        }

        return $component;
    });

    return response()->json($gradingComponents);
}


public function getScoresByCycleAndComponent($cycleId, $componentId)
{
    $students = \App\Models\Thesis::with('student')
        ->where('thesis_cycle_id', $cycleId)
        ->get();

    foreach($students as $student) {
        $student->scores = ThesisScoreResource::collection(
            \App\Models\ThesisScore::with(['teacher', 'committee', 'gradingComponent'])
                ->whereHas('thesis', function($q) use ($student) {
                    $q->where('student_id', $student->student_id);
                })
                ->where('grading_component_id', $componentId)
                ->get()
        );
    }

    return response()->json($students);
}





}
