<?php

namespace App\Http\Controllers\Proposal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proposal\ProposalTopicRequest;
use App\Http\Resources\Proposal\ProposalTopicRequestResource;

class ProposalTopicRequestController extends Controller
{
    // GET /api/proposal-topic-requests
    public function index(Request $request)
    {
        $query = ProposalTopicRequest::with(['topic', 'requestedBy']);

        // Optional filter by topic or student
        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
        }

        if ($request->filled('student_id')) {
            $query->where('requested_by_id', $request->student_id)
                  ->where('requested_by_type', 'App\Models\Student');
        }

        return ProposalTopicRequestResource::collection($query->latest()->get());
    }

    // GET /api/proposal-topic-requests/{id}
    public function show($id)
    {
        $request = ProposalTopicRequest::with(['topic', 'requestedBy'])->findOrFail($id);
        return new ProposalTopicRequestResource($request);
    }

    // POST /api/proposal-topic-requests
    public function store(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:proposed_topics,id',
            'student_id' => 'required|exists:students,id',
            'note' => 'nullable|string',
            'selection_date' => 'required|date_format:Y-m-d H:i:s',
        ]);

        try {
            $topicRequest = ProposalTopicRequest::create([
                'topic_id' => $validated['topic_id'],
                'requested_by_id' => $validated['student_id'],
                'requested_by_type' => 'App\Models\Student',
                'req_note' => $validated['note'],
                'is_selected' => false,
                'selected_at' => $validated['selection_date'],
            ]);

            return response()->json([
                'message' => 'Topic request saved successfully!',
                'data' => new ProposalTopicRequestResource($topicRequest),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save topic request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/proposal-topic-requests/{id}/approve
    public function approve($id)
    {
        $request = ProposalTopicRequest::findOrFail($id);
        $request->is_selected = true;
        $request->selected_at = now();
        $request->approved_by_id = auth()->id(); // optional if you track approver
        $request->save();

        return response()->json([
            'message' => 'Сэдэв батлагдлаа',
            'data' => new ProposalTopicRequestResource($request),
        ]);
    }

    // DELETE /api/proposal-topic-requests/{id}
    public function destroy($id)
    {
        $request = ProposalTopicRequest::findOrFail($id);
        $request->delete();

        return response()->json([
            'message' => 'Сэдэв хүсэлт устгагдлаа.',
        ]);
    }
}
