<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Committee;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommitteeResource;

class CommitteeController extends Controller
{
    public function index(Request $request)
    {
        $committees = Committee::with([
            'department', 
            'gradingComponent', 
            'members.teacher', 
            'students', 
            'schedules', 
            'thesis_cycle' // Load thesis cycle
        ])->where('dep_id', $request->user()->dep_id)
          ->paginate(10);
    
        return CommitteeResource::collection($committees);
    }
    
    public function show(Committee $committee)
    {
        return new CommitteeResource($committee->load([
            'department', 
            'gradingComponent', 
            'members.teacher', 
            'students.student', 
            'schedules',
            'thesis_cycle' // Load thesis cycle
        ]));
    }
    

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grading_component_id' => 'nullable|exists:grading_components,id',
            'dep_id' => 'required|exists:departments,id',
            'status' => 'nullable|in:planned,active,done,cancelled' // Status validation
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
            'status' => 'sometimes|in:planned,active,done,cancelled' // Status validation
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

