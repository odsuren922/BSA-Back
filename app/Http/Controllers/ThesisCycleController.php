<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ThesisCycle;
use App\Models\GradingSchema;

// TODO:: Шаардлагатай бол тэнхимийн ID-р шүүхийг нэмэх
class ThesisCycleController extends Controller
{
    /**
     * Бүх төгсөлтийн ажлын мөчлөгийг авах
     * - "Устгах" төлөвтэй мөчлөгийг алгасана
     * - gradingSchema хамааралтайгаар хамт авчирна
     */
    public function index()
    {
        return response()->json(
            ThesisCycle::with('gradingSchema')
                ->where('status', '!=', 'Устгах')
                ->get()
        );
    }

    /**
     * Одоогоор идэвхтэй байгаа мөчлөгийг авах
     * - Эхлэх огноо нь өнөөдрөөс өмнө эсвэл өнөөдөр
     * - Дуусах огноо нь өнөөдрөөс хойш эсвэл өнөөдөр
     * - Статус нь "Идэвхитэй"
     * - Мөн тухайн мөчлөгт хамаарах нийт БСА (thesis)-ийн тоог авчирна
     */
    public function active()
    {
        $activeThesis = ThesisCycle::with('gradingSchema')
            ->withCount(['theses as totalTheses' => function ($query) {
                $query->whereColumn('thesis_cycle_id', 'thesis_cycles.id');
            }])
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('status', 'Идэвхитэй')
            ->first();

        return response()->json($activeThesis);
    }

    /**
     * ID-р нь нэг мөчлөгийн мэдээллийг авах
     * - gradingSchema болон тухайн мөчлөгт хэдэн БСА байгааг авчирна
     */
    public function show($id)
    {
        return response()->json(
            ThesisCycle::with('gradingSchema')
                ->withCount(['theses as totalTheses' => function ($query) {
                    $query->whereColumn('thesis_cycle_id', 'thesis_cycles.id');
                }])
                ->findOrFail($id)
        );
    }

    /**
     * Шинэ төгсөлтийн ажлын мөчлөг үүсгэх
     * - Нэр, он, улирал, эхлэх/дуусах огноо, үнэлгээний арга шаардлагатай
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'year' => 'required|integer',
            'semester' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'grading_schema_id' => 'nullable|exists:grading_schemas,id'
        ]);

        // Хэрэв grading_schema_id өгөөгүй бол default-оор шинээр үүсгэж болно (доорх кодыг идэвхжүүлж болно)
        /*
        if (!$request->grading_schema_id) {
            $gradingSchema = GradingSchema::create([
                'year' => $request->year,
                'name' => 'Default Schema for ' . $request->name,
                'step_num' => 3,
            ]);
            $request->merge(['grading_schema_id' => $gradingSchema->id]);
        }
        */

        $thesisCycle = ThesisCycle::create($request->all());

        return response()->json($thesisCycle, 201); // 201 = Шинэ зүйл амжилттай үүссэн
    }

    /**
     * Тодорхой мөчлөгийн мэдээллийг шинэчлэх
     * TODO:: Зөвхөн админ эрхтэй хэрэглэгч шинэчилж болохоор хязгаар тавих
     */
    public function update(Request $request, $id)
    {
        $thesisCycle = ThesisCycle::findOrFail($id);
        $thesisCycle->update($request->all());
        return response()->json($thesisCycle);
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
}
