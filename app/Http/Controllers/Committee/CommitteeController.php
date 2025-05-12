<?php

namespace App\Http\Controllers\Committee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Http\Resources\CommitteeResource;
use App\Models\ThesisCycle;
use App\Models\GradingComponent;
use App\Models\CommitteeStudent;
use App\Models\CommitteeMember;
use App\Models\Committee;

class CommitteeController extends Controller
{
    public function index(Request $request)
    {
        
        $committees = Committee::with(['department', 'gradingComponent', 'members.teacher', 'members.committeeScores.student', 'students', 'schedules', 'thesis_cycle','scores','externalReviewers.scores']);
            // ->where('dep_id', $request->user()->dep_id)
            // ->paginate(10);

        return CommitteeResource::collection($committees);
    }
    public function getCommitteeMembersWithStudentsAndScores($committeeId)
{
    $committee = Committee::with([
        'gradingComponent',
        'members.teacher',                      // load teacher info
        'members.committeeScores.student',      // student info through scores
        'members.committeeScores.component',
        'students',    // grading component info
    ])->findOrFail($committeeId);

    return new CommitteeResource($committee);
}


    // thesis_cycle id tai
    public function getByThesisCycle(ThesisCycle $thesisCycle, Request $request)
    {
        $committees = Committee::with(['department', 'gradingComponent', 'members.teacher', 'students', 'schedules', 'thesis_cycle'])
            // ->where('dep_id', $request->user()->dep_id)
            ->where('thesis_cycle_id', $thesisCycle->id);
            // ->paginate(10);

        return CommitteeResource::collection($committees);
    }
    //TODO::

    public function getByCycleAndComponent(ThesisCycle $thesisCycle, GradingComponent $gradingComponent, Request $request)
    {
        //student.the component scoer maybe need to send 
        $committees = Committee::with(['department', 'gradingComponent', 'members.teacher', 'members.committeeScores.student', 'students', 'schedules', 'thesis_cycle','scores','externalReviewers.scores'])
            ->where('thesis_cycle_id', $thesisCycle->id)
            ->where('grading_component_id', $gradingComponent->id)
            // ->where('dep_id', $request->user()->dep_id)
             ->paginate(10);

        return CommitteeResource::collection($committees);
 
    }

   

    public function getActiveCycleValidCommittees(Request $request)
{
    $committees = Committee::with([
        //'department',
        'gradingComponent',
        'members.teacher',
        // 'students',
        'schedules',
       
    ])
        ->whereHas('thesis_cycle', function ($query) {
            $query->where('status', 'Идэвхитэй');
        })
        // ->where('dep_id', $request->user()->dep_id)
        ->whereNotIn('status', ['cancelled', 'done'])
        ->get();
        // ->paginate(10);

    return CommitteeResource::collection($committees);
}


    public function storeWithCycleAndComponent(Request $request, ThesisCycle $thesisCycle, GradingComponent $gradingComponent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $committee = Committee::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'thesis_cycle_id' => $thesisCycle->id,
            'grading_component_id' => $gradingComponent->id,
            'dep_id' => $request->user()->dep_id,
        ]);

        return new CommitteeResource($committee);
    }
    public function getCommitteesByTeacher($teacherId, Request $request)
{
    $committees = Committee::with([
        'gradingComponent',
        'schedules',
        'thesis_cycle',
    ])
    ->whereHas('members', function ($query) use ($teacherId) {
        $query->where('teacher_id', $teacherId);
    })
    // ->where('dep_id', $request->user()->dep_id)
    ->get();

    return CommitteeResource::collection($committees);
}

public function isTeacherAndStudentInSameCommittee(Request $request)
{
    $validated = $request->validate([
        'thesis_cycle_id' => 'required|exists:thesis_cycles,id',
        'grading_component_id' => 'required|exists:grading_components,id',
        'student_id' => 'required|exists:students,id',
        'teacher_id' => 'required|exists:teachers,id',
    ]);

    $cycleId = $validated['thesis_cycle_id'];
    $componentId = $validated['grading_component_id'];
    $studentId = $validated['student_id'];
    $teacherId = $validated['teacher_id'];

    // Step 1: Get committee IDs that match cycle and component
    $committeeIds = Committee::where('thesis_cycle_id', $cycleId)
        ->where('grading_component_id', $componentId)
        ->pluck('id');

    if ($committeeIds->isEmpty()) {
        return response()->json(['match' => false]);
    }

    // Step 2: Find if any of those committees have this student
    $studentCommitteeIds = CommitteeStudent::whereIn('committee_id', $committeeIds)
        ->where('student_id', $studentId)
        ->pluck('committee_id');

    if ($studentCommitteeIds->isEmpty()) {
        return response()->json(['match' => false]);
    }

    // Step 3: Check if teacher is in one of the same committees
    $match = CommitteeMember::whereIn('committee_id', $studentCommitteeIds)
        ->where('teacher_id', $teacherId)
        ->exists();

    return response()->json(['match' => $match]);
}


    public function show(Committee $committee)
    {
        //Загварын нэвтрэлтээр Committee-г эхлээд ачаалчихаад,
        // дараа нь load() ашиглан холбогдох мэдээллийг авна


        return new CommitteeResource(
            $committee->load(['department', 'gradingComponent', 'members.teacher', 'members.committeeScores.student', 'students.student', 'schedules', 'thesis_cycle','scores','externalReviewers.scores']),
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grading_component_id' => 'required|exists:grading_components,id',
            'thesis_cycle_id' => 'required|exists:thesis_cycles,id',
            'dep_id' => 'nullable|exists:departments,id',
            'status' => 'nullable|in:planned,active,done,cancelled', // Status validation
            'color' => 'required|string',
        ]);

        $committee = Committee::create($validated);
        return new CommitteeResource($committee);
    }

    public function update(Request $request, Committee $committee)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'grading_component_id' => 'nullable|exists:grading_components,id',
            'status' => 'sometimes|in:planned,active,done,cancelled', 
        ]);

        $committee->update($validated);
        return new CommitteeResource($committee);
    }
    

    public function destroy(Committee $committee)
    {
        $committee->delete();
        return response()->json(['message' => 'Committee deleted successfully']);
    }
}
