<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommitteeScore;
use App\Models\Score;
use App\Http\Resources\CommitteeScoreResource;
use App\Http\Resources\ScoreResource;
use App\Models\CommitteeStudent;
use App\Models\CommitteeMember;
use App\Models\Committee;

use Illuminate\Support\Facades\Validator;

class CommitteeScoreController extends Controller
{
    // GET /api/committee-scores
    public function index()
    {
        $scores = CommitteeScore::with(['thesis', 'student', 'committeeMember', 'component'])->get();
        return CommitteeScoreResource::collection($scores);
    }

    // POST /api/committee-scores
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'score_id' => 'nullable|exists:scores,id',
            'thesis_id' => 'required|exists:theses,id',
            'student_id' => 'required|exists:students,id',
            'committee_member_id' => 'required|exists:committee_members,id',
            'component_id' => 'required|exists:grading_components,id',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $score = CommitteeScore::create($request->all());
        return new CommitteeScoreResource($score->load(['thesis', 'student', 'committeeMember', 'component']));
    }

    // GET /api/committee-scores/{id}
    public function show($id)
    {
        $score = CommitteeScore::with(['thesis', 'student', 'committeeMember', 'component'])->findOrFail($id);
        return new CommitteeScoreResource($score);
    }

    // PUT/PATCH /api/committee-scores/{id}
    public function update(Request $request, $id)
    {
        $score = CommitteeScore::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'score' => 'sometimes|required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $score->update($request->only(['score']));
        return new CommitteeScoreResource($score->fresh(['thesis', 'student', 'committeeMember', 'component']));
    }

    public function storeBatch(Request $request)
    {
        $data = $request->all();

        if (!is_array($data)) {
            return response()->json(['error' => 'Invalid format. Expecting an array of score objects.'], 400);
        }

        $errors = [];
        $processed = [];

        foreach ($data as $index => $item) {
            $validator = Validator::make($item, [
                'student_id' => 'required|exists:students,id',
                'thesis_id' => 'required|exists:thesis,id',
                'committee_member_id' => 'required|exists:committee_members,id',
                'component_id' => 'required|exists:grading_components,id',
                'score' => 'required|numeric|min:0|max:100',
            ]);

            if ($validator->fails()) {
                $errors[$index] = $validator->errors();
                continue;
            }

            // Check if the record already exists
            $existing = CommitteeScore::where('student_id', $item['student_id'])->where('committee_member_id', $item['committee_member_id'])->where('component_id', $item['component_id'])->first();

            if ($existing) {
                // Update existing score
                $existing->update(['score' => $item['score']]);
                $processed[] = $existing;
            } else {
                // Create new score
                $processed[] = CommitteeScore::create($item);
            }
        }

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        return CommitteeScoreResource::collection(
            CommitteeScore::whereIn('id', collect($processed)->pluck('id'))
                ->with(['thesis', 'student', 'committeeMember', 'component'])
                ->get(),
        );
    }
    //Нэг оюутан (student) + нэг үнэлгээний хэсэг (component) дээр
    // Бүх committee гишүүд оноогоо өгсөн эсэхийг шалгаад
    // Бүх гишүүд өгсөн байвал дундаж оноо тооцоод
    // Score хүснэгтэд хадгална

    public function finalizeCommitteeScores($studentId, $componentId)
    {
        $committeeScores = CommitteeScore::where('student_id', $studentId)
            ->where('component_id', $componentId)
            ->with('committeeMember')
            ->get();
    
        if ($committeeScores->isEmpty()) {
            return response()->json(['error' => 'No committee scores found.'], 404);
        }
    
        $firstCommitteeMember = $committeeScores->first()->committeeMember;
    
        if (!$firstCommitteeMember) {
            return response()->json(['error' => 'Committee member information missing.'], 500);
        }
    
        $committeeId = $firstCommitteeMember->committee_id;
    
        //  Аль хэдийн finalize хийсэн оноо байгаа эсэхийг шалгах
        $existingScore = Score::where('student_id', $studentId)
            ->where('component_id', $componentId)
            ->where('given_by_type', 'App\Models\Committee')
            ->where('given_by_id', $committeeId)
            ->first();
    
        if ($existingScore) {
            //  CommitteeScore хамгийн сүүлд хэзээ update болсон бэ?
            $latestCommitteeScoreUpdate = $committeeScores->max('updated_at');
    
            //  Existing Score хамгийн сүүлд хэзээ update болсон бэ?
            $scoreLastUpdated = $existingScore->updated_at;
    
            if ($latestCommitteeScoreUpdate <= $scoreLastUpdated) {
                //  Хэрэв CommitteeScore өөрчлөгдөөгүй бол finalize хийхийг хориглоно
                return response()->json(['error' => 'This score has already been finalized and no updates detected.'], 400);
            }
    
            //  CommitteeScore шинэчлэгдсэн бол дараа нь үргэлжлүүлж шинэчилнэ
            $existingScore->update([
                'score' => round($committeeScores->avg('score'), 2),
            ]);
    
            return new ScoreResource($existingScore);
        }
    
        // Коммитийн нийт гишүүдийн тоо
        $committeeMemberCount = \App\Models\CommitteeMember::where('committee_id', $committeeId)->count();
        $givenScoresCount = $committeeScores->count();
    
        if ($committeeMemberCount !== $givenScoresCount) {
            return response()->json(['error' => 'Not all committee members have given scores yet.'], 400);
        }
    
        $averageScore = round($committeeScores->avg('score'), 2);
    
        //  Сурагчийн committee_student_id авах
        $committeeStudent = \App\Models\CommitteeStudent::where('committee_id', $committeeId)
            ->where('student_id', $studentId)
            ->first();
    
        if (!$committeeStudent) {
            return response()->json(['error' => 'Committee student not found.'], 404);
        }
    
        $score = Score::create([
            'thesis_id' => $committeeScores->first()->thesis_id,
            'student_id' => $studentId,
            'component_id' => $componentId,
            'score' => $averageScore,
            'given_by_type' => 'App\Models\Committee',
            'given_by_id' => $committeeId,
            'committee_student_id' => $committeeStudent->id,
        ]);
    
        return new ScoreResource($score);
    }
    public function batchFinalizeByCommittee(Request $request)
    {
        $data = $request->validate([
            'committee_id' => 'required|integer|exists:committees,id',
        ]);
    
        $committeeId = $data['committee_id'];
        $committee = Committee::with('gradingComponent')->findOrFail($committeeId);
        
        // Validate grading component exists
        if (!$committee->grading_component_id) {
            return response()->json(['error' => 'Committee has no associated grading component.'], 400);
        }
    
        $committeeStudents = CommitteeStudent::where('committee_id', $committeeId)
            ->with('student')
            ->get();
    
        if ($committeeStudents->isEmpty()) {
            return response()->json(['error' => 'No students found in this committee.'], 404);
        }
    
        $committeeMemberCount = CommitteeMember::where('committee_id', $committeeId)->count();
        if ($committeeMemberCount === 0) {
            return response()->json(['error' => 'No committee members found for this committee.'], 400);
        }
    
        $results = [
            'success' => [],
            'failed' => [],
        ];
    
        foreach ($committeeStudents as $committeeStudent) {
            $studentId = $committeeStudent->student_id;
            $componentId = $committee->grading_component_id;
    
            $committeeScores = CommitteeScore::where('student_id', $studentId)
                ->where('component_id', $componentId)
                ->with('committeeMember')
                ->get();
    
            // Validation checks
            if ($committeeScores->isEmpty()) {
                $results['failed'][] = $this->createFailedResult($studentId, $componentId, 'No committee scores found');
                continue;
            }
    
            $firstCommitteeMember = $committeeScores->first()->committeeMember;
            if (!$firstCommitteeMember) {
                $results['failed'][] = $this->createFailedResult($studentId, $componentId, 'Committee member info missing');
                continue;
            }
    
            if ($committeeScores->count() !== $committeeMemberCount) {
                $results['failed'][] = $this->createFailedResult($studentId, $componentId, 'Not all committee members have given scores');
                continue;
            }
    
            // Calculate average score
            $averageScore = round($committeeScores->avg('score'), 2);
    
            // Handle existing or new score
            $existingScore = Score::where('student_id', $studentId)
                ->where('component_id', $componentId)
                ->where('given_by_type', 'App\Models\Committee')
                ->where('given_by_id', $committeeId)
                ->first();
    
            if ($existingScore) {
                $latestCommitteeScoreUpdate = $committeeScores->max('updated_at');
                if ($latestCommitteeScoreUpdate > $existingScore->updated_at) {
                    $existingScore->update(['score' => $averageScore]);
                    $results['success'][] = $this->createSuccessResult($studentId, $componentId, 'updated');
                } else {
                    $results['failed'][] = $this->createFailedResult($studentId, $componentId, 'Already finalized and no updates detected');
                }
            } else {
                Score::create([
                    'thesis_id' => $committeeScores->first()->thesis_id,
                    'student_id' => $studentId,
                    'component_id' => $componentId,
                    'score' => $averageScore,
                    'given_by_type' => 'App\Models\Committee',
                    'given_by_id' => $committeeId,
                    'committee_student_id' => $committeeStudent->id,
                ]);
                $results['success'][] = $this->createSuccessResult($studentId, $componentId, 'created');
            }
        }
    
        return response()->json([
            'message' => 'Batch finalization completed',
            'results' => $results,
            'stats' => [
                'total_students' => $committeeStudents->count(),
                'successful' => count($results['success']),
                'failed' => count($results['failed']),
            ]
        ]);
    }
    
    // Helper methods for consistent result formatting
    private function createSuccessResult($studentId, $componentId, $status)
    {
        return [
            'student_id' => $studentId,
            'component_id' => $componentId,
            'status' => $status,
        ];
    }
    
    private function createFailedResult($studentId, $componentId, $reason)
    {
        return [
            'student_id' => $studentId,
            'component_id' => $componentId,
            'reason' => $reason,
        ];
    }

    
    
    

    // DELETE /api/committee-scores/{id}
    public function destroy($id)
    {
        $score = CommitteeScore::findOrFail($id);
        $score->delete();

        return response()->json(['message' => 'Committee score deleted successfully.']);
    }
}
