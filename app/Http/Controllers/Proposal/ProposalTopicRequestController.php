<?php

namespace App\Http\Controllers\Proposal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proposal\ProposalTopicRequest;
use App\Models\Proposal\ProposedTopic;
use App\Models\ThesisCycle;

use App\Http\Resources\Proposal\ProposalTopicRequestResource;

class ProposalTopicRequestController extends Controller
{
    private function getActiveCycle($depId)
    {
        return ThesisCycle::where('status', 'Идэвхитэй')->where('dep_id', $depId)->first();
    }

    // GET /api/proposal-topic-requests
    public function index(Request $request)
    {
        $query = ProposalTopicRequest::with(['topic', 'requestedBy']);

        // Optional filter by topic or student
        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
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
            'note' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $activeCycle = $this->getActiveCycle($user->dep_id);
        if (!$activeCycle) {
            return $this->errorResponse('Идэвхтэй дипломын цикл олдсонгүй.', 422);
        }

        $theTopic = ProposedTopic::findOrFail($validated['topic_id']);
        if ($theTopic->created_by_type === get_class($user)) {
            return response()->json(
                [
                    'message' => 'Та энэ сэдэвт хүсэлт өгөх эрхгүй байна',
                ],
                422,
            );
        }
        /**
         * Суралцагч ямар нэг сэдэврүү хүсэлт явуулах үед батлагдсан хүсэлт тухайн улиралд байгаа
         * эсэхийг шалгана
         * Мөн суралцагч хэрэв сэдэв дэвшүүлсэн бол тэр нь сонгогдсон эсэхийг шалгана
         */
        $studentId = $theTopic->created_by_type === 'App\\Models\\Teacher' ? $theTopic->created_by_id : $user->id;

        if (
            // batlagdsan huselt baigaa yu
            ProposalTopicRequest::where('requested_by_id', $studentId)->where('requested_by_type', 'App\\Models\\Student')->where('thesis_cycle_id', $activeCycle->id)->where('status', 'approved')->exists()
        ) {
            return response()->json(
                [
                    'message' => 'Энэ оюутан аль хэдийн өөр сэдэвт сонгогдсон байна.',
                ],
                422,
            );
        }

        //songogdson buyu dewsuulsen sedew ni udirdagch bagshtai bolood songogdson uyd
        $hasChosenCreatedTopic = ProposedTopic::where('created_by_type', 'App\\Models\\Student')->where('created_by_id', $studentId)->where('thesis_cycle_id', $activeCycle->id)->where('status', 'chosen')->exists();

        if ($hasChosenCreatedTopic) {
            return response()->json(
                [
                    'message' => 'Танд сонгогдсон сэдэв байна.',
                ],
                422,
            );
        }

        /**
         * herew bagsh tuhain topic ruu huselt ilgeej baigaa bol topic uusgesen suragchiin id gaar ni shalgana
         *
         */

        try {
            $topicRequest = ProposalTopicRequest::create([
                'topic_id' => $validated['topic_id'],
                'requested_by_id' => $user->id,
                'requested_by_type' => get_class($user),
                'thesis_cycle_id' => $activeCycle->id,
                'req_note' => $validated['note'] !== '' ? $validated['note'] : null,
                'selected_at' => now(),
            ]);

            return response()->json(
                [
                    'message' => 'Сэдвийн хүсэлт амжилттай илгээгдлээ!',
                    'data' => new ProposalTopicRequestResource($topicRequest),
                ],
                201,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'Сэдвийн хүсэлт хадгалах үед алдаа гарлаа.',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    // PUT /api/proposal-topic-requests/{id}/approve
    public function approve(Request $request, $id)
    {
        $user = $request->user();

        $topicRequest = ProposalTopicRequest::findOrFail($id);
        $topic = $topicRequest->topic;

        // 1. Сэдэв дээр аль хэдийн сонгогдсон хүсэлт байгаа эсэхийг шалгах
        if ($topic->topicRequests()->where('status', 'approved')->exists()) {
            return response()->json(
                [
                    'message' => 'Энэ сэдэв аль хэдийн сонгогдсон байна.',
                ],
                422,
            );
        }

        // 1. Идэвхтэй дипломын цикл олж авах
        $activeCycle = $this->getActiveCycle($user->dep_id);
        if (!$activeCycle) {
            return $this->errorResponse('Идэвхтэй дипломын цикл олдсонгүй.', 422);
        }

        // 2. requested_by_type нь Student бөгөөд тухайн оюутан өөр сэдэвт аль хэдийн сонгогдсон бол зогсооно
        if (
            $topicRequest->requested_by_type === 'App\\Models\\Student' &&
            ProposalTopicRequest::where('requested_by_id', $topicRequest->requested_by_id)->where('requested_by_type', 'App\\Models\\Student')->where('thesis_cycle_id', $activeCycle->id)->where('status', 'approved')//   ->whereHas('topic', fn ($q) => $q->where('status', 'chosen'))
            ->exists()
        ) {
            return response()->json(
                [
                    'message' => 'Энэ оюутан аль хэдийн өөр сэдэвт сонгогдсон байна.',
                ],
                422,
            );
        }

        if ($topicRequest->requested_by_type === 'App\\Models\\Teacher') {
            $hasChosenCreatedTopic = ProposedTopic::where('created_by_type', 'App\\Models\\Student')->where('created_by_id', $user->id)->where('thesis_cycle_id', $activeCycle->id)->where('status', 'chosen')->exists();

            if ($hasChosenCreatedTopic) {
                return response()->json(
                    [
                        'message' => 'Танд сонгогдсон сэдэв байна.',
                    ],
                    422,
                );
            }
        }

        // 3. Энэ хүсэлтийг сонгогдсон гэж тэмдэглэх
        // $topicRequest->is_selected = true;
        $topicRequest->selected_at = now();
        $topicRequest->status = 'approved'; // Сэдвийн хүсэлтийн статусыг баталгаажуулсан гэж тэмдэглэнэ
        $topicRequest->save();

        // 4. Сэдвийн төлөвийг шинэчлэх
        if ($topic) {
            $topic->status = 'chosen';
            $topic->save();
        }

        // 5. Бусад бүх хүсэлтийг татгалзсан гэж тэмдэглэх
        ProposalTopicRequest::where('topic_id', $topicRequest->topic_id)
            ->where('id', '!=', $topicRequest->id)
            // ->update(['is_selected' => false])
            ->update(['status' => 'rejected']);
        // 6. Сурагчийн дэвшүүлсэн бусад сэдвийн хүсэлтүүдийг цуцлах мөн архивлах
        
        return response()->json([
            'message' => 'Сэдэв амжилттай батлагдлаа.',
            'data' => new ProposalTopicRequestResource($topicRequest),
        ]);
    }

    public function decline(Request $request, $id)
    {
        $topicRequest = ProposalTopicRequest::findOrFail($id);
        $topic = $topicRequest->topic;

        if ($topicRequest->status !== 'approved') {
            return response()->json(
                [
                    'message' => 'Энэ хүсэлт сонгогдоогүй тул татгалзах боломжгүй.',
                ],
                400,
            );
        }

        // 1. Сонгогдсон төлөвийг цуцлах
        // $topicRequest->is_selected = false;
        $topicRequest->selected_at = null;
        $topicRequest->status = 'rejected'; // Сэдвийн хүсэлтийн статусыг татгалзсан гэж тэмдэглэнэ
        $topicRequest->save();

        // 2. ProposedTopic статусыг буцааж "approved" болгох
        if ($topic) {
            $topic->status = 'approved';
            $topic->save();
        }

        return response()->json([
            'message' => 'Сонгогдсон хүсэлтийг татгалзлаа.',
            'data' => $topicRequest,
        ]);
    }
    public function cancelling(Request $request, $id)
    {
        $proposalRequest = ProposalTopicRequest::findOrFail($id);
        // Just cancel it
        $proposalRequest->status = 'cancelled';
        $proposalRequest->save();

        return response()->json([
            'message' => 'Сэдэв хүсэлт цуцлагдлаа.',
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
