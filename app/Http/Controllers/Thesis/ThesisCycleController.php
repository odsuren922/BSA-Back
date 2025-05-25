<?php

namespace App\Http\Controllers\Thesis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ThesisCycle;
use App\Models\Teacher;
use App\Models\GradingSchema;
use App\Models\Committee;
use App\Models\GradingComponent;
use App\Models\ThesisCycleDeadline;





// TODO:: Шаардлагатай бол тэнхимийн ID-р шүүхийг нэмэх
class ThesisCycleController extends Controller
{
    /**
     * Бүх төгсөлтийн ажлын мөчлөгийг авах
     * - "Устгах" төлөвтэй мөчлөгийг алгасана
     * - gradingSchema хамааралтайгаар хамт авчирна
     */


    public function index(Request $request)
    {
        $depId = $request->query('dep_id');
        return response()->json(
            ThesisCycle::with('gradingSchema', 'reminders.schedules', 'deadlines')
                ->where('status', '!=', 'Устгах')
                ->when($depId, function ($query, $depId) {
                    return $query->where('dep_id', $depId);
                })
                ->orderByRaw("
                    CASE 
                        WHEN status = 'Идэвхитэй' THEN 1
                        WHEN status = 'Хүлээгдэж буй' THEN 2
                        ELSE 3
                    END
                ")
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }
    

    public function active(Request $request)
    {
        $depId = $request->query('dep_id');
    
        $activeThesis = ThesisCycle::with('gradingSchema')
            ->withCount(['theses as totalTheses' => function ($query) {
                $query->whereColumn('thesis_cycle_id', 'thesis_cycles.id',);
            }])
         
            ->where('status', 'Идэвхитэй')
            ->when($depId, function ($query, $depId) {
                return $query->where('dep_id', $depId);
            })
            
            ->first();
    
        return response()->json($activeThesis);
    }
    


    /**
     * ID-р нь нэг мөчлөгийн мэдээллийг авах
     * - gradingSchema болон тухайн мөчлөгт хэдэн БСА байгааг авчирна
     */
    //TODO::
    public function show($id)
    {
        return response()->json(
            ThesisCycle::withCount(['theses as totalTheses' => function ($query) {
                    $query->whereColumn('thesis_cycle_id', 'thesis_cycles.id');
                }])
                ->findOrFail($id)
        );
    }
    /**
     * bsa гийн ID-р нь нэг мөчлөгийн мэдээллийг авах
     * - gradingSchema болон тухайн мөчлөгт хэдэн БСА байгааг авчирна
     */
    public function thesis_idShow($id)
    {
      $thesis = Thesis::findOrFail($id);


        return response()->json(
            ThesisCycle::with('gradingSchema')
                ->withCount(['theses as totalTheses' => function ($query) {
                    $query->whereColumn('thesis_cycle_id', 'thesis_cycles.id');
                }])
                ->findOrFail($thesis->thesisCycle->id )
        );
    }

    /**
     * Шинэ төгсөлтийн ажлын мөчлөг үүсгэх
     * - Нэр, он, улирал, эхлэх/дуусах огноо, үнэлгээний арга шаардлагатай
     * үүнд deadline-ууд орохгүй
     * - gradingSchema хамааралтайгаар хамт үүсгэнэ
     * - gradingSchema үүсгэхдээ "Төгсөлтийн ажлыг үнэлэх арна зүй" гэсэн утгатай
     */
    public function storeCycle(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'dep_id' => 'nullable|exists:departments,id',
            'year' => 'required|integer',
            'end_year' =>'required|integer',
            'semester' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'grading_schema_id' => 'nullable|exists:grading_schemas,id'
        ]);

        $thesisCycle = ThesisCycle::create($request->all());
        $thesisCycle->load('gradingSchema');

        return response()->json($thesisCycle, 201); // 201 = Шинэ зүйл амжилттай үүссэн
    }
    /**
     * Шинэ төгсөлтийн ажлын мөчлөг үүсгэх
     * - Нэр, он, улирал, эхлэх/дуусах огноо, үнэлгээний арга шаардлагатай
     *  үнэлгээний арга зүйн  deadline-ууд хадгалаж байгаа (жишээ нь: явц нэг хэднээс хэндийн хооронд явагдах)
     * - gradingSchema хамааралтайгаар хамт үүсгэнэ
     * - gradingSchema үүсгэхдээ "Төгсөлтийн ажлыг үнэлэх арна зүй" гэсэн утгатай
     */

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:100',
        'dep_id' => 'nullable|exists:departments,id',
        'year' => 'required|integer',
        'end_year' =>'required|integer',
        'semester' => 'required|string|max:20',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'status'=>'required|string|max:100',
        'grading_schema_id' => 'nullable|exists:grading_schemas,id',
        'deadlines' => 'nullable|array',
        'deadlines.*.grading_component_id' => 'required|exists:grading_components,id',
        'deadlines.*.start_date' => 'required|date',
        'deadlines.*.end_date' => 'required|date|after_or_equal:deadlines.*.start_date',
    ]);

    DB::beginTransaction();
    try {
        $thesisCycle = ThesisCycle::create($validated);

        if (!empty($validated['deadlines'])) {
            foreach ($validated['deadlines'] as $deadline) {
                ThesisCycleDeadline::create([
                    'thesis_cycle_id' => $thesisCycle->id,
                    'type' => 'grading_component', // or other type logic
                    'related_id' => $deadline['grading_component_id'],
                    'related_type' => 'App\Models\GradingComponent',
                    'title' => null, // optional
                    'description' => null, // optional
                    'start_date' => $deadline['start_date'],
                    'end_date' => $deadline['end_date'],
                ]);
            }
        }

        DB::commit();
        $thesisCycle->load('gradingSchema');
        return response()->json($thesisCycle, 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Хадгалахад алдаа гарлаа.'], 500);
    }
}

    /**
     * Тодорхой мөчлөгийн мэдээллийг шинэчлэх
     * TODO:: Зөвхөн админ эрхтэй хэрэглэгч шинэчилж болохоор хязгаар тавих
     */
    public function updateCycle(Request $request, $id)
    {
        $thesisCycle = ThesisCycle::with('gradingSchema')->findOrFail($id);
        $thesisCycle->update($request->all());
        
        // Refresh the model to get updated relationships
        $thesisCycle = $thesisCycle->fresh('gradingSchema');
        
        return response()->json($thesisCycle);
    }
    public function update(Request $request, $id)
{
    $validated = $request->validate([
        'name' => 'required|string|max:100',
        'dep_id' => 'nullable|exists:departments,id',
        'year' => 'required|integer',
        'end_year' =>'required|integer',
        'semester' => 'required|string|max:20',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'status'=>'required|string|max:100',
        'grading_schema_id' => 'nullable|exists:grading_schemas,id',
        'deadlines' => 'nullable|array',
        'deadlines.*.grading_component_id' => 'required|exists:grading_components,id',
        'deadlines.*.start_date' => 'required|date',
        'deadlines.*.end_date' => 'required|date|after_or_equal:deadlines.*.start_date',
    ]);
    //TODO::

    DB::beginTransaction();

    try {
        $thesisCycle = ThesisCycle::findOrFail($id);
        $thesisCycle->update($validated);

        // Delete old deadlines
        ThesisCycleDeadline::where('thesis_cycle_id', $thesisCycle->id)->delete();

        // Create new deadlines if provided
        if (!empty($validated['deadlines'])) {
            foreach ($validated['deadlines'] as $deadline) {
                ThesisCycleDeadline::create([
                    'thesis_cycle_id' => $thesisCycle->id,
                    'type' => 'grading_component',
                    'related_id' => $deadline['grading_component_id'],
                    'title' => null,
                    'description' => null,
                    'start_date' => $deadline['start_date'],
                    'end_date' => $deadline['end_date'],
                ]);
            }
        }

        DB::commit();

        $thesisCycle = $thesisCycle->fresh('gradingSchema');

        return response()->json($thesisCycle);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Шинэчлэх үед алдаа гарлаа.'], 500);
    }
}

    /**
     * Төгсөлтийн ажлын мөчлөгийг устгах
     * - Мөнхөд устгана (soft delete биш)
     */
    public function destroy($id)
    {
        ThesisCycle::destroy($id);
        return response()->json(['message' => 'Төгсөлтийн ажлын мөчлөг амжилттай устгагдлаа']);
    }
    public function getTeachersAndThesisCountsByCycleId($id)
    {
        $thesisCycle = ThesisCycle::findOrFail($id);
    
        // Group thesis by student program and count
        $programCounts = $thesisCycle->theses
            ->groupBy('student.program')
            ->map(function ($theses, $program) {
                return [
                    'program' => $program,
                    'student_count' => $theses->count(),
                ];
            })
            ->values();
    
        // Get teacher count from the same department
        $teacherCount = Teacher::where('dep_id', $thesisCycle->dep_id)->count();
    
        // Return both as one JSON object
        return response()->json([
            'teacher_count' => $teacherCount,
            'program_counts' => $programCounts,
        ]);
    }
    
}
