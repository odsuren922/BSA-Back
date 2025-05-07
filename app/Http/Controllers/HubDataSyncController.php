<?php

namespace App\Http\Controllers;

use App\Services\HubApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HubDataSyncController extends Controller
{
    protected $hubApiService;
    
    public function __construct(HubApiService $hubApiService)
    {
        $this->hubApiService = $hubApiService;
    }
    
    /**
     * Sync departments data from HUB API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncDepartments()
    {
        try {
            Log::info('Starting department sync');
            
            $stats = $this->hubApiService->syncDepartments();
            
            Log::info('Department sync completed', $stats);
            
            return response()->json([
                'success' => true,
                'message' => 'Departments synchronized successfully',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Department sync error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sync teachers data from HUB API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncTeachers(Request $request)
    {
        try {
            $departmentId = $request->input('department_id');
            
            Log::info('Starting teacher sync', [
                'department_id' => $departmentId
            ]);
            
            $stats = $this->hubApiService->syncTeachers($departmentId);
            
            Log::info('Teacher sync completed', $stats);
            
            return response()->json([
                'success' => true,
                'message' => 'Teachers synchronized successfully',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Teacher sync error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize teachers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync students data from HUB API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncStudents(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'nullable|string',
                'year' => 'nullable|integer',
                'semester' => 'nullable|integer|in:1,4',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $courseId = $request->input('course_id', 'THES400');
            $year = $request->input('year', 2025);
            $semester = $request->input('semester', 4); // Default to Spring semester
            
            Log::info('Starting student sync', [
                'course_id' => $courseId,
                'year' => $year,
                'semester' => $semester
            ]);
            
            $stats = $this->hubApiService->syncStudents($courseId, $year, $semester);
            
            Log::info('Student sync completed', $stats);
            
            return response()->json([
                'success' => true,
                'message' => 'Students synchronized successfully',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Student sync error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize students',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sync all entities from HUB API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncAll(Request $request)
    {
        $results = [];
        $success = true;
        
        try {
            // Sync departments
            Log::info('Starting full sync - departments');
            $departmentStats = $this->hubApiService->syncDepartments();
            $results['departments'] = [
                'success' => true,
                'message' => 'Departments synchronized successfully',
                'stats' => $departmentStats
            ];
            
            // Sync teachers
            Log::info('Starting full sync - teachers');
            $departmentId = $request->input('department_id');
            $teacherStats = $this->hubApiService->syncTeachers($departmentId);
            $results['teachers'] = [
                'success' => true,
                'message' => 'Teachers synchronized successfully',
                'stats' => $teacherStats
            ];
            
            // Sync students
            Log::info('Starting full sync - students');
            $courseId = $request->input('course_id', 'THES400');
            $year = $request->input('year', 2025);
            $semester = $request->input('semester', 4);
            $studentStats = $this->hubApiService->syncStudents($courseId, $year, $semester);
            $results['students'] = [
                'success' => true,
                'message' => 'Students synchronized successfully',
                'stats' => $studentStats
            ];
            
            Log::info('Full sync completed successfully');
            
            return response()->json([
                'success' => true,
                'message' => 'All entities synchronized successfully',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Full sync error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize all entities',
                'error' => $e->getMessage(),
                'partial_results' => $results
            ], 500);
        }
    }
    
    /**
     * Test the HUB API connection
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection()
    {
        try {
            $departments = $this->hubApiService->getDepartments();
            
            if ($departments) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection to HUB API successful',
                    'data' => [
                        'department_count' => count($departments),
                        'sample' => array_slice($departments, 0, 3)
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch data from HUB API',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('HUB API connection test failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'HUB API connection test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}