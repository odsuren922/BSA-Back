<?php

namespace App\Http\Controllers\Thesis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Score;
use App\Models\Teacher;
use App\Models\Thesis;
use App\Models\Department;
use App\Models\ThesisCycle;
use App\Models\ThesisPlanStatus;
use App\Http\Resources\ThesisResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Services\MockupApiService;


class ThesisController extends Controller
{


    //SANJAA CODE::

    protected $apiService;
    
    public function __construct(MockupApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    /**
     * Get students enrolled in thesis for a department
     */
    public function getStudentsEnrolledInThesis(Request $request, $departmentId)
    {
        $query = <<<'GRAPHQL'
        query ($departmentId: String!) {
          sisi_GetStudentsEnrolledInThesis(
            clientId: "test", 
            departmentId: $departmentId, 
            semesterId: "2025-1", 
            courseCode: "THES400"
          ) {
            sisi_id
            first_name
            last_name
            student_email
            program_name
            has_selected_research
          }
        }
        GRAPHQL;
        
        $variables = [
            'departmentId' => $departmentId
        ];
        
        $result = $this->apiService->query($query, $variables);
        
        if (!$result) {
            return response()->json(['error' => 'Failed to fetch student data'], 500);
        }
        
        return response()->json($result['sisi_GetStudentsEnrolledInThesis']);
    }
    
    /**
     * Get department information with programs
     */
    public function getDepartments()
    {
        $query = <<<'GRAPHQL'
        query {
          hr_GetDepartments(clientId: "test") {
            id
            name
            programs {
              program_id
              program_index
              program_name
            }
          }
        }
        GRAPHQL;
        
        $result = $this->apiService->query($query);
        
        if (!$result) {
            return response()->json(['error' => 'Failed to fetch department data'], 500);
        }
        
        return response()->json($result['hr_GetDepartments']);
    }
}
