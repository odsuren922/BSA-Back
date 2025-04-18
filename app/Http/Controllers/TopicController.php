<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Student;
use App\Models\TopicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TopicController extends Controller
{
    public function index()
    {
        return Topic::with(['student', 'advisor'])->get();
    }

    public function getSubmittedTopicsByType($type)
    {
        if (!in_array($type, ['student', 'teacher'])) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $topics = Topic::where('status', 'submitted')
            ->where('created_by_type', $type)
            ->get();

        return response()->json($topics);
    }

    public function getCheckedTopics()
    {
        $topics = Topic::where('status', 'approved')
            ->where('created_by_type', 'teacher')
            ->get();

        return response()->json($topics);
    }

    public function getCheckedTopicsByStud()
    {
        $topics = Topic::where('status', 'approved')
            ->where('created_by_type', 'student')
            ->get();

        return response()->json($topics);
    }

    public function getTopicListProposedByUser(Request $request)
    {
        $userType = $request->query('user_type');

        $topics = Topic::whereIn('status', ['submitted', 'approved', 'refused'])
            ->where('created_by_type', $userType)
            ->get();

        return response()->json($topics);
    }

    public function getDraftTopicsByStudent()
    {
        $topics = Topic::whereIn('status', ['draft', 'refused'])
            ->where('created_by_type', 'student')
            ->get();

        return response()->json($topics);
    }

    public function getDraftTopicsByTeacher()
    {
        $topics = Topic::whereIn('status', ['draft', 'refused'])
            ->where('created_by_type', 'teacher')
            ->get();

        return response()->json($topics);
    }

    public function storestudent(Request $request)
    {
        $validated = $request->validate([
            'form_id' => 'required|string|max:10',
            'fields' => 'array|required',
            'fields.*.field' => 'required',
            'fields.*.field2' => 'required',
            'fields.*.value' => 'required',
            'status' => 'required|string',
        ]);

        Topic::create([
            'form_id' => $validated['form_id'],
            'fields' => json_encode($validated['fields']),
            'status' => $validated['status'],
            'created_by_type' => 'student',
            'created_by_id' => auth()->id() ?? 1,
        ]);

        return response()->json(['message' => 'Оюутны сэдэв хадгалагдлаа']);
    }

    public function storeteacher(Request $request)
    {
        $validated = $request->validate([
            'form_id' => 'required|string|max:10',
            'fields' => 'array|required',
            'fields.*.field' => 'required',
            'fields.*.field2' => 'required',
            'fields.*.value' => 'required',
            'program' => 'nullable',
            'status' => 'required|string',
        ]);

        Topic::create([
            'form_id' => $validated['form_id'],
            'fields' => json_encode($validated['fields']),
            'program' => json_encode($validated['program']),
            'status' => $validated['status'],
            'created_by_type' => 'teacher',
            'created_by_id' => auth()->id() ?? 1,
            'advisor_id' => auth()->id() ?? 1,
        ]);

        return response()->json(['message' => 'Багшийн сэдэв хадгалагдлаа']);
    }

    public function confirmTopic(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|integer',
            'req_id' => 'required|integer',
            'student_id' => 'required|integer',
            'res_date' => 'nullable',
        ]);

        try {
            $topic = Topic::findOrFail($validated['topic_id']);
            $topic->update([
                'status' => 'confirmed',
                'student_id' => $validated['student_id'],
                'advisor_id' => $topic->created_by_type === 'teacher'
                    ? $topic->created_by_id
                    : auth()->id() ?? 1, // teacher ID from request context
            ]);

            Student::find($validated['student_id'])?->update([
                'is_choosed' => true,
            ]);

            TopicRequest::find($validated['req_id'])?->update([
                'is_selected' => true,
                'selected_date' => $validated['res_date'],
            ]);

            return response()->json(['message' => 'Сэдэв баталгаажлаа']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Сэдэв баталгаажуулахад алдаа гарлаа'], 500);
        }
    }

    public function declineTopic(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|integer',
            'req_id' => 'required|integer',
            'student_id' => 'required|integer',
            'res_date' => 'nullable',
        ]);

        try {
            $topic = Topic::findOrFail($validated['topic_id']);
            $topic->update(['status' => 'approved']);

            Student::find($validated['student_id'])?->update(['is_choosed' => false]);

            TopicRequest::find($validated['req_id'])?->update([
                'is_selected' => false,
                'selected_date' => $validated['res_date'],
            ]);

            return response()->json(['message' => 'Сэдэв цуцлагдлаа']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Сэдэв цуцлахад алдаа гарлаа'], 500);
        }
    }

    public function show($id)
    {
        return Topic::with(['student', 'advisor'])->findOrFail($id);
    }

    public function getRefusedOrApprovedTopics()
    {
        return Topic::whereIn('status', ['refused', 'approved'])->get();
    }

    public function getConfirmedTopicsWithAdvisors()
    {
        $topics = Topic::with(['student', 'advisor'])->where('status', 'confirmed')->get();

        $result = $topics->map(function ($topic) {
            return [
                'student_name' => optional($topic->student)->first_name . ' ' . optional($topic->student)->last_name,
                'sisi_id' => optional($topic->student)->sisi_id,
                'topic_title' => optional(collect(json_decode($topic->fields))->firstWhere('field', 'name_mongolian'))->value ?? '-',
                'advisor_name' => $topic->created_by_type === 'teacher'
                    ? optional($topic->advisor)->name
                    : 'Өөрөө',
            ];
        });

        return response()->json($result);
    }

    public function update(Request $request, $id)
    {
        $topic = Topic::findOrFail($id);
        $topic->update($request->all());
        return response()->json($topic);
    }
    public function getConfirmedTopicsByTeacher(Request $request)
{
    $user = auth()->user();
    
    if (!$user || $user->role !== 'teacher') {
        return response()->json(['error' => 'Unauthorized.'], 403);
    }

    $topics = Topic::with(['student'])
        ->where('advisor_id', $user->id)
        ->where('status', 'confirmed')
        ->get();

    $data = $topics->map(function ($topic) {
        $fieldsArray = json_decode($topic->fields, true) ?? [];
        $fields = [];
        foreach ($fieldsArray as $field) {
            $fields[$field['field']] = $field['value'];
            $fields[$field['field'].'_name'] = $field['field2'];
        }

        return array_merge($fields, [
            'id' => $topic->id,
            'topic_id' => $topic->id,
            'fields' => $topic->fields,
            'sisi_id' => optional($topic->student)->sisi_id,
            'firstname' => optional($topic->student)->first_name,
            'lastname' => optional($topic->student)->last_name,
            'created_by_id' => $topic->student_id,
            'req_id' => $topic->request_id ?? null,
        ]);
    });

    return response()->json(['data' => $data]);
}

}
