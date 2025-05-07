<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Models\Student;
use App\Models\Teacher;

class HubApiService
{
    protected $client;
    protected $endpoint;
    protected $clientId;
    protected $clientSecret;
    protected $accessToken;
    protected $tokenExpiry;
    protected $oauthService;

    public function __construct(OAuthService $oauthService = null)
    {
        $this->client = new Client([
            'verify' => config('hubapi.verify_ssl', false),
            'timeout' => 30,
        ]);

        $this->endpoint = config('hubapi.endpoint', 'https://tree.num.edu.mn/gateway');
        $this->clientId = config('hubapi.client_id', 'thesis_management_system');
        $this->clientSecret = config('hubapi.client_secret');
        $this->oauthService = $oauthService ?? new OAuthService();
    }

    /**
     * Get access token for API requests using login mutation
     *
     * @return string|null
     */
    protected function getAccessToken()
    {
        // Check if we have a cached token
        $cacheKey = 'hub_api_token';
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // If not, get a new token
        try {
            $query = <<<'GRAPHQL'
mutation Login($input: LoginInput!) {
    login(input: $input) {
        access_token
        expires_in
    }
}
GRAPHQL;

            $variables = [
                'input' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret
                ]
            ];

            $response = $this->executeQuery($query, $variables, false);
            
            if (isset($response['data']['login']['access_token'])) {
                $token = $response['data']['login']['access_token'];
                $expiresIn = $response['data']['login']['expires_in'] ?? 3600;
                
                // Cache the token for slightly less than its expiration time
                Cache::put($cacheKey, $token, $expiresIn - 60);
                
                return $token;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get HUB API access token: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Execute a GraphQL query
     *
     * @param string $query The GraphQL query/mutation
     * @param array $variables Variables for the query
     * @param bool $authenticate Whether to authenticate the request
     * @return array|null The response data or null on failure
     */
    public function executeQuery($query, $variables = [], $authenticate = true)
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];
        
        if ($authenticate) {
            $token = $this->getAccessToken();
            if (!$token) {
                Log::error('No access token available for HUB API request');
                return null;
            }
            $headers['Authorization'] = 'Bearer ' . $token;
        }
        
        try {
            $response = $this->client->post($this->endpoint, [
                'headers' => $headers,
                'json' => [
                    'query' => $query,
                    'variables' => $variables
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            
            // Check for GraphQL errors
            if (isset($data['errors'])) {
                $errorMessages = array_map(function ($error) {
                    return $error['message'];
                }, $data['errors']);
                
                Log::error('GraphQL errors: ' . implode(', ', $errorMessages), [
                    'query' => $query,
                    'variables' => $this->sanitizeVariables($variables)
                ]);
            }
            
            return $data;
        } catch (GuzzleException $e) {
            Log::error('HUB API request failed: ' . $e->getMessage(), [
                'query' => $query,
                'variables' => $this->sanitizeVariables($variables)
            ]);
            
            return null;
        }
    }

    /**
     * Sanitize variables for logging (remove sensitive data)
     *
     * @param array $variables
     * @return array
     */
    protected function sanitizeVariables($variables)
    {
        $sanitized = $variables;
        
        // Remove sensitive data like client_secret
        if (isset($sanitized['input']['client_secret'])) {
            $sanitized['input']['client_secret'] = '[REDACTED]';
        }
        
        return $sanitized;
    }

    /**
     * Get departments data
     *
     * @return array|null
     */
    public function getDepartments()
    {
        $query = <<<'GRAPHQL'
query Sisi_GetUnitsInfo {
    sisi_GetUnitsInfo {
        id
        name
    }
}
GRAPHQL;

        $response = $this->executeQuery($query);
        
        if ($response && isset($response['data']['sisi_GetUnitsInfo'])) {
            return $response['data']['sisi_GetUnitsInfo'];
        }
        
        return null;
    }

    /**
     * Get department details including programs
     * 
     * @param string $departmentId
     * @return array|null
     */
    public function getDepartmentPrograms($departmentId)
    {
        $query = <<<'GRAPHQL'
query Sisi_GetPrograms($departmentId: Int!) {
    sisi_GetPrograms(departmentId: $departmentId) {
        id
        name
    }
}
GRAPHQL;

        $variables = [
            'departmentId' => (int)$departmentId
        ];

        $response = $this->executeQuery($query, $variables);
        
        if ($response && isset($response['data']['sisi_GetPrograms'])) {
            return $response['data']['sisi_GetPrograms'];
        }
        
        return null;
    }

    /**
     * Get teachers/staff data with optional department filter
     *
     * @param string|null $departmentId
     * @return array|null
     */
    public function getTeachers($departmentId = null)
    {
        $query = <<<'GRAPHQL'
query Sisi_GetEmployees($unitId: Int!) {
    sisi_GetEmployees(unitId: $unitId) {
        id
        firstname
        lastname
        mail
        superior
        degree
    }
}
GRAPHQL;

        $variables = [
            'unitId' => $departmentId ? (int)$departmentId : null
        ];

        $response = $this->executeQuery($query, $variables);
        
        if ($response && isset($response['data']['sisi_GetEmployees'])) {
            return $response['data']['sisi_GetEmployees'];
        }
        
        return null;
    }

    /**
     * Get students enrolled in the THES400 course
     *
     * @param string $courseId Course ID for THES400
     * @param int $year The academic year
     * @param int $semester Semester (1 for Fall, 4 for Spring)
     * @return array|null
     */
    public function getStudentsOfLesson($courseId = 'THES400', $year = 2025, $semester = 4)
    {
        $query = <<<'GRAPHQL'
query Sisi_GetStudentsOfLesson($courseId: String!, $year: Int!, $semester: Int!) {
    sisi_GetStudentsOfLesson(courseId: $courseId, year: $year, semester: $semester) {
        sisi_id
        firstname
        lastname
        mail
        program
        dep_id
        phone
    }
}
GRAPHQL;

        $variables = [
            'courseId' => $courseId,
            'year' => $year,
            'semester' => $semester
        ];

        $response = $this->executeQuery($query, $variables);
        
        if ($response && isset($response['data']['sisi_GetStudentsOfLesson'])) {
            return $response['data']['sisi_GetStudentsOfLesson'];
        }
        
        return null;
    }

    /**
     * Sync departments data with the database
     *
     * @return array Statistics about the sync process
     */
    public function syncDepartments()
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0
        ];

        try {
            // Get departments from HUB API
            $departments = $this->getDepartments();
            
            if (!$departments) {
                Log::error('Failed to fetch departments from HUB API');
                return $stats;
            }
            
            $stats['total'] = count($departments);
            
            foreach ($departments as $deptData) {
                try {
                    // Get programs for this department
                    $programs = $this->getDepartmentPrograms($deptData['id']);
                    $programData = [];
                    
                    if ($programs) {
                        foreach ($programs as $program) {
                            $programData[] = [
                                'id' => $program['id'],
                                'name' => $program['name'],
                            ];
                        }
                    }
                    
                    // Find or create department
                    $department = Department::where('id', $deptData['id'])->first();
                    
                    if (!$department) {
                        // Create new department
                        Department::create([
                            'id' => $deptData['id'],
                            'name' => $deptData['name'],
                            'programs' => $programData
                        ]);
                        
                        $stats['created']++;
                    } else {
                        // Update existing department
                        $department->update([
                            'name' => $deptData['name'],
                            'programs' => $programData
                        ]);
                        
                        $stats['updated']++;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to process department: ' . $e->getMessage(), [
                        'department_id' => $deptData['id'] ?? 'unknown',
                        'department_name' => $deptData['name'] ?? 'unknown'
                    ]);
                    
                    $stats['failed']++;
                }
            }
        } catch (\Exception $e) {
            Log::error('Department sync error: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Sync teachers data with the database
     *
     * @param string|null $departmentId Optional department ID to filter teachers
     * @return array Statistics about the sync process
     */
    public function syncTeachers($departmentId = null)
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0
        ];

        try {
            // Get all departments if no specific department ID is provided
            $departments = $departmentId ? [$departmentId] : array_column($this->getDepartments() ?? [], 'id');
            
            foreach ($departments as $depId) {
                // Get teachers from HUB API for this department
                $teachers = $this->getTeachers($depId);
                
                if (!$teachers) {
                    Log::error("Failed to fetch teachers for department ID: $depId");
                    continue;
                }
                
                $stats['total'] += count($teachers);
                
                foreach ($teachers as $teacherData) {
                    try {
                        // Verify we have required fields
                        if (empty($teacherData['id'])) {
                            Log::warning('Teacher missing ID', ['data' => $teacherData]);
                            $stats['failed']++;
                            continue;
                        }
                        
                        // Find or create teacher
                        $teacher = Teacher::where('id', $teacherData['id'])->first();
                        
                        $teacherRecord = [
                            'id' => $teacherData['id'],
                            'dep_id' => $depId,
                            'firstname' => $teacherData['firstname'],
                            'lastname' => $teacherData['lastname'],
                            'mail' => $teacherData['mail'],
                            'degree' => $teacherData['degree'] ?? null,
                            'superior' => $teacherData['superior'] ?? null,
                            'numof_choosed_stud' => 0
                        ];
                        
                        if (!$teacher) {
                            // Create new teacher
                            Teacher::create($teacherRecord);
                            $stats['created']++;
                        } else {
                            // Update existing teacher
                            $teacher->update($teacherRecord);
                            $stats['updated']++;
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to process teacher: ' . $e->getMessage(), [
                            'teacher_id' => $teacherData['id'] ?? 'unknown',
                            'teacher_name' => ($teacherData['firstname'] ?? '') . ' ' . ($teacherData['lastname'] ?? '')
                        ]);
                        
                        $stats['failed']++;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Teacher sync error: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Sync students data with the database
     *
     * @param string $courseId Course ID for THES400
     * @param int $year The academic year
     * @param int $semester Semester (1 for Fall, 4 for Spring)
     * @return array Statistics about the sync process
     */
    public function syncStudents($courseId = 'THES400', $year = 2025, $semester = 4)
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0
        ];

        try {
            // Get students from HUB API
            $students = $this->getStudentsOfLesson($courseId, $year, $semester);
            
            if (!$students) {
                Log::error('Failed to fetch students from HUB API');
                return $stats;
            }
            
            $stats['total'] = count($students);
            
            foreach ($students as $studentData) {
                try {
                    // Verify we have required fields
                    if (empty($studentData['sisi_id'])) {
                        Log::warning('Student missing sisi_id', ['data' => $studentData]);
                        $stats['failed']++;
                        continue;
                    }
                    
                    // Find or create student
                    $student = Student::where('sisi_id', $studentData['sisi_id'])->first();
                    
                    $studentRecord = [
                        'sisi_id' => $studentData['sisi_id'],
                        'firstname' => $studentData['firstname'],
                        'lastname' => $studentData['lastname'],
                        'program' => $studentData['program'],
                        'mail' => $studentData['mail'],
                        'phone' => $studentData['phone'] ?? '',
                        'dep_id' => $studentData['dep_id'],
                        'is_choosed' => false,
                        'proposed_number' => 0
                    ];
                    
                    if (!$student) {
                        // Create new student
                        Student::create($studentRecord);
                        $stats['created']++;
                    } else {
                        // Update existing student
                        $student->update($studentRecord);
                        $stats['updated']++;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to process student: ' . $e->getMessage(), [
                        'student_id' => $studentData['sisi_id'] ?? 'unknown',
                        'student_name' => ($studentData['firstname'] ?? '') . ' ' . ($studentData['lastname'] ?? '')
                    ]);
                    
                    $stats['failed']++;
                }
            }
        } catch (\Exception $e) {
            Log::error('Student sync error: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Sync all data types
     *
     * @return array Results for each sync operation
     */
    public function syncAll()
    {
        $results = [];
        
        // Sync departments
        $results['departments'] = $this->syncDepartments();
        
        // Sync teachers (all departments)
        $results['teachers'] = $this->syncTeachers();
        
        // Sync students
        $results['students'] = $this->syncStudents();
        
        return $results;
    }
}