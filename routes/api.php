<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProposalFormController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TopicRequestController;
use App\Http\Controllers\TopicResponseController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Api\DataSyncController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GraphQLTestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\NotificationSettingController;

use App\Http\Controllers\Thesis\ThesisController;
use App\Http\Controllers\Thesis\ThesisCycleController;
use App\Http\Controllers\Thesis\ThesisScoreController;
use App\Http\Controllers\Thesis\ThesisPlanStatusController;

use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubtaskController;

use App\Http\Controllers\Grading\GradingSchemaController;
use App\Http\Controllers\Grading\GradingComponentController;
use App\Http\Controllers\Grading\GradingCriteriaController;

use App\Http\Controllers\Committee\CommitteeController;
use App\Http\Controllers\Committee\CommitteeMemberController;
use App\Http\Controllers\Committee\CommitteeStudentController;

use App\Http\Controllers\ScheduleController;

// OAuth token exchange endpoint
Route::post('/oauth/exchange-token', [OAuthController::class, 'exchangeToken']);

// OAuth refresh token endpoint
Route::post('/oauth/refresh-token', [OAuthController::class, 'refreshToken']);

// User data endpoint (protected by bearer token)
Route::get('/user', [OAuthController::class, 'getUserData']);

// Public API routes
Route::prefix('public')->group(function () {
    // Add any routes here that should be accessible without authentication
});

Route::post('/oauth/token', [OAuthController::class, 'exchangeCodeForToken']);

// Notification routes accessible with token auth (not middleware group)
Route::middleware('auth.api.token')->group(function () {
    Route::get('/notifications/unread', [NotificationController::class, 'getUnread']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/subscribe', [NotificationController::class, 'subscribe']);
    Route::post('/notifications/unsubscribe', [NotificationController::class, 'unsubscribe']);
});

// Protected API routes
Route::middleware('auth.api.token')->group(function () {
    Route::get('/user/role', [RoleController::class, 'getUserRole']);

    // Data Sync Routes
    Route::prefix('sync')->group(function () {
        Route::post('/departments', [DataSyncController::class, 'syncDepartments']);
        Route::post('/teachers', [DataSyncController::class, 'syncTeachers']);
        Route::post('/students', [DataSyncController::class, 'syncStudents']);
        Route::post('/all', [DataSyncController::class, 'syncAll']);
    });

    // GraphQL Testing Routes
    Route::prefix('graphql-test')->group(function () {
        Route::get('/connection', [GraphQLTestController::class, 'testConnection']);
        Route::get('/departments', [GraphQLTestController::class, 'testDepartments']);
        Route::get('/teachers', [GraphQLTestController::class, 'testTeachers']);
        Route::get('/students', [GraphQLTestController::class, 'testStudents']);
    });

    Route::get('/proposalform', [ProposalFormController::class, 'index']);
    Route::post('/proposalform', [ProposalFormController::class, 'update']);
    
    // Topic related routes
    Route::post('/topic/store', [TopicController::class, 'store']);
    
    
    // Other protected routes
    Route::get('/teacher/{id}', [TeacherController::class, 'show']);
    Route::get('/department/{id}', [DepartmentController::class, 'show']);
    Route::get('/topic_requests_teacher', [TopicRequestController::class, 'getRequestedTopicByTeacher']);
    
    // Student routes
    Route::post('/topic/storestudent', [TopicController::class, 'storestudent']);
    Route::post('/topic-requests', [TopicRequestController::class, 'store']);
    Route::get('/topics/draftstudent', [TopicController::class, 'getDraftTopicsByStudent']);
    Route::get('/topics/draftteacher', [TopicController::class, 'getDraftTopicsByTeacher']);
    Route::get('/topic_confirmed', [TopicRequestController::class, 'getConfirmedTopicOnStudent']);
    
    // Supervisor routes
    Route::get('/topics/submittedby/{type}', [TopicController::class, 'getSubmittedTopicsByType']);
    Route::post('/topic-response', [TopicResponseController::class, 'store']);
    Route::get('/topics/reviewedtopicList', [TopicController::class, 'getRefusedOrApprovedTopics']);
    
    // Teacher routes
    Route::post('/topic/storeteacher', [TopicController::class, 'storeteacher']);
    Route::post('/topic-requestsbyteacher', [TopicRequestController::class, 'storebyteacher']);
    Route::get('/api/department', [DepartmentController::class, 'index']);
    Route::get('/topic_requests', [TopicRequestController::class, 'index']);
    Route::post('/topic_confirm', [TopicController::class, 'confirmTopic']);
    Route::post('/topic_decline', [TopicController::class, 'declineTopic']);
    Route::get('/topics_confirmed', [TopicRequestController::class, 'getConfirmedTopics']);
    Route::get('/topics/checkedtopicsbystud', [TopicController::class, 'getCheckedTopicsByStud']);
    Route::get('/topics/checkedtopics', [TopicController::class, 'getCheckedTopics']);
    
    // Default routes
    Route::get('/topics/topiclistproposedbyuser', [TopicController::class, 'getTopicListProposedByUser']);
    Route::apiResource('topics', TopicController::class);
    
    // Students API
    Route::get('/students/all', [StudentController::class, 'index']);

    // Other notification routes
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::post('/notifications/template', [NotificationController::class, 'sendTemplateNotification']);
    
    // Template management routes
    Route::get('/notification-templates', [NotificationTemplateController::class, 'index']);
    Route::post('/notification-templates', [NotificationTemplateController::class, 'store']);
    Route::get('/notification-templates/{id}', [NotificationTemplateController::class, 'show']);
    Route::put('/notification-templates/{id}', [NotificationTemplateController::class, 'update']);
    Route::delete('/notification-templates/{id}', [NotificationTemplateController::class, 'destroy']);
    Route::get('/notification-settings', [NotificationSettingController::class, 'index']);
    Route::post('/notification-settings', [NotificationSettingController::class, 'update']);
});



Route::middleware('auth.api.token')->group(function () {
// ------------------------------
        //Thesis Plan Tasks & Subtasks Үечилсэн төлөвлөгөө ажил & дэл ажил
        // ------------------------------
        // Route::get('/tasks', [TaskController::class, 'index']);
        Route::post('/thesis-plan/save-all', [TaskController::class, 'saveAll']);
    
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::put('/tasks/{id}', [TaskController::class, 'updateTask']);
        Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
        Route::post('/subtask', [SubtaskController::class, 'store']);
        Route::put('/subtask/{id}', [SubtaskController::class, 'updateSubTask']);
        Route::delete('/subtask/{id}', [SubtaskController::class, 'destroy']);
    
        // ------------------------------
        // Thesis Plan Status Management
        // Үечилсэн төлөвлөгөө батлах, илгээх, буцаах
        // ------------------------------
        Route::get('/thesis-plan-status/{thesis_id}', [ThesisPlanStatusController::class, 'show']);
        Route::patch('/thesis-plan-status/{thesis_id}/student-send', [ThesisPlanStatusController::class, 'studentSent']);
        Route::patch('/thesis-plan-status/{thesis_id}/student-Unsend', [ThesisPlanStatusController::class, 'studentUnSent']);
        Route::patch('/thesis-plan-status/{thesis_id}/teacher-status', [ThesisPlanStatusController::class, 'updateTeacherStatus']);
        Route::patch('/thesis-plan-status/{thesis_id}/department-status', [ThesisPlanStatusController::class, 'updateDepartmentStatus']);
    
        // ------------------------------
        // Thesis View
        // ------------------------------
    
        Route::get('/theses', [ThesisController::class, 'supervisodThesis']); //нэвтэрсэн багштай харьяатай бүр БСА
        Route::get('/thesisInfo/{id}', [ThesisController::class, 'index']); //БСА холбоотой мэдээлэл авах
        Route::get('/thesisInfoBySid/{id}', [ThesisController::class, 'thesisbyStudentId']); //БСА холбоотой мэдээлэл  student id -гааравах
    
        Route::get('/onethesis/{id}', [ThesisController::class, 'getThesis']); //БСА-н мэдээлэл авах( төлөвлөгөө, статус)
        Route::get('/thesis/{id}', [ThesisController::class, 'pdf']); //PDF ҮҮСГЭХЭД ХЭРЭГЛЭХ МЭДЭЭЛЭЛ
    
        Route::get('/cycles/{id}/theses', [ThesisController::class, 'getThesesByCycle']); //✅ ThesisCyclePage //ThesisCycle id гаар бүх БСА-н мэдээллийг багш сурагчидтай хамт авах
        Route::get('/cycles/{id}/active-theses', [ThesisController::class, 'getActiveThesesByCycle']); //getActiveThesesByCycle багш сурагчидын мэдээлэлтэй
        Route::get('/cycles/{id}/student-counts', [ThesisController::class, 'getStudentCountByProgram']);//✅ ThesisCyclePage  //Тухайн БСА ымар мэргэжлийн хэдэн хүүхэд байгааг олох
        // ------------------------------
        // Thesis Cycle
        //Тэнхмийн туслах шинэ cycle үүсгэх
        // ------------------------------
    
        Route::post('/thesis-cycles', [ThesisCycleController::class, 'store']);
        Route::get('/thesis-cycles', [ThesisCycleController::class, 'index']);//✅ ThesisCycles awah 
        Route::get('/active-cycles', [ThesisCycleController::class, 'active']);//✅ AdminDashboard 
        // Route::get('/active-cycles/dep_id={dep_id}', [ThesisCycleController::class, 'active']);
        


        Route::get('/thesis-cycles/{id}', [ThesisCycleController::class, 'show']);//✅ ThesisCyclePage 
        Route::put('/thesis-cycles/{id}', [ThesisCycleController::class, 'update']);
        Route::delete('/thesis-cycles/{id}', [ThesisCycleController::class, 'destroy']); //done
    
        // ------------------------------
        // Grading Schema & Component үнэлэх аргын нэгдэл
        // ------------------------------
        Route::post('/grading-schemas', [GradingSchemaController::class, 'store']);
        Route::get('/grading-schemas', [GradingSchemaController::class, 'index']);//✅ grading Schemas awah 
        Route::get('/thesis-cycles/{id}/grading-schema', [GradingSchemaController::class, 'showByThesisCycle']);////✅ AdminDashboard
        Route::get('/grading-schemas/{id}', [GradingSchemaController::class, 'show']);
        Route::put('/grading-schemas/{id}', [GradingSchemaController::class, 'update']);
        Route::patch('/grading-schemas/{id}', [GradingSchemaController::class, 'addComponents']);
        Route::put('/grading-one-schema/{id}', [GradingSchemaController::class, 'updateone']);
        Route::delete('/grading-schemas/{id}', [GradingSchemaController::class, 'destroy']);
        // ------------------------------
        // Grading Component Management
        // ------------------------------
        Route::post('grading-components', [GradingComponentController::class, 'store']);
        Route::get('grading-components', [GradingComponentController::class, 'index']);
        Route::get('grading-components/{id}', [GradingComponentController::class, 'show']);
        Route::put('grading-components/{id}', [GradingComponentController::class, 'update']);
        Route::delete('grading-components/{id}', [GradingComponentController::class, 'destroy']);
    
        // Grading Criteria Management
        //үнэлэх аргын дэлгэрэнгүй
        Route::post('grading-criteria', [GradingCriteriaController::class, 'store']);
        Route::get('grading-criteria', [GradingCriteriaController::class, 'index']);
        Route::get('grading-criteria/{id}', [GradingCriteriaController::class, 'show']);
        Route::put('grading-criteria/{id}', [GradingCriteriaController::class, 'update']);
        Route::delete('grading-criteria/{id}', [GradingCriteriaController::class, 'destroy']);
        // ------------------------------
        // Committees & Scheduling
        // ------------------------------
    
        Route::get('committees', [CommitteeController::class, 'index']); // Get all committees
        Route::get('/committees/active-cycle', [CommitteeController::class, 'getActiveCycleValidCommittees']); // Get all committees with active cycle
        Route::get('committees/{committee}', [CommitteeController::class, 'show']); // Get single committee
        Route::post('committees', [CommitteeController::class, 'store']); // Create committee
        Route::patch('committees/{committee}', [CommitteeController::class, 'update']);
        Route::delete('committees/{committee}', [CommitteeController::class, 'destroy']); // Delete committee
    
        Route::get('/thesis-cycles/{thesisCycle}/committees', [CommitteeController::class, 'getByThesisCycle']);

        Route::prefix('thesis-cycles/{thesisCycle}/grading-components/{gradingComponent}')->group(function () {
            Route::get('/committees', [CommitteeController::class, 'getByCycleAndComponent']);

            Route::post('/committees', [CommitteeController::class, 'storeWithCycleAndComponent']);
        });
        Route::get('/committees/by-teacher/{teacherId}', [CommitteeController::class, 'getCommitteesByTeacher']);
        // Route::get('/committees/by-student/{studentId}', [CommitteeController::class, 'getCommitteesByStudent']);
        Route::delete('/committee-members/{id}', [CommitteeMemberController::class, 'destroy']);
        Route::patch('/committee-members/{member}/role', [CommitteeMemberController::class, 'patchRole']);
    
        Route::prefix('committees/{committee}')->group(function () {
            Route::get('members', [CommitteeMemberController::class, 'index']);
            Route::post('members', [CommitteeMemberController::class, 'store']);
            Route::put('members/{member}', [CommitteeMemberController::class, 'update']);
            // Route::delete('members/{member}', [CommitteeMemberController::class, 'destroy']);
    
            // Students routes
            Route::get('students', [CommitteeStudentController::class, 'index']);
            Route::post('students', [CommitteeStudentController::class, 'store']);
            Route::put('students/{committeeStudent}', [CommitteeStudentController::class, 'update'])->scopeBindings();
            Route::delete('students/{committeeStudent}', [CommitteeStudentController::class, 'destroy'])->scopeBindings();
            
            Route::get('schedules', [ScheduleController::class, 'index']);
            Route::post('schedules', [ScheduleController::class, 'store']);
            Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy']);
            // Route::apiResource('schedules', ScheduleController::class)
            //     ->except(['show'])
            //     ->scoped(['schedule' => 'committee']);
        });
        Route::patch('schedules/{schedule}', [ScheduleController::class, 'update']);
        // ------------------------------
        // Thesis Scores
        // ------------------------------
        Route::get('/thesis/{id}/scores', [ThesisScoreController::class, 'getThesisScores']);
        Route::post('/supervisor/thesis-scores', [ThesisScoreController::class, 'storeScore']);
        Route::post('/thesis/{thesisId}/give-scores', [ThesisScoreController::class, 'storeMultipleScores']);
        Route::post('/committee-scores/bulk', [ThesisScoreController::class, 'storeBulk']);
        Route::get('/committees/{committee}/scores', [ThesisScoreController::class, 'getCommitteeStudentScores']);
        
        Route::get('/scores/{id}', [ThesisScoreController::class, 'index']);
        Route::get('/thesis-cycles/{cycleId}/grading-components/{componentId}/scores', [ThesisScoreController::class, 'getScoresByCycleAndComponent']);



});