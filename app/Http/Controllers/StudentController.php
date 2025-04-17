<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::all();
        return response()->json($students);
    }

    public function getWithTopics()
    {
        $students = Student::with(['selectedTopic.advisor', 'selectedTopic.teacher'])
            ->whereNotNull('selected_topic_id')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'sisi_id' => $student->sisi_id,
                    'selected_topic_id' => $student->selected_topic_id,
                    'topic_title' => optional($student->selectedTopic)->title,
                    'advisor_name' => optional($student->selectedTopic->advisor)->name,
                    'proposed_by_teacher' => optional($student->selectedTopic->teacher)->name,
                ];
            });

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'sisi_id' => 'required|string|unique:students,sisi_id',
            'email' => 'required|email|unique:students,email',
        ]);

        $student = Student::create($validated);

        return response()->json($student);
    }
}
