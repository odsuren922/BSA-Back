<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommitteeScore;
use App\Models\Score;
use App\Http\Resources\CommitteeScoreResource;
use App\Http\Resources\ScoreResource;
use App\Models\CommitteeStudent;
use App\Models\CommitteeMember;
use App\Models\Thesis;
use App\Models\Committee;
use App\Models\ExternalReviewerScore;
use App\Models\ExternalReviewer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function saveEditableScores(Request $request)
{
    $data = $request->validate([
        'committee_id' => 'required|exists:committees,id',
        'component_id' => 'required|exists:grading_components,id',
        'scores' => 'required|array',
        'scores.*.student_id' => 'required|exists:students,id',
        'scores.*.committee_member_id' => 'required|exists:committee_members,id',
        'scores.*.score' => 'required|numeric|min:0|max:100',
    ]);

    $errors = [];
    $processed = [];

    foreach ($data['scores'] as $index => $item) {
        try {
            $thesis = Thesis::where('student_id', $item['student_id'])->firstOrFail();
                $thesisId = $thesis->id;

            $existing = CommitteeScore::where([
                'student_id' => $item['student_id'],
                'committee_member_id' => $item['committee_member_id'],
                'component_id' => $data['component_id'],
            ])->first();

            if ($existing) {
                $existing->update(['score' => $item['score']]);
                $processed[] = $existing->id;
            } else {
                $new = CommitteeScore::create([
                    'student_id' => $item['student_id'],
                    'committee_member_id' => $item['committee_member_id'],
                    'component_id' => $data['component_id'],
                    'score' => $item['score'],
                    'thesis_id' => $thesisId,
                    
                ]);
                $processed[] = $new->id;
            }
        } catch (\Exception $e) {
            $errors[$index] = $e->getMessage();
        }
    }

    if (!empty($errors)) {
        return response()->json(['errors' => $errors], 422);
    }

    return CommitteeScoreResource::collection(
        CommitteeScore::whereIn('id', $processed)
            ->with(['student', 'committeeMember'])
            ->get()
    );
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
                ->with(['student', 'committeeMember'])
                ->get(),
        );
    }

    //Нэг оюутан (student) + нэг үнэлгээний хэсэг (component) дээр
    // Бүх committee гишүүд оноогоо өгсөн эсэхийг шалгаад
    // Бүх гишүүд өгсөн байвал дундаж оноо тооцоод
    // Score хүснэгтэд хадгална

    public function finalizeCommitteeScores($studentId, $componentId)
    {
        $committeeScores = CommitteeScore::where('student_id', $studentId)->where('component_id', $componentId)->with('committeeMember')->get();

        if ($committeeScores->isEmpty()) {
            return response()->json(['error' => 'No committee scores found.'], 404);
        }

        $firstCommitteeMember = $committeeScores->first()->committeeMember;

        if (!$firstCommitteeMember) {
            return response()->json(['error' => 'Committee member information missing.'], 500);
        }

        $committeeId = $firstCommitteeMember->committee_id;

        //  Аль хэдийн finalize хийсэн оноо байгаа эсэхийг шалгах
        $existingScore = Score::where('student_id', $studentId)->where('component_id', $componentId)->where('given_by_type', 'App\Models\Committee')->where('given_by_id', $committeeId)->first();

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
        $committeeStudent = \App\Models\CommitteeStudent::where('committee_id', $committeeId)->where('student_id', $studentId)->first();

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
            'committee_id' => 'required|exists:committees,id',
            'component_id' => 'required|exists:grading_components,id',
            'scores' => 'required|array',
            'scores.*.student_id' => 'required|integer|exists:students,id',
            'scores.*.average' => 'nullable|numeric|min:0|max:100',
        ]);

        $committeeId = $data['committee_id'];
        $scores = $data['scores'];

        $committee = Committee::with('gradingComponent')->findOrFail($committeeId);
        $componentId = $data['component_id'];

        $savedScores = [];
        DB::beginTransaction();
        try {
            // Log::info('Комисс оноо хадгалах эхэллээ');

            foreach ($scores as $scoreData) {
                $studentId = $scoreData['student_id'];
                $averageScore = $scoreData['average'];

                // Log::info('Оюутан: ', ['student_id' => $studentId, 'avg' => $averageScore]);

                $committeeStudent = CommitteeStudent::with('student.thesis')->where('committee_id', $committeeId)->where('student_id', $studentId)->first();

                if (!$committeeStudent || !$committeeStudent->student || !$committeeStudent->student->thesis) {
                    Log::warning('Оюутны эсвэл thesis мэдээлэл байхгүй', ['student_id' => $studentId]);
                    continue;
                }

                // $thesisId = $committeeStudent->student->thesis->id;
                $thesis = Thesis::where('student_id', $studentId)->firstOrFail();
                $thesisId = $thesis->id;

                Log::info('Thesis ID:', ['thesis_id' => $thesisId]);

                $score = Score::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'component_id' => $componentId,
                        'given_by_type' => 'App\Models\Committee',
                        'given_by_id' => $committeeId,
                    ],
                    [
                        'thesis_id' => $thesisId,
                        'score' => $averageScore,
                        'committee_student_id' => $committeeStudent->id,
                    ],
                );
                $savedScores[] = $score;

                // Log::info('Score хадгаллаа.');
            }
            DB::commit();
            return response()->json([
                'message' => 'Комиссын оноонууд амжилттай хадгалагдлаа.',
                'data' => ScoreResource::collection(collect($savedScores)),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Finalizing scores error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(
                [
                    'error' => 'Оноо хадгалахад алдаа гарлаа.',
                    'details' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    // DELETE /api/committee-scores/{id}
    public function destroy($id)
    {
        $score = CommitteeScore::findOrFail($id);
        $score->delete();

        return response()->json(['message' => 'Committee score deleted successfully.']);
    }
}
