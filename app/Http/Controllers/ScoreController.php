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
use App\Models\AssignedGrading;
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
    public function getScoresByComponentAndCycle($thesis_cycle_id, $component_id)
    {
        $component = GradingComponent::findOrFail($component_id);
        if ($component->by_who !== 'committee') {

            if ($component->by_who === 'supervisor') {
                $scores = Score::with([
                    'student',
                    'component',
                    'thesis.supervisor',
                    'givenBy' // <- Now added
                ])
                ->where('component_id', $component_id)
                ->whereHas('thesis', function ($query) use ($thesis_cycle_id) {
                    $query->where('thesis_cycle_id', $thesis_cycle_id);
                })
                ->get();
        
                return ScoreResource::collection($scores);
            } else {
                $scores = Score::with([
                    'student',
                    'component',
                    'thesis.supervisor',
                    'givenBy' // <- Now added
                ])
                ->where('component_id', $component_id)
                ->whereHas('thesis', function ($query) use ($thesis_cycle_id) {
                    $query->where('thesis_cycle_id', $thesis_cycle_id);
                })
                ->get();


    
            }
        }
    }
    




    /**
     * Шинэ оноо үүсгэх
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'thesis_id' => 'nullable|exists:thesis,id',
            'student_id' => 'required|exists:students,id',
            'component_id' => 'required|exists:grading_components,id',
            'score' => 'required|numeric|min:0|max:100',
            'given_by_type' => 'required|string',
            'given_by_id' => 'required|integer',
            'committee_student_id' => 'nullable|exists:committee_students,id',
        ]);
    
        // Normalize given_by_type to fully qualified class name
        $typeMap = [
            'teacher' => \App\Models\Teacher::class,
            'committee' => \App\Models\Committee::class,
            // 'admin' => \App\Models\Admin::class,
        ];
    
        $data['given_by_type'] = $typeMap[strtolower($data['given_by_type'])] ?? $data['given_by_type'];
    
        $score = Score::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'component_id' => $data['component_id'],
                'thesis_id' => $data['thesis_id'] ?? null,
            ],
            $data
        );
    
        $score->load(['student', 'thesis.supervisor', 'component', 'givenBy']);
    
        return new ScoreResource($score);
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
