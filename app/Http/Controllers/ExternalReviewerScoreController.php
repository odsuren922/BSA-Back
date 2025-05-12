<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExternalReviewerScoreResource;
use App\Models\ExternalReviewerScore;
use Illuminate\Http\Request;

class ExternalReviewerScoreController extends Controller
{
    public function index()
    {
        return ExternalReviewerScoreResource::collection(
            ExternalReviewerScore::with(['externalReviewer', 'student', 'gradingComponent'])->get()
        );
    }
    //
    public function store(Request $request)
{
    $data = $request->validate([
        'external_reviewer_id' => 'required|exists:external_reviewers,id',
        'student_id' => 'required|exists:students,id',
        'grading_component_id' => 'nullable|exists:grading_components,id',
        'score' => 'required|numeric|min:0|max:100',
    ]);

    $score = ExternalReviewerScore::create($data);

    return new ExternalReviewerScoreResource($score);
}
public function storeBatch(Request $request)
{
    $data = $request->validate([
       'grading_component_id' => 'nullable|exists:grading_components,id',
        '*.student_id' => 'required|exists:students,id',
        '*.external_reviewer_id' => 'required|exists:external_reviewers,id',
        '*.score' => 'required|numeric|min:0|max:100',
    ]);

    $results = [];
    foreach ($data as $scoreData) {
        $score = ExternalReviewerScore::updateOrCreate(
            [
                'student_id' => $scoreData['student_id'],
                'external_reviewer_id' => $scoreData['external_reviewer_id'],
            ],
            ['score' => $scoreData['score']]
        );
        $results[] = $score;
    }

    return response()->json(['message' => 'External scores saved', 'data' => $results]);
}

public function storeBatch2(Request $request)
{
    // Step 1: Validate request data
    $data = $request->validate([
        'grading_component_id' => 'nullable|exists:grading_components,id',
        'external_scores' => 'required|array',
        'external_scores.*.student_id' => 'required|exists:students,id',
        'external_scores.*.external_reviewer_id' => 'required|exists:external_reviewers,id',
        'external_scores.*.score' => 'required|numeric|min:0|max:100',
    ]);

    $results = [];

    // Step 2: Process each score entry
    foreach ($data['external_scores'] as $scoreData) {
        $score = ExternalReviewerScore::updateOrCreate(
            [
                'student_id' => $scoreData['student_id'],
                'external_reviewer_id' => $scoreData['external_reviewer_id'],
            ],
            [
                'score' => $scoreData['score'],
                'grading_component_id' => $data['grading_component_id'] ?? null,
            ]
        );

        $results[] = $score;
    }

    // Step 3: Return successful JSON response
    return response()->json([
        'message' => 'External scores saved successfully.',
        'data' => $results,
    ]);
}



public function update(Request $request, ExternalReviewerScore $externalReviewerScore)
{
    $data = $request->validate([
        'score' => 'required|numeric|min:0|max:100',
    ]);

    $externalReviewerScore->update($data);

    return new ExternalReviewerScoreResource($externalReviewerScore);
}

}
