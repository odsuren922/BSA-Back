<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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
    protected $verify;
    protected $cacheEnabled;
    protected $cacheTtl;

    /**
     * Constructor
     */
    public function __construct(OAuthService $oauthService = null)
    {
        $this->endpoint = config('hubapi.endpoint', 'https://tree.num.edu.mn/gateway');
        $this->clientId = config('hubapi.client_id');
        $this->clientSecret = config('hubapi.client_secret');
        $this->verify = config('hubapi.verify_ssl', false);
        $this->cacheEnabled = config('hubapi.cache_enabled', true);
        $this->cacheTtl = config('hubapi.cache_ttl', 3600);
        
        $this->client = new Client([
            'verify' => $this->verify,
            'timeout' => 30,
        ]);
        
        $this->oauthService = $oauthService;
    }

    /**
     * Authenticate with the HUB API
     *
     * @return string|null Access token or null on failure
     */
    protected function authenticate()
    {
        $cacheKey = 'hubapi_access_token';
        
        // Check if we have a cached token
        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            Log::debug('Using cached HUB API access token');
            return Cache::get($cacheKey);
        }
        
        try {
            Log::info('Authenticating with HUB API');
            
            $query = <<<'GRAPHQL'
mutation Login($input: LoginInput) {
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
                $accessToken = $response['data']['login']['access_token'];
                $expiresIn = $response['data']['login']['expires_in'] ?? 3600;
                
                Log::info('Successfully obtained HUB API access token', [
                    'expires_in' => $expiresIn
                ]);
                
                // Cache the token
                if ($this->cacheEnabled) {
                    $cacheDuration = min($expiresIn - 60, $this->cacheTtl);
                    Cache::put($cacheKey, $accessToken, $cacheDuration);
                }
                
                $this->accessToken = $accessToken;
                return $accessToken;
            }
            
            Log::error('Failed to get HUB API access token', [
                'response' => $response
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('HUB API authentication error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return null;
        }
    }

    /**
     * Execute a GraphQL query
     *
     * @param string $query The GraphQL query/mutation
     * @param array $variables Variables for the query
     * @param bool $authenticate Whether to authenticate the request
     * @return array|null The response data or null on failure
     */
    protected function executeQuery($query, $variables = [], $authenticate = true)
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];
        
        if ($authenticate) {
            if (!$this->accessToken) {
                $this->accessToken = $this->authenticate();
                
                if (!$this->accessToken) {
                    throw new \Exception("Failed to authenticate with HUB API");
                }
            }
            
            // Use raw token without "Bearer " prefix as required by the API
            $headers['Authorization'] = $this->accessToken;
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
                
                Log::error('GraphQL errors', [
                    'messages' => $errorMessages,
                    'query' => $query,
                    'variables' => $variables
                ]);
                
                throw new \Exception("GraphQL errors: " . implode(', ', $errorMessages));
            }
            
            return $data;
        } catch (GuzzleException $e) {
            Log::error('HUB API request failed: ' . $e->getMessage(), [
                'query' => $query,
                'variables' => $variables
            ]);
            
            throw new \Exception("HUB API request failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get all departments from HUB API
     *
     * @return array|null Array of departments or null on failure
     */
    public function getDepartments()
    {
        $cacheKey = 'hubapi_departments';
        
        // Check if we have cached data
        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        try {
            $query = <<<'GRAPHQL'
query Sisi_GetDepartmentsInfo {
    sisi_GetDepartmentsInfo {
        departmentID
        departmentName
        departmentNamem
    }
}
GRAPHQL;

            $response = $this->executeQuery($query);
            
            if (isset($response['data']['sisi_GetDepartmentsInfo'])) {
                $departments = $response['data']['sisi_GetDepartmentsInfo'];
                
                // Cache the data
                if ($this->cacheEnabled) {
                    Cache::put($cacheKey, $departments, $this->cacheTtl);
                }
                
                return $departments;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get departments: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all teachers from HUB API
     *
     * @param int|null $unitId Filter by unit ID
     * @return array|null Array of teachers or null on failure
     */
    public function getTeachers($unitId = null)
    {
        $unitId = $unitId ?: 1002076; // Default to MUIS, MTES unit ID if not specified
        $cacheKey = "hubapi_teachers_{$unitId}";
        
        // Check if we have cached data
        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        try {
            $query = <<<'GRAPHQL'
query Sisi_GetEmployees($unitId: Int) {
    sisi_GetEmployees(unitID: $unitId) {
        degrees {
            degree
        }
        departmentID
        departmentNamem
        firstNamem
        lastNamem
        phones {
            phone
        }
        emails {
            email
        }
        positions {
            position
        }
    }
}
GRAPHQL;

            $variables = [
                'unitId' => (int)$unitId
            ];

            $response = $this->executeQuery($query, $variables);
            
            if (isset($response['data']['sisi_GetEmployees'])) {
                $teachers = $response['data']['sisi_GetEmployees'];
                
                // Cache the data
                if ($this->cacheEnabled) {
                    Cache::put($cacheKey, $teachers, $this->cacheTtl);
                }
                
                return $teachers;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get teachers: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get students information for a specific course
     *
     * @param string $courseId The course ID (e.g., 'THES400')
     * @param int $year The academic year
     * @param int $semester The semester (1=Fall, 4=Spring)
     * @return array|null Array of students or null on failure
     */
    public function getStudentsInfo($courseId = null, $year = null, $semester = null)
    {
        $courseId = "NUM-L26404";
        $year = 2024;
        $semester = 4;
        
        $cacheKey = "hubapi_students_{$courseId}_{$year}_{$semester}";
        
        // Check if we have cached data
        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        try {
            $query = <<<'GRAPHQL'
query Sisi_GetStudentsOfLesson($courseId: String!, $year: Int!, $semester: Int!) {
    sisi_GetStudentsOfLesson(courseID: $courseId, year: $year, semester: $semester) {
        cardNr
        departmentID
        email
        fnamem
        lnamem
        phone
        programID
        programNamem
        public_hash
    }
}
GRAPHQL;

            $variables = [
                'courseId' => $courseId,
                'year' => (int)$year,
                'semester' => (int)$semester
            ];

            $response = $this->executeQuery($query, $variables);
            
            if (isset($response['data']['sisi_GetStudentsOfLesson'])) {
                $students = $response['data']['sisi_GetStudentsOfLesson'];
                
                // Cache the data
                if ($this->cacheEnabled) {
                    Cache::put($cacheKey, $students, $this->cacheTtl);
                }
                
                return $students;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get students: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get programs for a specific department
     *
     * @param int $departmentId The department ID
     * @return array|null Array of programs or null on failure
     */
    public function getPrograms($departmentId)
    {
        $cacheKey = "hubapi_programs_{$departmentId}";
        
        // Check if we have cached data
        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        try {
            $query = <<<'GRAPHQL'
query Sisi_GetPrograms($departmentId: Int) {
    sisi_GetPrograms(departmentID: $departmentId) {
        academicLevel
        programID
        programIndex
        programName
        programNamem
    }
}
GRAPHQL;

            $variables = [
                'departmentId' => (int)$departmentId
            ];

            $response = $this->executeQuery($query, $variables);
            
            if (isset($response['data']['sisi_GetPrograms'])) {
                $programs = $response['data']['sisi_GetPrograms'];
                
                // Cache the data
                if ($this->cacheEnabled) {
                    Cache::put($cacheKey, $programs, $this->cacheTtl);
                }
                
                return $programs;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get programs: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Synchronize departments from HUB API to local database
     *
     * @return array Sync statistics (total, created, updated, failed)
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
                throw new \Exception("Failed to get departments from HUB API");
            }
            
            $stats['total'] = count($departments);
            
            foreach ($departments as $deptData) {
                try {
                    // Get programs for this department
                    $programs = $this->getPrograms($deptData['departmentID']);
                    
                    // Format programs for storage
                    $formattedPrograms = [];
                    if ($programs) {
                        foreach ($programs as $program) {
                            $formattedPrograms[] = [
                                'id' => $program['programID'],
                                'index' => $program['programIndex'],
                                'name' => $program['programNamem'],
                                'name_en' => $program['programName'],
                                'level' => $program['academicLevel']
                            ];
                        }
                    }
                    
                    // Create or update department
                    $department = Department::updateOrCreate(
                        ['id' => $deptData['departmentID']],
                        [
                            'name' => $deptData['departmentNamem'],
                            'programs' => $formattedPrograms
                        ]
                    );
                    
                    if ($department->wasRecentlyCreated) {
                        $stats['created']++;
                    } else {
                        $stats['updated']++;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to sync department: ' . $e->getMessage(), [
                        'department' => $deptData['departmentID'],
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                    
                    $stats['failed']++;
                }
            }
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('Department sync error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Synchronize teachers from HUB API to local database
     *
     * @param int|null $departmentId Filter by department ID
     * @return array Sync statistics (total, created, updated, failed)
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
            // Get teachers from HUB API
            $teachers = $this->getTeachers();
            
            if (!$teachers) {
                throw new \Exception("Failed to get teachers from HUB API");
            }
            
            // Filter by department if specified
            if ($departmentId) {
                $teachers = array_filter($teachers, function($teacher) use ($departmentId) {
                    return $teacher['departmentID'] == $departmentId;
                });
            }
            
            $stats['total'] = count($teachers);
            
            foreach ($teachers as $teacherData) {
                try {
                    // Skip if no department ID
                    if (empty($teacherData['departmentID'])) {
                        $stats['failed']++;
                        continue;
                    }
                    
                    // Get email if available
                    $email = '';
                    if (!empty($teacherData['emails']) && is_array($teacherData['emails'])) {
                        foreach ($teacherData['emails'] as $emailData) {
                            if (!empty($emailData['email'])) {
                                $email = $emailData['email'];
                                break;
                            }
                        }
                    }
                    
                    // Get degree if available
                    $degree = '';
                    if (!empty($teacherData['degrees']) && is_array($teacherData['degrees'])) {
                        foreach ($teacherData['degrees'] as $degreeData) {
                            if (!empty($degreeData['degree'])) {
                                $degree = $degreeData['degree'];
                                break;
                            }
                        }
                    }
                    
                    // Get position/superior if available
                    $superior = '';
                    if (!empty($teacherData['positions']) && is_array($teacherData['positions'])) {
                        foreach ($teacherData['positions'] as $positionData) {
                            if (!empty($positionData['position'])) {
                                $superior = $positionData['position'];
                                break;
                            }
                        }
                    }
                    
                    // Generate a unique ID for the teacher (may need adjusting based on your needs)
                    $teacherId = 'T' . $teacherData['departmentID'] . '_' . str_replace(' ', '', $teacherData['firstNamem'] . $teacherData['lastNamem']);
                    
                    // Create or update teacher
                    $teacher = Teacher::updateOrCreate(
                        ['id' => $teacherId],
                        [
                            'dep_id' => $teacherData['departmentID'],
                            'firstname' => $teacherData['firstNamem'],
                            'lastname' => $teacherData['lastNamem'],
                            'mail' => $email,
                            'degree' => $degree,
                            'superior' => $superior,
                            'numof_choosed_stud' => 0,
                        ]
                    );
                    
                    if ($teacher->wasRecentlyCreated) {
                        $stats['created']++;
                    } else {
                        $stats['updated']++;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to sync teacher: ' . $e->getMessage(), [
                        'teacher' => $teacherData['firstNamem'] . ' ' . $teacherData['lastNamem'],
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                    
                    $stats['failed']++;
                }
            }
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('Teacher sync error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Synchronize students from HUB API to local database
     * Will only sync students with department ID 1001298
     *
     * @param string|null $courseId Course ID (default: THES400)
     * @param int|null $year Academic year (default: config value)
     * @param int|null $semester Semester (1=Fall, 4=Spring) (default: config value)
     * @return array Sync statistics (total, created, updated, failed)
     */
    public function syncStudents($courseId = null, $year = null, $semester = null)
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'skipped' => 0  // Added to track skipped students
        ];
        
        try {
            // Set default values if not provided
            $courseId = $courseId ?: config('hubapi.thesis_course', 'THES400');
            $year = $year ?: config('hubapi.academic_year', 2025);
            $semester = $semester ?: config('hubapi.semester', 4);
            
            // Set the specific department ID we want to filter for
            $targetDepartmentId = "1001298";
            
            Log::info('Starting student sync', [
                'course_id' => $courseId,
                'year' => $year,
                'semester' => $semester,
                'filter_department_id' => $targetDepartmentId
            ]);
            
            // Get students from HUB API
            $students = $this->getStudentsInfo($courseId, $year, $semester);
            
            if (!$students) {
                throw new \Exception("Failed to get students from HUB API");
            }
            
            $allStudentsCount = count($students);
            
            // Filter students by department ID
            $students = array_filter($students, function($student) use ($targetDepartmentId) {
                return isset($student['departmentID']) && $student['departmentID'] == $targetDepartmentId;
            });
            
            $stats['total'] = count($students);
            $stats['skipped'] = $allStudentsCount - $stats['total'];
            
            Log::info("Found {$allStudentsCount} total students, {$stats['total']} in target department, {$stats['skipped']} skipped", [
                'department_id' => $targetDepartmentId
            ]);
            
            foreach ($students as $studentData) {
                try {
                    // Validate required fields
                    if (empty($studentData['cardNr'])) {
                        Log::warning('Missing required fields for student', [
                            'student' => ($studentData['fnamem'] ?? 'Unknown') . ' ' . ($studentData['lnamem'] ?? 'Unknown'),
                            'card_nr' => $studentData['cardNr'] ?? 'missing'
                        ]);
                        $stats['failed']++;
                        continue;
                    }
                    
                    // Ensure proper data types and handle arrays
                    $studentRecord = [
                        'dep_id' => $targetDepartmentId,
                        'firstname' => strval($studentData['fnamem'] ?? ''),
                        'lastname' => strval($studentData['lnamem'] ?? ''),
                        'program' => strval($studentData['programNamem'] ?? ''),
                        'mail' => strval($studentData['email'][0] ?? ''),
                        'phone' => strval($studentData['phone'][0] ?? ''),
                        'is_choosed' => false, // Default value
                        'proposed_number' => 0, // Default value
                    ];
                    
                    // Handle optional fields that might be arrays
                    if (!empty($studentData['public_hash'])) {
                        $studentRecord['gid'] = is_array($studentData['public_hash']) 
                            ? json_encode($studentData['public_hash']) 
                            : strval($studentData['public_hash']);
                    }
                    
                    // Always set role
                    $studentRecord['role'] = 'student';
                    
                    // Create or update student
                    $student = Student::updateOrCreate(
                        ['sisi_id' => $studentData['cardNr']],
                        $studentRecord
                    );
                    
                    if ($student->wasRecentlyCreated) {
                        $stats['created']++;
                        Log::info("Created new student record", [
                            'sisi_id' => $studentData['cardNr'],
                            'name' => ($studentData['fnamem'] ?? '') . ' ' . ($studentData['lnamem'] ?? '')
                        ]);
                    } else {
                        $stats['updated']++;
                        Log::info("Updated existing student record", [
                            'sisi_id' => $studentData['cardNr'],
                            'name' => ($studentData['fnamem'] ?? '') . ' ' . ($studentData['lnamem'] ?? '')
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to sync student: ' . $e->getMessage(), [
                        'student' => ($studentData['fnamem'] ?? 'Unknown') . ' ' . ($studentData['lnamem'] ?? 'Unknown'),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'data' => $studentData,  // Log the data for debugging
                    ]);
                    
                    $stats['failed']++;
                }
            }
            
            Log::info('Student sync completed', $stats);
            return $stats;
        } catch (\Exception $e) {
            Log::error('Student sync error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
}