<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupervisorController extends Controller
{
    // Бүх хянагч багш нарын жагсаалт
    public function index()
    {
        return response()->json(\App\Models\Supervisor::all());
    }

    // Нэг хянагч багшийг харах
    public function show($id)
    {
        $supervisor = \App\Models\Supervisor::find($id);

        if (!$supervisor) {
            return response()->json(['message' => 'Supervisor not found'], 404);
        }

        return response()->json($supervisor);
    }

    // 🟡 Оюутан болон багшийн сэдэв дэвшүүлэлтийг харах - submitted
    public function getSubmittedTopics()
    {
        $topics = Topic::where('status', 'submitted')->get();

        return response()->json($topics);
    }

    // 🟢 Баталсан сэдвүүд - approved
    public function getApprovedTopics()
    {
        $topics = Topic::where('status', 'approved')->get();

        return response()->json($topics);
    }

    // 🔴 Түтгэлзүүлсэн сэдвүүд - refused
    public function getRefusedTopics()
    {
        $topics = Topic::where('status', 'refused')->get();

        return response()->json($topics);
    }

    // ✅ Сэдвийг батлах
    public function approveTopic(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
        ]);

        try {
            $topic = Topic::findOrFail($validated['topic_id']);
            $topic->status = 'approved';
            $topic->save();

            return response()->json(['message' => 'Сэдвийг амжилттай баталлаа.']);
        } catch (\Exception $e) {
            Log::error('Approve error: ' . $e->getMessage());
            return response()->json(['message' => 'Сэдвийг батлахад алдаа гарлаа.'], 500);
        }
    }

    // ❌ Сэдвийг түтгэлзүүлэх
    public function refuseTopic(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
        ]);

        try {
            $topic = Topic::findOrFail($validated['topic_id']);
            $topic->status = 'refused';
            $topic->save();

            return response()->json(['message' => 'Сэдвийг түтгэлзүүллээ.']);
        } catch (\Exception $e) {
            Log::error('Refuse error: ' . $e->getMessage());
            return response()->json(['message' => 'Сэдвийг түтгэлзүүлэхэд алдаа гарлаа.'], 500);
        }
    }
}
