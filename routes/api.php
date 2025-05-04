<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Auth\OAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// OAuth token exchange and refresh endpoints
Route::post('/oauth/exchange-token', [OAuthController::class, 'exchangeToken']);
Route::post('/oauth/refresh-token', [OAuthController::class, 'refreshToken']);
Route::post('/oauth/token', [\App\Http\Controllers\Auth\OAuthController::class, 'exchangeCodeForToken']);
Route::get('/user', [\App\Http\Controllers\Auth\OAuthController::class, 'getUserData'])->middleware('auth:sanctum');
Route::get('/user/role', [\App\Http\Controllers\Api\RoleController::class, 'getUserRole'])->middleware('auth:sanctum');



// Thesis management API routes - Protected by auth:sanctum
Route::middleware('require.token')->group(function () {
    // User information for current authenticated user
    Route::middleware('auth:sanctum')->get('/user', [OAuthController::class, 'getUserData']);
    Route::middleware('auth:sanctum')->get('/user/role', [RoleController::class, 'getUserRole']);

    // ProposalForm routes
    Route::prefix('proposalform')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProposalFormController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\ProposalFormController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\ProposalFormController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\ProposalFormController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\ProposalFormController::class, 'destroy']);
    });
    
    // Department routes
    Route::apiResource('departments', App\Http\Controllers\DepartmentController::class);
    
    // Teacher routes
    Route::prefix('teachers')->group(function () {
        Route::get('/{id}', [\App\Http\Controllers\TeacherController::class, 'dep_id']);
        Route::get('/count/department/{dep_id}', [\App\Http\Controllers\TeacherController::class, 'countByDepartment']);
    });
    Route::get('/teacher/{id}', [\App\Http\Controllers\TeacherController::class, 'show']);
    
    // Topic routes
    Route::prefix('topics')->group(function () {
        Route::post('/storestudent', [\App\Http\Controllers\TopicController::class, 'storestudent']);
        Route::post('/storeteacher', [\App\Http\Controllers\TopicController::class, 'storeteacher']);
        Route::get('/submittedby/{type}', [\App\Http\Controllers\TopicController::class, 'getSubmittedTopicsByType']);
        Route::get('/draftstudent', [\App\Http\Controllers\TopicController::class, 'getDraftTopicsByStudent']);
        Route::get('/draftteacher', [\App\Http\Controllers\TopicController::class, 'getDraftTopicsByTeacher']);
        Route::get('/checkedtopics', [\App\Http\Controllers\TopicController::class, 'getCheckedTopics']);
        Route::get('/checkedtopicsbystud', [\App\Http\Controllers\TopicController::class, 'getCheckedTopicsByStud']);
        Route::get('/reviewedtopicList', [\App\Http\Controllers\TopicController::class, 'getRefusedOrApprovedTopics']);
        Route::get('/topiclistproposedbyuser', [\App\Http\Controllers\TopicController::class, 'getTopicListProposedByUser']);
    });
    
    // Topic Request routes
    Route::post('/topic-requests', [App\Http\Controllers\TopicRequestController::class, 'store']);
    Route::post('/topic-requestsbyteacher', [App\Http\Controllers\TopicRequestController::class, 'storebyteacher']);
    Route::get('/topic_requests', [App\Http\Controllers\TopicRequestController::class, 'index']);
    Route::get('/topic_confirmed', [App\Http\Controllers\TopicRequestController::class, 'getConfirmedTopicOnStudent']);
    Route::get('/topics_confirmed', [App\Http\Controllers\TopicRequestController::class, 'getConfirmedTopics']);
    Route::get('/topic_requests_teacher', [App\Http\Controllers\TopicRequestController::class, 'getRequestedTopicByTeacher']);
    
    // Topic Response routes
    Route::post('/topic-response', [App\Http\Controllers\TopicResponseController::class, 'store']);
    
    // Student routes
    Route::get('/students/all', [App\Http\Controllers\StudentController::class, 'index']);
    
    // Data Sync routes
    Route::prefix('sync')->group(function () {
        Route::post('/departments', [App\Http\Controllers\DataSyncController::class, 'syncDepartments']);
        Route::post('/teachers', [App\Http\Controllers\DataSyncController::class, 'syncTeachers']);
        Route::post('/students', [App\Http\Controllers\DataSyncController::class, 'syncStudents']);
        Route::post('/all', [App\Http\Controllers\DataSyncController::class, 'syncAll']);
    });
    
    // Thesis Plan and Subtasks routes
    Route::post('/thesis-plan/save-all', [App\Http\Controllers\TaskController::class, 'saveAll']);
    Route::apiResource('tasks', App\Http\Controllers\TaskController::class);
    Route::put('/tasks/{id}', [App\Http\Controllers\TaskController::class, 'updateTask']);
    Route::post('/subtask', [App\Http\Controllers\SubtaskController::class, 'store']);
    Route::put('/subtask/{id}', [App\Http\Controllers\SubtaskController::class, 'updateSubTask']);
    Route::delete('/subtask/{id}', [App\Http\Controllers\SubtaskController::class, 'destroy']);
    
    // Thesis Plan Status Management
    Route::get('/thesis-plan-status/{thesis_id}', [App\Http\Controllers\Thesis\ThesisPlanStatusController::class, 'show']);
    Route::patch('/thesis-plan-status/{thesis_id}/student-send', [App\Http\Controllers\Thesis\ThesisPlanStatusController::class, 'studentSent']);
    Route::patch('/thesis-plan-status/{thesis_id}/student-Unsend', [App\Http\Controllers\Thesis\ThesisPlanStatusController::class, 'studentUnSent']);
    Route::patch('/thesis-plan-status/{thesis_id}/teacher-status', [App\Http\Controllers\Thesis\ThesisPlanStatusController::class, 'updateTeacherStatus']);
    Route::patch('/thesis-plan-status/{thesis_id}/department-status', [App\Http\Controllers\Thesis\ThesisPlanStatusController::class, 'updateDepartmentStatus']);
    
    // Thesis routes
    Route::get('/theses', [App\Http\Controllers\Thesis\ThesisController::class, 'supervisodThesis']); 
    Route::get('/thesisInfo/{id}', [App\Http\Controllers\Thesis\ThesisController::class, 'index']); 
    Route::get('/thesisInfoBySid/{id}', [App\Http\Controllers\Thesis\ThesisController::class, 'thesisbyStudentId']);
    Route::get('/onethesis/{id}', [App\Http\Controllers\Thesis\ThesisController::class, 'getThesis']); 
    Route::get('/thesis/{id}', [App\Http\Controllers\Thesis\ThesisController::class, 'pdf']); 
    Route::get('/cycles/{id}/theses', [App\Http\Controllers\Thesis\ThesisController::class, 'getThesesByCycle']); 
    Route::get('/cycles/{id}/active-theses', [App\Http\Controllers\Thesis\ThesisController::class, 'getActiveThesesByCycle']); 
    Route::get('/cycles/{id}/student-counts', [App\Http\Controllers\Thesis\ThesisController::class, 'getStudentCountByProgram']);
    
    // Thesis Cycle routes
    Route::get('/thesis-cycles/{id}/counts', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'getTeachersAndThesisCountsByCycleId']);
    Route::apiResource('thesis-cycles', App\Http\Controllers\Thesis\ThesisCycleController::class);
    Route::get('/active-cycles', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'active']);
    
    // Grading Schema and Component Management
    Route::get('/thesis-cycles/{id}/grading-schema', [App\Http\Controllers\Grading\GradingSchemaController::class, 'showByThesisCycle']);
    Route::get('/thesis-cycles/{id}/grading-schema-filter', [App\Http\Controllers\Grading\GradingSchemaController::class, 'filteredGradingSchema']);
    Route::apiResource('grading-schemas', App\Http\Controllers\Grading\GradingSchemaController::class);
    Route::patch('/grading-schemas/{id}', [App\Http\Controllers\Grading\GradingSchemaController::class, 'addComponents']);
    Route::put('/grading-one-schema/{id}', [App\Http\Controllers\Grading\GradingSchemaController::class, 'updateone']);
    
    // Grading Component Management
    Route::apiResource('grading-components', App\Http\Controllers\Grading\GradingComponentController::class);
    
    // Committee Management
    Route::get('/committees/active-cycle', [App\Http\Controllers\Committee\CommitteeController::class, 'getActiveCycleValidCommittees']);
    Route::get('/thesis-cycles/{thesisCycle}/committees', [App\Http\Controllers\Committee\CommitteeController::class, 'getByThesisCycle']);
    Route::get('/thesis-cycles/{thesisCycle}/grading-components/{gradingComponent}/committees', 
        [App\Http\Controllers\Committee\CommitteeController::class, 'getByCycleAndComponent']);
    Route::post('/thesis-cycles/{thesisCycle}/grading-components/{gradingComponent}/committees', 
        [App\Http\Controllers\Committee\CommitteeController::class, 'storeWithCycleAndComponent']);
    Route::get('/committees/by-teacher/{teacherId}', [App\Http\Controllers\Committee\CommitteeController::class, 'getCommitteesByTeacher']);
    Route::get('/committees/{id}/members-scores', [App\Http\Controllers\Committee\CommitteeController::class, 'getCommitteeMembersWithStudentsAndScores']);
    Route::post('/committees/check-assignment', [App\Http\Controllers\Committee\CommitteeController::class, 'isTeacherAndStudentInSameCommittee']);
    Route::apiResource('committees', App\Http\Controllers\Committee\CommitteeController::class);
    
    // Committee Members routes
    Route::get('/committees/{committee}/members', [App\Http\Controllers\Committee\CommitteeMemberController::class, 'index']);
    Route::post('/committees/{committee}/members', [App\Http\Controllers\Committee\CommitteeMemberController::class, 'store']);
    Route::put('/committees/{committee}/members/{member}', [App\Http\Controllers\Committee\CommitteeMemberController::class, 'update']);
    Route::delete('/committee-members/{id}', [App\Http\Controllers\Committee\CommitteeMemberController::class, 'destroy']);
    Route::patch('/committee-members/{member}/role', [App\Http\Controllers\Committee\CommitteeMemberController::class, 'patchRole']);
    
    // Committee Students routes
    Route::get('/committees/{committee}/students', [App\Http\Controllers\Committee\CommitteeStudentController::class, 'index']);
    Route::post('/committees/{committee}/students', [App\Http\Controllers\Committee\CommitteeStudentController::class, 'store']);
    Route::put('/committees/{committee}/students/{committeeStudent}', [App\Http\Controllers\Committee\CommitteeStudentController::class, 'update']);
    Route::delete('/committees/{committee}/students/{committeeStudent}', [App\Http\Controllers\Committee\CommitteeStudentController::class, 'destroy']);
    
    // Schedule Management
    Route::get('/committees/{committee}/schedules', [App\Http\Controllers\ScheduleController::class, 'index']);
    Route::post('/committees/{committee}/schedules', [App\Http\Controllers\ScheduleController::class, 'store']);
    Route::patch('/schedules/{schedule}', [App\Http\Controllers\ScheduleController::class, 'update']);
    Route::delete('/committees/{committee}/schedules/{schedule}', [App\Http\Controllers\ScheduleController::class, 'destroy']);
    
    // Scoring routes
    Route::apiResource('scores', App\Http\Controllers\ScoreController::class);
    Route::get('/scores/getScoreByThesis/{id}', [App\Http\Controllers\ScoreController::class, 'getScoreByThesis']);
    Route::get('/scores/getDetailedScoreByThesis/{id}', [App\Http\Controllers\ScoreController::class, 'getScoreByThesisWithDetail']);
    
    // Committee Scores
    Route::apiResource('committee-scores', App\Http\Controllers\CommitteeScoreController::class);
    Route::post('/committee-scores/batch', [App\Http\Controllers\CommitteeScoreController::class, 'storeBatch']);
    Route::post('/committee-scores/finalize/{studentId}/{componentId}', [App\Http\Controllers\CommitteeScoreController::class, 'finalizeCommitteeScores']);
    Route::post('/committee-scores/batch-finalize-by-committee', [App\Http\Controllers\CommitteeScoreController::class, 'batchFinalizeByCommittee']);
    
    // Assigned Grading
    Route::get('/assigned-grading', [App\Http\Controllers\AssignedGradingController::class, 'index']);
    Route::post('/assigned-grading', [App\Http\Controllers\AssignedGradingController::class, 'store']);
    Route::post('/assigned-grading/check-assignment', [App\Http\Controllers\AssignedGradingController::class, 'checkAssignment']);
    Route::delete('/assigned-grading/{assignedGrading}', [App\Http\Controllers\AssignedGradingController::class, 'destroy']);
    Route::get('/assigned-grading/component/{componentId}/cycle/{cycleId}', [App\Http\Controllers\AssignedGradingController::class, 'getByComponentAndCycle']);
});