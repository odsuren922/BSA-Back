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

    // Constants for filtering
    const TARGET_DEPARTMENT_ID = 1001298; // МКУТ
    const TARGET_ACADEMIC_LEVEL = 'Бакалавр';

    public function __construct(OAuthService $oauthService = null)
    {
        $this->client = new Client([
            'verify' => config('hubapi.verify_ssl', false),
            'timeout' => 30,
        ]);

        $this->endpoint = config('hubapi.endpoint', 'https://tree.num.edu.mn/gateway');

        // Get credentials from config or hardcode for testing
        $this->clientId = config('hubapi.client_id', '4d797efc8f91416c95e641fb6f88e3c1');
        $this->clientSecret = config('hubapi.client_secret', '7c9365aff5b44ddd8f595d3ccd5969a6.5b51852d1ed248c9aab85478c8c91fc5');

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
                    'client_secret' => $this->clientSecret,
                ],
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
            // IMPORTANT: Use the raw token without "Bearer " prefix
            $headers['Authorization'] = $token;
        }

        try {
            $response = $this->client->post($this->endpoint, [
                'headers' => $headers,
                'json' => [
                    'query' => $query,
                    'variables' => $variables,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            // Check for GraphQL errors
            if (isset($data['errors'])) {
                $errorMessages = array_map(function ($error) {
                    return $error['message'];
                }, $data['errors']);

                Log::error('GraphQL errors: ' . implode(', ', $errorMessages), [
                    'query' => $query,
                    'variables' => $this->sanitizeVariables($variables),
                ]);
            }

            return $data;
        } catch (GuzzleException $e) {
            Log::error('HUB API request failed: ' . $e->getMessage(), [
                'query' => $query,
                'variables' => $this->sanitizeVariables($variables),
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
        query Sisi_GetDepartmentsInfo {
            sisi_GetDepartmentsInfo {
                departmentID
                departmentName
                departmentNamem
            }
        }
        GRAPHQL;

        $response = $this->executeQuery($query);

        if ($response && isset($response['data']['sisi_GetDepartmentsInfo'])) {
            return $response['data']['sisi_GetDepartmentsInfo'];
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
            'departmentId' => (int) $departmentId,
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
            'unitId' => $departmentId ? (int) $departmentId : null,
        ];

        $response = $this->executeQuery($query, $variables);

        if ($response && isset($response['data']['sisi_GetEmployees'])) {
            return $response['data']['sisi_GetEmployees'];
        }

        return null;
    }

    /**
     * Sync only the specific department and its bachelor programs with the database
     *
     * @return array Statistics about the sync process
     */
    public function syncDepartments()
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'filtered_out' => 0,
        ];

        try {
            // Get departments from HUB API
            $departments = $this->getDepartments();

            if (!$departments) {
                Log::error('Failed to fetch departments from HUB API');
                return $stats;
            }

            $stats['total'] = count($departments);

            // Filter to only include the target department
            $filteredDepartments = array_filter($departments, function ($dept) {
                return $dept['departmentID'] == self::TARGET_DEPARTMENT_ID;
            });

            $stats['filtered_out'] = $stats['total'] - count($filteredDepartments);

            foreach ($filteredDepartments as $deptData) {
                try {
                    // Get programs for this department
                    $programs = $this->getDepartmentPrograms($deptData['departmentID']);
                    $programData = [];

                    if ($programs) {
                        // Filter programs to include only bachelor level
                        $filteredPrograms = array_filter($programs, function ($program) {
                            return $program['academicLevel'] == self::TARGET_ACADEMIC_LEVEL;
                        });

                        foreach ($filteredPrograms as $program) {
                            $programData[] = [
                                'id' => $program['programID'],
                                'index' => $program['programIndex'],
                                'name' => $program['programNamem'],
                                'name_en' => $program['programName'],
                                'level' => $program['academicLevel'],
                            ];
                        }
                    }

                    // Find or create department
                    $department = Department::where('id', $deptData['departmentID'])->first();

                    $departmentRecord = [
                        'id' => $deptData['departmentID'],
                        'name' => $deptData['departmentNamem'],
                        'programs' => $programData,
                    ];

                    if (!$department) {
                        // Create new department
                        Department::create($departmentRecord);
                        $stats['created']++;

                        Log::info('Created department', [
                            'id' => $deptData['departmentID'],
                            'name' => $deptData['departmentNamem'],
                            'program_count' => count($programData),
                        ]);
                    } else {
                        // Update existing department
                        $department->update($departmentRecord);
                        $stats['updated']++;

                        Log::info('Updated department', [
                            'id' => $deptData['departmentID'],
                            'name' => $deptData['departmentNamem'],
                            'program_count' => count($programData),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to process department: ' . $e->getMessage(), [
                        'department_id' => $deptData['departmentID'] ?? 'unknown',
                        'department_name' => $deptData['departmentNamem'] ?? 'unknown',
                    ]);

                    $stats['failed']++;
                }
            }

            Log::info('Departments sync complete', $stats);
        } catch (\Exception $e) {
            Log::error('Department sync error: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Sync teachers data with the database, but only for specific department
     *
     * @return array Statistics about the sync process
     */
    public function syncTeachers()
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'filtered_out' => 0,
        ];

        try {
            // Only get teachers for the target department
            $departmentId = self::TARGET_DEPARTMENT_ID; // МКУТ
            $teachers = $this->getTeachers(null); // Fetch all and then filter by departmentID

            if (!$teachers) {
                Log::error('Failed to fetch teachers from HUB API');
                return $stats;
            }

            $stats['total'] = count($teachers);

            // Filter teachers by the target department
            $filteredTeachers = array_filter($teachers, function ($teacher) use ($departmentId) {
                return $teacher['departmentID'] == $departmentId;
            });

            $stats['filtered_out'] = $stats['total'] - count($filteredTeachers);

            foreach ($filteredTeachers as $teacherData) {
                try {
                    // Extract data from the nested response - taking only the first item from each array
                    $email = !empty($teacherData['emails'][0]['email']) ? $teacherData['emails'][0]['email'] : null;
                    $phone = !empty($teacherData['phones'][0]['phone']) ? $teacherData['phones'][0]['phone'] : null;
                    $degree = !empty($teacherData['degrees'][0]['degree']) ? $teacherData['degrees'][0]['degree'] : null;
                    $position = !empty($teacherData['positions'][0]['position']) ? $teacherData['positions'][0]['position'] : null;

                    // Skip if no email is available
                    if (empty($email)) {
                        Log::warning('Teacher has no email, skipping', [
                            'teacher_name' => $teacherData['firstNamem'] . ' ' . $teacherData['lastNamem'],
                            'department_id' => $teacherData['departmentID'],
                        ]);
                        $stats['failed']++;
                        continue;
                    }

                    // Generate a unique ID based on email
                    $teacherId = md5($email);

                    // Find teacher by email
                    $teacher = Teacher::where('mail', $email)->first();

                    // Create teacher record with only the required fields
                    // Specifically excluding num_of_choosed_stud, oauth_id, gid, role as requested
                    $teacherRecord = [
                        'id' => $teacherId,
                        'dep_id' => $teacherData['departmentID'],
                        'firstname' => $teacherData['firstNamem'],
                        'lastname' => $teacherData['lastNamem'],
                        'mail' => $email,
                        'degree' => $degree,
                        'superior' => $position, // Use position for superior field
                    ];

                    if (!$teacher) {
                        // Create new teacher with only the fields we want
                        Teacher::create($teacherRecord);
                        $stats['created']++;

                        Log::info('Created teacher', [
                            'id' => $teacherId,
                            'name' => $teacherData['firstNamem'] . ' ' . $teacherData['lastNamem'],
                            'email' => $email,
                        ]);
                    } else {
                        // Update only the fields we want to update
                        $teacher->update($teacherRecord);
                        $stats['updated']++;

                        Log::info('Updated teacher', [
                            'id' => $teacherId,
                            'name' => $teacherData['firstNamem'] . ' ' . $teacherData['lastNamem'],
                            'email' => $email,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to process teacher: ' . $e->getMessage(), [
                        'teacher_name' => ($teacherData['firstNamem'] ?? '') . ' ' . ($teacherData['lastNamem'] ?? ''),
                        'stack_trace' => $e->getTraceAsString(),
                    ]);

                    $stats['failed']++;
                }
            }

            Log::info('Teachers sync complete', $stats);
        } catch (\Exception $e) {
            Log::error('Teacher sync error: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
            ]);
        }

        return $stats;
    }

    /**
     * Sync all data types (departments and teachers only, with filtering)
     *
     * @return array Results for each sync operation
     */
    public function syncAll()
    {
        $results = [];

        // Sync only the specific department and its bachelor programs
        $results['departments'] = $this->syncDepartments();

        // Sync only teachers for the specific department
        $results['teachers'] = $this->syncTeachers();

        return $results;
    }
}
