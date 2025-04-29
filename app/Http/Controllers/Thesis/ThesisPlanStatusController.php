<?php

namespace App\Http\Controllers\Thesis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ThesisPlanStatus;

class ThesisPlanStatusController extends Controller
{
    // GET /api/thesis-plan-status/{thesis_id}
    public function show($thesis_id)
    {
        $status = ThesisPlanStatus::where('thesis_id', $thesis_id)->first();

        if (!$status) {
            return response()->json(['message' => 'Төлөвлөгөөний мэдээлэл олдсонгүй.'], 404);
        }

        return response()->json($status);
    }

    // PATCH /api/thesis-plan-status/{thesis_id}/student-send
    // Сурагч төлөвлөгөөг батлуулахаар илгээх
    public function studentSent($thesis_id)
    {
        try {
            $status = ThesisPlanStatus::updateOrCreate(
                ['thesis_id' => $thesis_id],
                [
                    'student_sent' => true,
                    'student_sent_at' => now()
                ]
            );
    
            return response()->json([
                'message' => 'Төлөвлөгөө амжилттай илгээгдлээ.',
                'data' => $status
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Төлөвлөгөөг илгээх явцад алдаа гарлаа.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function studentUnSent($thesis_id)
    {
        try {
            $status = ThesisPlanStatus::where('thesis_id', $thesis_id)->firstOrFail();
    
            //  Багш аль хэдийн зөвшөөрсөн бол буцаахыг зөвшөөрөхгүй
            if ($status->teacher_status === 'approved') {
                return response()->json([
                    'message' => 'Багш төлөвлөгөөг зөвшөөрсөн тул буцаах боломжгүй.',
                ], 403); // 403 Forbidden
            }
    
            //  Буцаах боломжтой
            $status->student_sent = false;
            $status->student_sent_at = null;
            $status->save();
    
            return response()->json([
                'message' => 'Төлөвлөгөө амжилттай буцаалаа.',
                'data' => $status
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Төлөвлөгөөг буцаах явцад алдаа гарлаа.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    

    // PATCH /api/thesis-plan-status/{thesis_id}/teacher-status
    // Багшийн статусаа шинэчлэх
    public function updateTeacherStatus(Request $request, $thesis_id)
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,returned,pending',
            ]);
    
            $status = ThesisPlanStatus::firstOrCreate(['thesis_id' => $thesis_id]);
            $status->teacher_status = $request->status;
            $status->teacher_status_updated_at = now();
    
            if ($request->status === 'returned') {
                $status->student_sent = false;
                $status->student_sent_at = null;
            }
    
            $status->save();
            
    
            return response()->json(['message' => 'Багшийн төлөв амжилттай шинэчлэгдлээ.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Багшийн төлөв шинэчлэх явцад алдаа гарлаа.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    // PATCH /api/thesis-plan-status/{thesis_id}/department-status
    // Тэнхимийн статусаа шинэчлэх
    public function updateDepartmentStatus(Request $request, $thesis_id)
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,returned,pending',
            ]);

            $status = ThesisPlanStatus::firstOrCreate(['thesis_id' => $thesis_id]);
            $status->department_status = $request->status;
            $status->department_status_updated_at = now();
            $status->save();

            return response()->json(['message' => 'Тэнхимийн төлөв амжилттай шинэчлэгдлээ.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Тэнхимийн төлөв шинэчлэх явцад алдаа гарлаа.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

