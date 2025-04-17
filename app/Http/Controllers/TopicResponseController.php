<?php

namespace App\Http\Controllers;

use App\Models\TopicResponse;
use App\Models\TopicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TopicResponseController extends Controller
{
    // Бүх хариуг татах
    public function index()
    {
        $responses = TopicResponse::with(['request'])->get();
        return response()->json($responses);
    }

    // Сэдэвт хүсэлтэд хариу илгээх
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'request_id' => 'required|integer|exists:topic_requests,id',
            'res_note' => 'nullable|string|max:500',
            'status' => 'required|in:approved,refused',
        ]);

        try {
            $existing = TopicResponse::where('request_id', $validatedData['request_id'])->first();
            if ($existing) {
                return response()->json(['message' => 'Хариу аль хэдийн илгээгдсэн байна.'], 409);
            }

            TopicResponse::create($validatedData);

            // Хүсэлтийн статус автоматаар шинэчлэгдэх
            $topicRequest = TopicRequest::findOrFail($validatedData['request_id']);
            $topicRequest->update([
                'status' => $validatedData['status'] === 'approved' ? 'approved' : 'refused',
            ]);

            return response()->json(['message' => 'Хариу амжилттай илгээгдлээ.']);
        } catch (\Exception $e) {
            Log::error('Хариу илгээхэд алдаа гарлаа: ' . $e->getMessage());
            return response()->json(['message' => 'Хариу илгээхэд алдаа гарлаа.'], 500);
        }
    }

    // Нэг хариуг харах
    public function show($id)
    {
        $response = TopicResponse::with('request')->findOrFail($id);
        return response()->json($response);
    }
}
