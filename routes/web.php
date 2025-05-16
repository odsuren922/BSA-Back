<?php

use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;

use App\Http\Controllers\ProposalFormController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\TopicDetailController;
use App\Http\Controllers\TopicRequestController;
use App\Http\Controllers\TopicResponseController;
use App\Http\Controllers\Auth\OAuthController;

use App\Services\HubApiService;
use App\Http\Controllers\DataSyncController;

// OAuth Authentication Routes
Route::get('/oauth/redirect', [App\Http\Controllers\Auth\OAuthController::class, 'redirectToProvider'])->name('oauth.redirect');
Route::get('/auth', [App\Http\Controllers\Auth\OAuthController::class, 'handleProviderCallback'])->name('oauth.callback');
Route::post('/oauth/exchange-token', [App\Http\Controllers\Auth\OAuthController::class, 'exchangeToken']);
Route::post('/oauth/refresh-token', [App\Http\Controllers\Auth\OAuthController::class, 'refreshToken']);
Route::post('/logout', [App\Http\Controllers\Auth\OAuthController::class, 'logout'])->name('logout');

// Public pages
Route::get('/', function () {
    return view('welcome');
});

// Routes protected by OAuth middleware
Route::middleware(['oauth'])->group(function () {
    // Protected web pages
    Route::get('/protected-page', function () {
        $userData = session('oauth_user');
        return view('protected-page', ['userData' => $userData]);
    })->name('protected-page');
    
    // Department admin routes
    Route::prefix('department')->middleware(['role:department'])->group(function () {
        Route::get('/proposalform', [ProposalFormController::class, 'index']);
        Route::post('/proposalform', [ProposalFormController::class, 'update']);
        Route::get('/students/all', [StudentController::class, 'index']);
    });
    
    // Student routes
    Route::prefix('student')->middleware(['role:student'])->group(function () {
        Route::post('/topic/store', [TopicController::class, 'storestudent']);
        Route::post('/topic-requests', [TopicRequestController::class, 'store']);
        Route::get('/topics/draftstudent', [TopicController::class, 'getDraftTopicsByStudent']);
        Route::get('/topic_confirmed', [TopicRequestController::class, 'getConfirmedTopicOnStudent']);
        Route::get('/topics/checkedtopics', [TopicController::class, 'getCheckedTopics']);
    });
    
    // Supervisor routes
    Route::prefix('supervisor')->middleware(['role:supervisor'])->group(function () {
        Route::get('/topics/submittedby/{type}', [TopicController::class, 'getSubmittedTopicsByType']);
        Route::post('/topic-response', [TopicResponseController::class, 'store']);
        Route::get('/topics/reviewedtopicList', [TopicController::class, 'getRefusedOrApprovedTopics']);
    });
    
    // Teacher routes
    Route::prefix('teacher')->middleware(['role:teacher'])->group(function () {
        Route::post('/topic/storeteacher', [TopicController::class, 'storeteacher']);
        Route::post('/topic-requestsbyteacher', [TopicRequestController::class, 'storebyteacher']);
        Route::get('/topics/draftteacher', [TopicController::class, 'getDraftTopicsByTeacher']);
        Route::post('/topic_confirm', [TopicController::class, 'confirmTopic']);
        Route::post('/topic_decline', [TopicController::class, 'declineTopic']);
        Route::get('/topics_confirmed', [TopicRequestController::class, 'getConfirmedTopics']);
        Route::get('/topics/checkedtopicsbystud', [TopicController::class, 'getCheckedTopicsByStud']);
    });
    
    // Common routes (available to all authenticated users)
    Route::get('/topics/topiclistproposedbyuser', [TopicController::class, 'getTopicListProposedByUser']);
    Route::get('/teacher/{id}', [TeacherController::class, 'show']);
    Route::get('/department/{id}', [DepartmentController::class, 'show']);
    Route::get('/topic_requests_teacher', [TopicRequestController::class, 'getRequestedTopicByTeacher']);
});

// Routes that require a token (either OAuth or Sanctum) for API access
Route::middleware(['require.token'])->group(function () {
    
    // Data synchronization routes
    Route::get('/sync/departments', [DataSyncController::class, 'syncDepartments']);
    Route::get('/sync/teachers', [DataSyncController::class, 'syncTeachers']);
    Route::get('/sync/students', [DataSyncController::class, 'syncStudents']);
    Route::get('/sync/all', [DataSyncController::class, 'syncAll']);
    
    
    Route::get('/test-hub-api', function (HubApiService $hubApiService) {
        // Test departments
        $departments = $hubApiService->getDepartments();
        dump('Departments:', $departments);
        
        // Test teachers
        $teachers = $hubApiService->getTeachers();
        dump('Teachers:', $teachers);
        
        // Test students
        $students = $hubApiService->getStudentsInfo();
        dump('Students:', $students);
        
        return 'Check the dumped data above';
    });
});

// In routes/web.php
Route::get('/test-mongolian', function() {
    $departments = \App\Models\Department::all();
    echo "<html><head><meta charset='UTF-8'></head><body>";
    echo "<h1>Department Names</h1>";
    
    foreach ($departments as $department) {
        echo "<p><strong>ID:</strong> {$department->id}, <strong>Name:</strong> {$department->name}</p>";
        
        if (!empty($department->programs) && is_array($department->programs)) {
            echo "<ul>";
            foreach ($department->programs as $program) {
                echo "<li>{$program['name']} ({$program['name_en']})</li>";
            }
            echo "</ul>";
        }
    }
    
    echo "</body></html>";
});