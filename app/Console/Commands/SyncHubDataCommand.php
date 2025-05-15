<?php

namespace App\Console\Commands;

use App\Services\HubApiService;
use Illuminate\Console\Command;

class SyncHubDataCommand extends Command
{
    protected $signature = 'hub:sync 
                            {type? : Type of data to sync (departments, teachers, students, or all)} 
                            {--department= : Department ID to sync teachers for} 
                            {--course=THES400 : Course ID for students} 
                            {--year=2025 : Academic year} 
                            {--semester=4 : Semester (1=Fall, 4=Spring)}';
                            
    protected $description = 'Synchronize data from HUB-API to the database';

    protected $hubApiService;

    public function __construct(HubApiService $hubApiService)
    {
        parent::__construct();
        $this->hubApiService = $hubApiService;
    }

    public function handle()
    {
        $type = $this->argument('type') ?? 'all';
        $departmentId = $this->option('department');
        $courseId = $this->option('course');
        $year = (int)$this->option('year');
        $semester = (int)$this->option('semester');

        $this->info("Starting synchronization of {$type} data");
        $this->info("Parameters: course={$courseId}, year={$year}, semester={$semester}");
        
        if ($departmentId) {
            $this->info("Department filter: {$departmentId}");
        }

        $results = [];

        switch ($type) {
            case 'departments':
                $results = $this->hubApiService->syncDepartments();
                $this->displayResults('Departments', $results);
                break;
                
            case 'teachers':
                $results = $this->hubApiService->syncTeachers($departmentId);
                $this->displayResults('Teachers', $results);
                break;
                
            case 'students':
                $results = $this->hubApiService->syncStudents($courseId, $year, $semester);
                $this->displayResults('Students', $results);
                break;
                
            case 'all':
                $this->info('Syncing departments...');
                $departmentResults = $this->hubApiService->syncDepartments();
                $this->displayResults('Departments', $departmentResults);
                
                $this->info('Syncing teachers...');
                $teacherResults = $this->hubApiService->syncTeachers($departmentId);
                $this->displayResults('Teachers', $teacherResults);
                
                $this->info('Syncing students...');
                $studentResults = $this->hubApiService->syncStudents($courseId, $year, $semester);
                $this->displayResults('Students', $studentResults);
                
                $results = [
                    'departments' => $departmentResults,
                    'teachers' => $teacherResults,
                    'students' => $studentResults,
                ];
                break;
                
            default:
                $this->error("Unknown data type: {$type}");
                return 1;
        }

        $this->info('Synchronization completed successfully');
        return 0;
    }

    protected function displayResults($type, $results)
    {
        $this->info("=== {$type} Sync Results ===");
        $this->table(
            ['Total', 'Created', 'Updated', 'Failed'],
            [[
                $results['total'],
                $results['created'], 
                $results['updated'],
                $results['failed']
            ]]
        );
    }
}