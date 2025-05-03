<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Score;
use App\Models\Thesis;
use App\Models\Department;
use App\Models\ThesisCycle;
use App\Models\GradingComponent;
use App\Models\CommitteeScore;
use App\Http\Resources\ThesisResource;
use App\Http\Resources\ScoreResource;

class ScoreController extends Controller
{
        /**
     * Бүх оноог жагсаах
     */
    public function index()
    {
        $scores = Score::with(['student', 'thesis', 'component', 'committeeScores.committeeMember'])
                        ->latest()
                        ->get();

        return ScoreResource::collection($scores);
    }
      /**
     * Нэг оноог дэлгэрэнгүй үзүүлэх
     */
    public function show($id)
    {
        $score = Score::with(['student', 'thesis', 'component', 'committeeScores.committeeMember'])
                      ->findOrFail($id);

        return new ScoreResource($score);
    }
    public function getScoreByThesis($id){
        $thesis= Thesis::findOrFail($id);
        //TODO:: WHAT IF THE COMMITTE SCORE IS NOT THERE 
       //NOT ALL SCORES COMES FROM 
       $scores = Score::with([ 'component',])
                        ->where('thesis_id', $thesis->id)
                        ->latest()
                        ->get();
         return ScoreResource::collection($scores);

    }



    public function getScoreByThesisWithDetail($id){
        $thesis= Thesis::findOrFail($id);
        //TODO:: WHAT IF THE COMMITTE SCORE IS NOT THERE 
       //NOT ALL SCORES COMES FROM 
       
        $scores = Score::with(['student', 'thesis', 'component'])
                        ->where('thesis_id', $thesis->id)
                        ->latest()
                        ->get();
        return ScoreResource::collection($scores);

    }



    /**
     * Шинэ оноо үүсгэх
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'thesis_id' => 'nullable|exists:theses,id',
            'student_id' => 'required|exists:students,id',
            'component_id' => 'required|exists:grading_components,id',
            'score' => 'required|numeric|min:0|max:100',
            'given_by_type' => 'required|string',
            'given_by_id' => 'required|integer',
            'committee_student_id' => 'nullable|exists:committee_students,id',
        ]);

        $score = Score::create($data);

        return new ScoreResource($score->load(['student', 'thesis', 'component']));
    }

    /**
     * Оноо засах
     */
    public function update(Request $request, $id)
    {
        $score = Score::findOrFail($id);

        $data = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
        ]);

        $score->update($data);

        return new ScoreResource($score->fresh(['student', 'thesis', 'component']));
    }

     /**
     * Оноог устгах
     */
    public function destroy($id)
    {
        $score = Score::findOrFail($id);
        $score->delete();

        return response()->json(['message' => 'Score deleted']);
    }


}
