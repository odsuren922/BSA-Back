<?php

namespace App\Http\Controllers\Proposal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Proposal\ProposedTopic;
use App\Models\ThesisCycle;
use App\Models\Proposal\TopicContent;
use App\Models\Proposal\ProposalFieldValue;
use App\Models\Proposal\TopicApprovalLog;
use App\Http\Resources\Proposal\ProposedTopicResource;

class ProposedTopicController extends Controller
{
    /**
     * Бүх санал болгосон сэдвүүдийг жагсаана.
     * Хүсвэл дараах байдлаар шүүх боломжтой:
     * - status: төлөвөөр шүүх
     * - created_by_id: үүсгэсэн хэрэглэгчээр шүүх
     */
    public function index(Request $request)
    {
        $query = ProposedTopic::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('created_by_id')) {
            $query->where('created_by_id', $request->created_by_id);
        }

        return ProposedTopicResource::collection($query->with('fieldValues.field')->get());
    }

    /**
     * Нэвтэрсэн хэрэглэгчийн (оюутан эсвэл багшийн) үүсгэсэн сэдвүүдийг авах.
     * Хамгийн сүүлд үүсгэсэн сэдвүүд эхэнд нь байна.
     */
    public function getByUser(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['student', 'teacher'])) {
            \Log::warning('Зөвшөөрөгдөөгүй хандалтын оролдлого', [
                'user_id' => $user->id,
                'role' => $user->role,
            ]);
            return response()->json(['message' => 'Зөвшөөрөлгүй'], 403);
        }
         //TODO::SEND WITH THESSIS CYCLE INFO
        $topics = ProposedTopic::with([
            'fieldValues.field',
            'topicContent',
            'approvalLogs' ,
             'topicRequests.thesisCycle',
             'thesisCycle'
        ])
        ->where('created_by_id', $user->id)
        ->where('created_by_type', get_class($user))
        
        ->orderBy('created_at', 'desc')
        ->get();
        

        return ProposedTopicResource::collection($topics);
    }

    /**
     * Оюутнуудын үүсгэсэн бүх сэдвийг авах (админ, туслах хэрэглэгчдэд зориулав).
     */
    public function getAllApprovedTopicsByStudents(Request $request)
    {
        $user = $request->user(); 
    
        $topics = ProposedTopic::with([
            'fieldValues.field',
            'topicContent',
            'createdBy',
            'topicRequests' => function ($query) use ($user) {
                $query->where('requested_by_id', $user->id)
                      ->where('requested_by_type', get_class($user));
            }
        ])
        ->where('created_by_type', 'App\Models\Student')
        ->where('is_archived', false)
        ->whereIn('status', ['approved']) 
        ->get();
    
        return ProposedTopicResource::collection($topics);
    }
    

    /**
     * Багш нарын үүсгэсэн бүх сэдвийг авах (админ, туслах хэрэглэгчдэд зориулав).
     */
    public function getAllApprovedTopicsByTeachers(Request $request)
    {
        $user = $request->user(); 
        $topics = ProposedTopic::with([
            'fieldValues.field',
            'topicContent',
            'createdBy',
            'topicRequests' => function ($query) use ($user) {
                $query->where('requested_by_id', $user->id)
                      ->where('requested_by_type', get_class($user));
            }
        ])
        ->where('created_by_type', 'App\Models\Teacher')
        ->where('is_archived', false)
        ->whereIn('status', ['approved']) 
        ->get();

        return ProposedTopicResource::collection($topics);
    }

    public function getAllSubmittedByStudents()
    {
        $topics = ProposedTopic::with([
            'fieldValues.field',
            'topicContent',
            'approvalLogs',
             'createdBy',
            'approvalLogs' ,

        ])
        
        ->where('created_by_type', 'App\Models\Student')
        ->where('status', 'submitted')
        ->get();

        return ProposedTopicResource::collection($topics);
    }
    public function getAllSubmittedByTeachers(Request $request)
    {
        $user = $request->user(); 
    
        $topics = ProposedTopic::with([
            'fieldValues.field',
            'topicContent',
            'createdBy',
            'approvalLogs' ,

        ])
        ->where('created_by_type', 'App\Models\Teacher')
        ->where('status', 'submitted')
        ->where(function ($query) use ($user) {
            $query->where('created_by_id', '!=', $user->id)
                  ->orWhere('created_by_type', '!=', get_class($user));
        })
        ->get();
    
        return ProposedTopicResource::collection($topics);
    }
    
    public function getAllApprovedByUser(Request $request)
{
    $user = $request->user();

    $topics = ProposedTopic::whereHas('approvalLogs', function ($query) use ($user) {
        $query->where('reviewer_id', $user->id)
              ->where('reviewer_type', get_class($user))
              ->where('action', 'approved');
    })
    ->with([
        'fieldValues.field',
        'topicContent',
        'createdBy',
        'approvalLogs'
    ])
    ->get();

    return ProposedTopicResource::collection($topics);
}

    /**
     * Шинээр санал болгосон сэдэв үүсгэх.
     * - Идэвхтэй дипломын цикл байх шаардлагатай.
     * - Гарчиг, тайлбар болон нэмэлт талбаруудыг хадгална.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title_mn' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'status' => 'nullable|string|in:draft,submitted',
            'fields' => 'nullable|array',
            'fields.*.field_id' => 'required_with:fields.*|integer|exists:proposal_fields,id',
            'fields.*.value' => 'required_with:fields.*|string|max:1000',
        ]);
    
        $user = $request->user();
    
        // 1. Get active thesis cycle
        $activeCycle = ThesisCycle::where('status', 'Идэвхитэй')
            ->where('dep_id', $user->dep_id)
            ->first();
    
        if (!$activeCycle) {
            return response()->json(['message' => 'Идэвхтэй дипломын цикл олдсонгүй.'], 422);
        }
    
        // 2. Save topic content
        $content = TopicContent::create([
            'title_mn' => $validated['title_mn'],
            'title_en' => $validated['title_en'],
            'description' => $validated['description'],
        ]);
    
        // 3. Save proposed topic
        $proposedTopic = ProposedTopic::create([
            'created_by_id' => $user->id,
            'created_by_type' => $user->role === 'student' ? 'App\Models\Student' : 'App\Models\Teacher',
            'thesis_cycle_id' => $activeCycle->id,
            'topic_content_id' => $content->id,
            'status' => $validated['status'] ?? 'draft',
        ]);
    
        // 4. Save field values if provided
        if (!empty($validated['fields'])) {
            foreach ($validated['fields'] as $field) {
                ProposalFieldValue::create([
                    'proposed_topic_id' => $proposedTopic->id,
                    'field_id' => $field['field_id'],
                    'value' => $field['value'],
                ]);
            }
        }
    
        return response()->json([
            'message' => 'Сэдэв амжилттай хадгалагдлаа.',
            'topic' => $proposedTopic->load(['topicContent', 'fieldValues']),
        ], 201);
    }
    
    /**
     * Зөвхөн 'ноорог' болон 'татгалзсан' төлөвтэй сэдвийг засварлах боломжтой.
     * 'илгээсэн' болон 'батлагдсан' сэдвийг засаж болохгүй.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title_mn' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'status' => 'nullable|string|in:draft,submitted',
            'fields' => 'required|array',
            'fields.*.field_id' => 'required|integer|exists:proposal_fields,id',
            'fields.*.value' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        // 1. Сэдвийг олж шалгах
        $topic = ProposedTopic::with('topicContent', 'fieldValues')->findOrFail($id);
        if ($topic->created_by_id !== $user->id || $topic->created_by_type !== get_class($user)) {
            return response()->json(['message' => 'Зөвшөөрөлгүй'], 403);
        }

        if ($topic->status !== 'draft' && $topic->status !== 'rejected') {
            return response()->json(['message' => 'Сэдэв илгээгдсэн эсвэл батлагдсан'], 403);
        }

        // 2. Гарчиг, тайлбарыг шинэчлэх
        $topic->topicContent->update([
            'title_mn' => $validated['title_mn'],
            'title_en' => $validated['title_en'],
            'description' => $validated['description'],
        ]);

        // 3. Зөвхөн 'draft' эсвэл 'submitted' төлөвт шинэчлэхийг зөвшөөрнө
        if ($validated['status'] !== 'submitted' && $validated['status'] !== 'draft') {
            return response()->json(['message' => 'Сэдэвийг "Ноорог" эсвэл "Илгээх" төлөв рүү өөрчилж болно.'], 422);
        }

        $topic->status = $validated['status'] ?? 'draft';
        $topic->save();

        // 4. Хуучин талбаруудыг устгаж, шинэчлэх
        ProposalFieldValue::where('proposed_topic_id', $topic->id)->delete();

        foreach ($validated['fields'] as $field) {
            ProposalFieldValue::create([
                'proposed_topic_id' => $topic->id,
                'field_id' => $field['field_id'],
                'value' => $field['value'],
            ]);
        }

        return response()->json([
            'message' => 'Сэдэв амжилттай шинэчлэгдлээ.',
            'topic' => $topic->load(['topicContent', 'fieldValues.field']),
        ]);
    }

    /**
     * Сэдвийн зөвхөн төлөвийг шинэчлэх.
     * Оюутан болон багш нь 'approved' төлөв рүү шууд өөрчилж болохгүй.
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:draft,submitted,rejected',
        ]);

        $user = $request->user();
        $topic = ProposedTopic::findOrFail($id);

        // 'approved' төлөв рүү хэрэглэгч өөрөө өөрчилж болохгүй
        if (
            $topic->created_by_id === $user->id &&
            $topic->created_by_type === get_class($user) &&
            $validated['status'] === 'approved'
        ) {
            return response()->json(['message' => 'Зөвшөөрөлгүй'], 403);
        }

        $topic->status = $validated['status'];
        $topic->save();

        return response()->json([
            'message' => 'Төлөв амжилттай шинэчлэгдлээ.',
            'status' => $topic->status,
            //'get_class' => get_class($user), // Debug зориулалттай
        ]);
    }

    public function reviewTopic(Request $request, $id)
{
    $request->validate([
        'action' => 'required|in:approved,rejected',
        'comment' => 'nullable|string|max:1000',
    ]);

    if ($request->action === 'rejected' && !$request->comment) {
        return response()->json(['message' => 'Татгалзах шалтгаан заавал шаардлагатай.'], 422);
    }

    $user = $request->user();
    $topic = ProposedTopic::findOrFail($id);

    // Өөрийнхөө сэдвийг батлах/татгалзахыг хориглох
    // if (
    //     $topic->created_by_id === $user->id &&
    //     $topic->created_by_type === get_class($user)
    // ) {
    //     return response()->json([
    //         'message' => 'Та өөрийн дэвшүүлсэн сэдвийг батлах эсвэл татгалзах эрхгүй.',
    //     ], 403);
    // }

    $topic->status = $request->action;
    $topic->save();

    TopicApprovalLog::create([
        'topic_id' => $topic->id,
        'reviewer_id' => $user->id,
        'reviewer_type' => get_class($user),
        'action' => $request->action,
        'comment' => $request->comment ?? null,
        'acted_at' => now(),
    ]);

    return response()->json([
        'message' => $request->action === 'approved'
            ? 'Сэдэв зөвшөөрөгдлөө.'
            : 'Сэдэв татгалзсан.',
    ]);
}
public function archive(Request $request, $id)
{
    $topic = ProposedTopic::findOrFail($id);

    // Хандалтын эрх шалгах бол энд хийнэ
    $topic->is_archived = true;
    $topic->save();

    return response()->json(['message' => 'Сэдэв архивлагдлаа.']);
}

public function unarchive(Request $request, $id)
{
    $topic = ProposedTopic::findOrFail($id);

    // Хандалтын эрх шалгах бол энд хийнэ
    $topic->is_archived = false;
    $topic->save();

    return response()->json(['message' => 'Сэдэв архиваас гарлаа.']);
}

    
    


    /**
     * Санал болгосон сэдвийг бүрэн устгах.
     */
    public function destroy(ProposedTopic $proposedTopic)
    {
        $proposedTopic->delete();

        return response()->json(['message' => 'Сэдэв устгагдлаа.']);
    }
}
