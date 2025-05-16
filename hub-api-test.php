<?php

/**
 * Fixed HUB API Connection Test Script
 * 
 * This script tests the connection to the NUM University HUB API
 * with correct authorization format.
 * 
 * Run this script from the command line:
 * php hub-api-test.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HubApiTester
{
    protected $client;
    protected $endpoint;
    protected $clientId;
    protected $clientSecret;
    protected $accessToken;

    /**
     * Constructor
     * 
     * @param string $endpoint HUB API endpoint URL
     * @param string $clientId OAuth client ID
     * @param string $clientSecret OAuth client secret
     */
    public function __construct($endpoint, $clientId, $clientSecret)
    {
        $this->endpoint = $endpoint;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        
        $this->client = new Client([
            'verify' => false, // Set to true in production
            'timeout' => 30,
        ]);
    }

    /**
     * Run all tests
     * 
     * @return void
     */
    public function runAllTests()
    {
        echo "Starting HUB API Connection Tests\n";
        echo "=================================\n\n";
        
        try {
            // Test 1: Authentication
            $this->testAuthentication();
            
            // Test 2: Get Departments
            $this->testGetDepartments();
            
            // Test 3: Get Units/Schools
            $this->testGetUnits();
            
            // Test 4: Get Teachers for a specific unit
            $this->testGetTeachers(1002076); // МУИС, МТЭС unit ID
            
            // Test 5: Get Programs for a specific department
            $this->testGetPrograms(1001298); // МКУТ department ID
            
            // Test 6: Get Courses
            $this->testGetCourses('NUM-P955', 1002076, 1, "Бакалаврын судалгааны ажил");
            
            // Test 7: Get Students for THES400 course
            $this->testGetStudents('NUM-L26404', 2024, 4); // Spring 2025
            
            echo "\nAll tests completed successfully!\n";
        } catch (\Exception $e) {
            echo "\nTest failed: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
        }
    }

    /**
     * Test authentication with the HUB API
     * 
     * @return void
     * @throws \Exception
     */
    public function testAuthentication()
    {
        echo "Test 1: Authentication\n";
        echo "----------------------\n";
        
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
                    'client_secret' => $this->clientSecret
                ]
            ];

            $response = $this->executeQuery($query, $variables, false);
            
            if (isset($response['data']['login']['access_token'])) {
                $this->accessToken = $response['data']['login']['access_token'];
                $expiresIn = $response['data']['login']['expires_in'] ?? 3600;
                
                echo "✓ Successfully obtained access token\n";
                echo "  Token expires in: {$expiresIn} seconds\n";
                echo "  Token: " . substr($this->accessToken, 0, 15) . "...\n\n";
                
                return;
            }
            
            throw new \Exception("Failed to get access token. Response: " . json_encode($response));
        } catch (\Exception $e) {
            echo "✗ Authentication failed: " . $e->getMessage() . "\n\n";
            throw $e;
        }
    }

    /**
     * Test fetching departments
     * 
     * @return void
     * @throws \Exception
     */
    public function testGetDepartments()
    {
        echo "Test 2: Get Departments\n";
        echo "----------------------\n";
        
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
                $count = count($departments);
                
                echo "✓ Successfully retrieved {$count} departments\n";
                
                // Display first 3 departments
                for ($i = 0; $i < min(3, $count); $i++) {
                    $dept = $departments[$i];
                    echo "  - {$dept['departmentID']}: {$dept['departmentNamem']}\n";
                }
                
                if ($count > 3) {
                    echo "  - ... and " . ($count - 3) . " more\n";
                }
                echo "\n";
                
                return;
            }
            
            throw new \Exception("Failed to get departments. Response: " . json_encode($response));
        } catch (\Exception $e) {
            echo "✗ Get Departments failed: " . $e->getMessage() . "\n\n";
            throw $e;
        }
    }

    /**
     * Test fetching units/schools
     * 
     * @return void
     * @throws \Exception
     */
    public function testGetUnits()
    {
        echo "Test 3: Get Units/Schools\n";
        echo "------------------------\n";
        
        try {
            $query = <<<'GRAPHQL'
query Sisi_GetUnitsInfo {
    sisi_GetUnitsInfo {
        abbrevm
        orgtypeID
        orgtypeNamem
        unitID
        unitNamem
    }
}
GRAPHQL;

            $response = $this->executeQuery($query);
            
            if (isset($response['data']['sisi_GetUnitsInfo'])) {
                $units = $response['data']['sisi_GetUnitsInfo'];
                $count = count($units);
                
                echo "✓ Successfully retrieved {$count} units/schools\n";
                
                // Display first 3 units
                for ($i = 0; $i < min(3, $count); $i++) {
                    $unit = $units[$i];
                    echo "  - {$unit['unitID']}: {$unit['unitNamem']} ({$unit['abbrevm']})\n";
                }
                
                if ($count > 3) {
                    echo "  - ... and " . ($count - 3) . " more\n";
                }
                echo "\n";
                
                return;
            }
            
            throw new \Exception("Failed to get units. Response: " . json_encode($response));
        } catch (\Exception $e) {
            echo "✗ Get Units failed: " . $e->getMessage() . "\n\n";
            throw $e;
        }
    }

    /**
     * Test fetching teachers for a specific unit
     * 
     * @param int $unitId
     * @return void
     * @throws \Exception
     */
    public function testGetTeachers($unitId)
    {
        echo "Test 4: Get Teachers for unit {$unitId}\n";
        echo "-----------------------------------------\n";
        
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
                $count = count($teachers);
                
                echo "✓ Successfully retrieved {$count} teachers\n";
                
                // Display first 3 teachers
                for ($i = 0; $i < min(3, $count); $i++) {
                    $teacher = $teachers[$i];
                    $email = isset($teacher['emails'][0]) ? $teacher['emails'][0]['email'] : 'no email';
                    echo "  - {$teacher['lastNamem']}\n";
                    // echo "  - {$teacher['firstNamem']} {$teacher['lastNamem']} ({$email})\n";

                }
                
                if ($count > 3) {
                    echo "  - ... and " . ($count - 3) . " more\n";
                }
                echo "\n";
                
                return;
            }
            
            throw new \Exception("Failed to get teachers. Response: " . json_encode($response));
        } catch (\Exception $e) {
            echo "✗ Get Teachers failed: " . $e->getMessage() . "\n\n";
            throw $e;
        }
    }

    /**
     * Test fetching programs for a specific department
     * 
     * @param int $departmentId
     * @return void
     * @throws \Exception
     */
    public function testGetPrograms($departmentId)
    {
        echo "Test 5: Get Programs for department {$departmentId}\n";
        echo "-------------------------------------------------\n";
        
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
                $count = count($programs);
                
                echo "✓ Successfully retrieved {$count} programs\n";
                
                // Display first 3 programs
                for ($i = 0; $i < min(3, $count); $i++) {
                    $program = $programs[$i];
                    echo "  - {$program['programID']}: {$program['programNamem']} ({$program['programIndex']})\n";
                }
                
                if ($count > 3) {
                    echo "  - ... and " . ($count - 3) . " more\n";
                }
                echo "\n";
                
                return;
            }
            
            throw new \Exception("Failed to get programs. Response: " . json_encode($response));
        } catch (\Exception $e) {
            echo "✗ Get Programs failed: " . $e->getMessage() . "\n\n";
            throw $e;
        }
    }

    /**
     * Test fetching courses
     * 
     * @param string|null $programId
     * @param int|null $unitId
     * @param int $lang
     * @param string|null $searchWord
     * @return void
     * @throws \Exception
     */
    public function testGetCourses($programId, $unitId, $lang, $searchWord)
    {
        echo "Test 6: Get Courses";
        if ($searchWord) {
            echo " matching '{$searchWord}'";
        }
        echo "\n";
        echo "------------------------------\n";
        
        try {
            $query = <<<'GRAPHQL'
query Sisi_GetCourses($lang: Int!, $unitId: Int, $programId: String, $searchWord: String) {
    sisi_GetCourses(lang: $lang, unitID: $unitId, programID: $programId, searchWord: $searchWord) {
        courseID
        courseLevel
        subjectID
        courseProgram {
            courseID
            programID
            programName
        }
        courseUnit {
            courseDepartmentID
            courseDepartmentName
            courseID
            courseSemester
        }
        credit
        subjectName
    }
}
GRAPHQL;

            $variables = [
                'lang' => $lang,
                'unitId' => 1002076,
                // 'unitId' => $unitId ? (int)$unitId : null,
                'programId' => "NUM-P1922",
                // 'programId' => $programId,
                'searchWord' => "Бакалаврын"
            ];

            $response = $this->executeQuery($query, $variables);
            
            if (isset($response['data']['sisi_GetCourses'])) {
                $courses = $response['data']['sisi_GetCourses'];
                $count = count($courses);
                
                echo "✓ Successfully retrieved {$count} courses\n";
                
                // Display first 3 courses
                for ($i = 0; $i < min(3, $count); $i++) {
                    $course = $courses[$i];
                    echo "  - {$course['subjectID']}: {$course['subjectName']} ({$course['credit']} credits)\n";
                }
                
                if ($count > 3) {
                    echo "  - ... and " . ($count - 3) . " more\n";
                }
                echo "\n";
                
                return;
            }
            
            throw new \Exception("Failed to get courses. Response: " . json_encode($response));
        } catch (\Exception $e) {
            echo "✗ Get Courses failed: " . $e->getMessage() . "\n\n";
            throw $e;
        }
    }

    /**
     * Test fetching students for a specific course
     * 
     * @param string $courseId
     * @param int $year
     * @param int $semester
     * @return void
     * @throws \Exception
     */
    public function testGetStudents($courseId, $year, $semester)
    {
        echo "Test 7: Get Students for course {$courseId} ({$year}-" . ($semester == 1 ? "Fall" : "Spring") . ")\n";
        echo "----------------------------------------------------------------\n";
        
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
                'year' => $year,
                'semester' => $semester
            ];

            $response = $this->executeQuery($query, $variables);
            
            if (isset($response['data']['sisi_GetStudentsOfLesson'])) {
                $students = $response['data']['sisi_GetStudentsOfLesson'];
                $count = count($students);
                
                if ($count > 0) {
                    echo "✓ Successfully retrieved {$count} students\n";
                    
                    // Display first 3 students
                    for ($i = 0; $i < min(3, $count); $i++) {
                        $student = $students[$i];
                        echo "  - {$student['fnamem']} {$student['lnamem']} ({$student['cardNr']}) - {$student['programNamem']}\n";
                    }
                    
                    if ($count > 3) {
                        echo "  - ... and " . ($count - 3) . " more\n";
                    }
                } else {
                    echo "✓ Query successful but no students found for this course\n";
                    echo "  This could be normal if no students are enrolled yet\n";
                }
                echo "\n";
                
                return;
            }
            
            throw new \Exception("Failed to get students. Response: " . json_encode($response));
        } catch (\Exception $e) {
            echo "✗ Get Students failed: " . $e->getMessage() . "\n\n";
            throw $e;
        }
    }

    /**
     * Execute a GraphQL query
     * 
     * @param string $query The GraphQL query/mutation
     * @param array $variables Variables for the query
     * @param bool $authenticate Whether to authenticate the request
     * @return array The response data
     * @throws \Exception
     */
    public function executeQuery($query, $variables = [], $authenticate = true)
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];
        
        if ($authenticate) {
            if (!$this->accessToken) {
                throw new \Exception("No access token available. Run authentication test first.");
            }
            // IMPORTANT FIX: Use raw token without "Bearer " prefix
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
                
                throw new \Exception("GraphQL errors: " . implode(', ', $errorMessages));
            }
            
            return $data;
        } catch (GuzzleException $e) {
            // Print the entire exception message for debugging
            throw new \Exception("API request failed: " . $e->getMessage());
        }
    }
    
    /**
     * Print the GraphQL schema
     * 
     * @return void
     */
    public function printSchema()
    {
        echo "Printing GraphQL Schema\n";
        echo "----------------------\n";
        
        try {
            // First try to get the query type name
            $query = <<<'GRAPHQL'
{
  __schema {
    queryType {
      name
    }
  }
}
GRAPHQL;

            $response = $this->executeQuery($query);
            
            if (isset($response['data']['__schema']['queryType']['name'])) {
                $queryTypeName = $response['data']['__schema']['queryType']['name'];
                echo "Query type name: {$queryTypeName}\n\n";
                
                // Now get the query type fields
                $query = <<<GRAPHQL
{
  __type(name: "{$queryTypeName}") {
    name
    fields {
      name
      args {
        name
        type {
          name
          kind
          ofType {
            name
            kind
          }
        }
      }
      type {
        name
        kind
        ofType {
          name
          kind
        }
      }
    }
  }
}
GRAPHQL;

                $response = $this->executeQuery($query);
                
                if (isset($response['data']['__type']['fields'])) {
                    $fields = $response['data']['__type']['fields'];
                    
                    echo "Available Query Fields:\n";
                    foreach ($fields as $field) {
                        if (strpos($field['name'], 'sisi_') === 0) {
                            $typeName = $field['type']['name'] ?? 
                                       ($field['type']['ofType']['name'] ?? 'unknown');
                            
                            echo "  - {$field['name']}: {$typeName}\n";
                            
                            // Print arguments
                            if (!empty($field['args'])) {
                                echo "    Arguments:\n";
                                foreach ($field['args'] as $arg) {
                                    $argTypeName = $arg['type']['name'] ?? 
                                                 ($arg['type']['ofType']['name'] ?? 'unknown');
                                    echo "      {$arg['name']}: {$argTypeName}\n";
                                }
                            }
                            echo "\n";
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            echo "✗ Print Schema failed: " . $e->getMessage() . "\n\n";
        }
    }
}

// Configuration - Replace with your actual credentials
$endpoint = "https://tree.num.edu.mn/gateway";



































$clientId = "4d797efc8f91416c95e641fb6f88e3c1";
$clientSecret = "7c9365aff5b44ddd8f595d3ccd5969a6.5b51852d1ed248c9aab85478c8c91fc5";

// Create tester instance
$tester = new HubApiTester($endpoint, $clientId, $clientSecret);

// Run all tests
$tester->runAllTests();

// Uncomment to print schema if needed
// $tester->printSchema();
