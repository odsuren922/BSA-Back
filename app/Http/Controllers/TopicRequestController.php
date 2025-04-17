<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TopicRequest;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Topic;
use Illuminate\Support\Facades\Log;

class TopicRequestController extends Controller
{
    public function index()
    {
        $requests = TopicRequest::with(['teacher', 'student', 'topic'])->get();

        $data = $requests->map(function ($req) {
            return [
                'id' => $req->id,
                'topic_id' => $req->topic_id,
                'req_note' => $req->req_note,
                'is_selected' => $req->is_selected,
                'selected_date' => $req->selected_date,
                'created_by_type' => $req->created_by_type,
                'created_by_id' => $req->created_by_id,
                'firstname' => $req->created_by_type === 'student'
                    ? optional($req->student)->first_name
                    : optional($req->teacher)->name,
                'lastname' => $req->created_by_type === 'student'
                    ? optional($req->student)->last_name
                    : '',
                'sisi_id' => $req->created_by_type === 'student'
                    ? optional($req->student)->sisi_id
                    : '',
                'fields' => optional($req->topic)->fields,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'topic_id' => 'required|integer|exists:topics,id',
            'req_note' => 'nullable|string|max:500',
            'created_by_id' => 'required|integer',
            'created_by_type' => 'required|in:student,teacher',
        ]);

        $existing = TopicRequest::where([
            'topic_id' => $validatedData['topic_id'],
            'created_by_id' => $validatedData['created_by_id'],
            'created_by_type' => $validatedData['created_by_type'],
        ])->first();

        if ($existing) {
            return response()->json(['message' => 'Хүсэлт аль хэдийн илгээгдсэн байна.'], 409);
        }

        TopicRequest::create($validatedData);

        return response()->json(['message' => 'Хүсэлт амжилттай илгээгдлээ.']);
    }
}
