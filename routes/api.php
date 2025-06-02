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

Route::middleware('require.token')
    ->prefix('hub-sync')
    ->group(function () {
        Route::get('/test-connection', [App\Http\Controllers\HubDataSyncController::class, 'testConnection']);
        Route::post('/departments', [App\Http\Controllers\HubDataSyncController::class, 'syncDepartments']);
        Route::post('/teachers', [App\Http\Controllers\HubDataSyncController::class, 'syncTeachers']);
        Route::post('/students', [App\Http\Controllers\HubDataSyncController::class, 'syncStudents']);
        Route::post('/all', [App\Http\Controllers\HubDataSyncController::class, 'syncAll']);
    });

Route::middleware('require.token')->group(function () {});

// Thesis management API routes - Protected by auth:sanctum
Route::middleware('require.token')->group(function () {
    // User information for current authenticated user
    Route::middleware('auth:sanctum')->get('/user', [OAuthController::class, 'getUserData']);
    Route::middleware('auth:sanctum')->get('/user/role', [RoleController::class, 'getUserRole']);

    // Email Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index']);
        Route::post('/', [App\Http\Controllers\NotificationController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\NotificationController::class, 'show']);
        Route::post('/{id}/send', [App\Http\Controllers\NotificationController::class, 'send']);
        Route::post('/{id}/cancel', [App\Http\Controllers\NotificationController::class, 'cancel']);
    });

    // Tracking pixel route (no authentication required)
    Route::get('/notification-track/{recipient}', [App\Http\Controllers\NotificationController::class, 'track'])->name('notification.track');

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
    Route::post('/topic/storeteacher', [\App\Http\Controllers\TopicController::class, 'storeteacher']);
    Route::post('/topic/storestudent', [\App\Http\Controllers\TopicController::class, 'storestudent']);

    Route::prefix('topics')->group(function () {
        // Route::post('/storestudent', [\App\Http\Controllers\TopicController::class, 'storestudent']);
        // Route::post('/storeteacher', [\App\Http\Controllers\TopicController::class, 'storeteacher']);
        Route::get('/submittedby/{type}', [\App\Http\Controllers\TopicController::class, 'getSubmittedTopicsByType']);
        Route::get('/draftstudent', [\App\Http\Controllers\TopicController::class, 'getDraftTopicsByStudent']);
        Route::get('/draftteacher', [\App\Http\Controllers\TopicController::class, 'getDraftTopicsByTeacher']);
        Route::get('/checkedtopics', [\App\Http\Controllers\TopicController::class, 'getCheckedTopics']);
        Route::get('/checkedtopicsbystud', [\App\Http\Controllers\TopicController::class, 'getCheckedTopicsByStud']);
        Route::get('/reviewedtopicList', [\App\Http\Controllers\TopicController::class, 'getRefusedOrApprovedTopics']);
        Route::get('/topiclistproposedbyuser', [\App\Http\Controllers\TopicController::class, 'getTopicListProposedByUser']);
    });

    // Topic Request routes
    Route::post('/topic-requests', [App\Http\Controllers\TopicRequestController::class, 'store']); //Student sendt the request
    Route::post('/topic-requestsbyteacher', [App\Http\Controllers\TopicRequestController::class, 'storebyteacher']);
    Route::get('/topic_requests', [App\Http\Controllers\TopicRequestController::class, 'index']);
    Route::get('/topic_confirmed', [App\Http\Controllers\TopicRequestController::class, 'getConfirmedTopicOnStudent']);
    Route::get('/topics_confirmed', [App\Http\Controllers\TopicRequestController::class, 'getConfirmedTopics']);
    Route::get('/topic_requests_teacher', [App\Http\Controllers\TopicRequestController::class, 'getRequestedTopicByTeacher']);

    // Topic Response routes
    Route::post('/topic-response', [App\Http\Controllers\TopicResponseController::class, 'store']);

    // Student routes
    Route::get('/students/all', [App\Http\Controllers\StudentController::class, 'index']);

    // ------------------------------
    //Thesis Plan Tasks & Subtasks Үечилсэн төлөвлөгөө ажил & дэл ажил
    // ------------------------------
    // Route::get('/tasks', [App\Http\Controllers\TaskController::class, 'index']);
    Route::post('/thesis-plan/save-all', [App\Http\Controllers\TaskController::class, 'saveAll']);

    Route::post('/tasks', [App\Http\Controllers\TaskController::class, 'store']);
    Route::put('/tasks/{id}', [App\Http\Controllers\TaskController::class, 'updateTask']);
    Route::delete('/tasks/{id}', [App\Http\Controllers\TaskController::class, 'destroy']);
    Route::post('/subtask', [App\Http\Controllers\SubtaskController::class, 'store']);
    Route::put('/subtask/{id}', [App\Http\Controllers\SubtaskController::class, 'updateSubTask']);
    Route::delete('/subtask/{id}', [App\Http\Controllers\SubtaskController::class, 'destroy']);

    // ------------------------------
    // Thesis Plan Status Management
    // Үечилсэн төлөвлөгөө батлах, илгээх, буцаах
    // ------------------------------
    Route::get('/thesis-plan-status/{thesis_id}', [App\Http\Controllers\Thesis\ThesisPlanStatusController::class, 'show']);
    Route::patch('/thesis-plan-status/{thesis_id}/student-send', [App\Http\Controllers\Thesis\ThesisPlanStatusController::class, 'studentSent']);
    Route::patch('/thesis-plan-status/{thesis_id}/student-Unsend', [App\Http\Controllers\Thesis\ThesisPlanStatusController::class, 'studentUnSent']);
    Route::patch('/thesis-plan-status/{thesis_id}/teacher-status', [App\Http\Controllers\Thesis\ThesisPlanStatusController::class, 'updateTeacherStatus']);
    Route::patch('/thesis-plan-status/{thesis_id}/department-status', [App\Http\Controllers\Thesis\ThesisPlanStatusController::class, 'updateDepartmentStatus']);

    // ------------------------------
    // Thesis View
    // ------------------------------

    Route::get('/theses', [App\Http\Controllers\Thesis\ThesisController::class, 'supervisodThesis']); //нэвтэрсэн багштай харьяатай бүр БСА
    Route::get('/thesisInfo/{id}', [App\Http\Controllers\Thesis\ThesisController::class, 'index']); //БСА холбоотой мэдээлэл авах
    Route::get('/thesisInfoBySid', [App\Http\Controllers\Thesis\ThesisController::class, 'thesisbyStudentId']); //БСА холбоотой мэдээлэл  student id -гааравах

    Route::get('/onethesis/{id}', [App\Http\Controllers\Thesis\ThesisController::class, 'getThesis']); //БСА-н мэдээлэл авах( төлөвлөгөө, статус)
    Route::get('/thesis/{id}', [App\Http\Controllers\Thesis\ThesisController::class, 'pdf']); //PDF ҮҮСГЭХЭД ХЭРЭГЛЭХ МЭДЭЭЛЭЛ

    Route::get('/cycles/{id}/theses', [App\Http\Controllers\Thesis\ThesisController::class, 'getThesesByCycle']); //✅ ThesisCyclePage //ThesisCycle id гаар бүх БСА-н мэдээллийг багш сурагчидтай хамт авах
    Route::get('/cycles/{id}/active-theses', [App\Http\Controllers\Thesis\ThesisController::class, 'getActiveThesesByCycle']); //getActiveThesesByCycle багш сурагчидын мэдээлэлтэй
    Route::get('/cycles/{id}/student-counts', [App\Http\Controllers\Thesis\ThesisController::class, 'getStudentCountByProgram']); //✅ ThesisCyclePage  //Тухайн БСА ымар мэргэжлийн хэдэн хүүхэд байгааг олох
    // ------------------------------
    // Thesis Cycle
    //Тэнхмийн туслах шинэ cycle үүсгэх
    // ------------------------------
    // getTeachersAndThesisCountsByCycleId
    Route::get('/thesis-cycles/{id}/counts', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'getTeachersAndThesisCountsByCycleId']);
    Route::post('/thesis-cycles', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'store']);
    Route::post('/only-thesis-cycles', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'storeCycle']);
    Route::get('/thesis-cycles', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'index']); //✅ ThesisCycles awah
    Route::get('/active-cycles', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'active']); //✅ AdminDashboard
    // Route::get('/active-cycles/dep_id={dep_id}', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'active']);
    Route::get('/thesis-cycles/department/{dep_id}', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'getByDepartment']);

    Route::get('/thesis-cycles/{id}', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'show']); //✅ ThesisCyclePage
    Route::put('/thesis-cycles/{id}', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'update']);
    Route::put('/only-thesis-cycles/{id}', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'updateCycle']);

    Route::delete('/thesis-cycles/{id}', [App\Http\Controllers\Thesis\ThesisCycleController::class, 'destroy']); //done

    // ------------------------------
    // Grading Schema & Component үнэлэх аргын нэгдэл
    // ------------------------------
    Route::post('/grading-schemas', [App\Http\Controllers\Grading\GradingSchemaController::class, 'store']);
    Route::get('/grading-schemas', [App\Http\Controllers\Grading\GradingSchemaController::class, 'index']); //✅ grading Schemas awah
    Route::get('/thesis-cycles/{id}/grading-schema', [App\Http\Controllers\Grading\GradingSchemaController::class, 'showByThesisCycle']); ////✅ AdminDashboard
    Route::get('/thesis-cycles/{id}/grading-schema-filter', [App\Http\Controllers\Grading\GradingSchemaController::class, 'filteredGradingSchema']); ////✅ AdminDashboard

    Route::get('/grading-schemas/{id}', [App\Http\Controllers\Grading\GradingSchemaController::class, 'show']);
    Route::put('/grading-schemas/{id}', [App\Http\Controllers\Grading\GradingSchemaController::class, 'update']);
    Route::patch('/grading-schemas/{id}', [App\Http\Controllers\Grading\GradingSchemaController::class, 'addComponents']);
    Route::put('/grading-one-schema/{id}', [App\Http\Controllers\Grading\GradingSchemaController::class, 'updateone']);
    Route::delete('/grading-schemas/{id}', [App\Http\Controllers\Grading\GradingSchemaController::class, 'destroy']);
    // ------------------------------
    // Grading Component Management
    // ------------------------------
    Route::post('grading-components', [App\Http\Controllers\Grading\GradingComponentController::class, 'store']);
    Route::get('grading-components', [App\Http\Controllers\Grading\GradingComponentController::class, 'index']);
    Route::get('grading-components/{id}', [App\Http\Controllers\Grading\GradingComponentController::class, 'show']);
    Route::put('grading-components/{id}', [App\Http\Controllers\Grading\GradingComponentController::class, 'update']);
    Route::delete('grading-components/{id}', [App\Http\Controllers\Grading\GradingComponentController::class, 'destroy']);

    // Grading Criteria Management
    //үнэлэх аргын дэлгэрэнгүй
    // Route::post('grading-criteria', [App\Http\Controllers\Grading\GradingCriteriaController::class, 'store']);
    // Route::get('grading-criteria', [App\Http\Controllers\Grading\GradingCriteriaController::class, 'index']);
    // Route::get('grading-criteria/{id}', [App\Http\Controllers\Grading\GradingCriteriaController::class, 'show']);
    // Route::put('grading-criteria/{id}', [App\Http\Controllers\Grading\GradingCriteriaController::class, 'update']);
    // Route::delete('grading-criteria/{id}', [App\Http\Controllers\Grading\GradingCriteriaController::class, 'destroy']);
    // ------------------------------
    // Committees & Scheduling
    // ------------------------------

    Route::get('committees', [App\Http\Controllers\Committee\CommitteeController::class, 'index']); // Get all committees
    Route::get('/committees/active-cycle', [App\Http\Controllers\Committee\CommitteeController::class, 'getActiveCycleValidCommittees']); // Get all committees with active cycle
    Route::get('committees/{committee}', [App\Http\Controllers\Committee\CommitteeController::class, 'show']); // Get single committee
    Route::post('committees', [App\Http\Controllers\Committee\CommitteeController::class, 'store']); // Create committee
    Route::patch('committees/{committee}', [App\Http\Controllers\Committee\CommitteeController::class, 'update']);
    Route::delete('committees/{committee}', [App\Http\Controllers\Committee\CommitteeController::class, 'destroy']); // Delete committee

    Route::get('/thesis-cycles/{thesisCycle}/committees', [App\Http\Controllers\Committee\CommitteeController::class, 'getByThesisCycle']);

    Route::prefix('thesis-cycles/{thesisCycle}/grading-components/{gradingComponent}')->group(function () {
        Route::get('/committees', [App\Http\Controllers\Committee\CommitteeController::class, 'getByCycleAndComponent']);

        Route::post('/committees', [App\Http\Controllers\Committee\CommitteeController::class, 'storeWithCycleAndComponent']);
    });

    Route::get('/committees/by-teacher', [App\Http\Controllers\Committee\CommitteeController::class, 'getCommitteesByTeacher']);
    // Route::get('/committees/by-student/{studentId}', [App\Http\Controllers\Committee\CommitteeController::class, 'getCommitteesByStudent']);
    Route::delete('/committee-members/{id}', [App\Http\Controllers\Committee\CommitteeMemberController::class, 'destroy']);
    Route::patch('/committee-members/{member}/role', [App\Http\Controllers\Committee\CommitteeMemberController::class, 'patchRole']);

    Route::prefix('committees/{committee}')->group(function () {
        Route::get('members', [App\Http\Controllers\Committee\CommitteeMemberController::class, 'index']);
        Route::post('members', [App\Http\Controllers\Committee\CommitteeMemberController::class, 'store']);
        Route::put('members/{member}', [App\Http\Controllers\Committee\CommitteeMemberController::class, 'update']);
        // Route::delete('members/{member}', [App\Http\Controllers\Committee\CommitteeMemberController::class, 'destroy']);

        // Students routes
        Route::get('students', [App\Http\Controllers\Committee\CommitteeStudentController::class, 'index']);
        Route::post('students', [App\Http\Controllers\Committee\CommitteeStudentController::class, 'store']);
        Route::put('students/{committeeStudent}', [App\Http\Controllers\Committee\CommitteeStudentController::class, 'update'])->scopeBindings();
        Route::delete('students/{committeeStudent}', [App\Http\Controllers\Committee\CommitteeStudentController::class, 'destroy'])->scopeBindings();

        Route::get('schedules', [App\Http\Controllers\ScheduleController::class, 'index']);
        Route::post('schedules', [App\Http\Controllers\ScheduleController::class, 'store']);
        Route::delete('schedules/{schedule}', [App\Http\Controllers\ScheduleController::class, 'destroy']);
        // Route::apiResource('schedules', App\Http\Controllers\ScheduleController::class)
        //     ->except(['show'])
        //     ->scoped(['schedule' => 'committee']);
    });
    Route::patch('schedules/{schedule}', [App\Http\Controllers\ScheduleController::class, 'update']);
    // ------------------------------
    // Thesis Scores
    // ------------------------------

    Route::apiResource('scores', App\Http\Controllers\ScoreController::class);
    Route::get('/scores/getScoreByThesis/{id}', [App\Http\Controllers\ScoreController::class, 'getScoreByThesis']);
    Route::get('/scores/getDetailedScoreByThesis/{id}', [App\Http\Controllers\ScoreController::class, 'getScoreByThesisWithDetail']);
    Route::apiResource('committee-scores', App\Http\Controllers\CommitteeScoreController::class);

    //SAVES SCORES
    Route::post('/committee-scores/batch', [App\Http\Controllers\CommitteeScoreController::class, 'storeBatch']);
    //CHECK ALL MEMBER GIVES SCORES TO A STUDENT
    Route::post('/committee-scores/finalize/{studentId}/{componentId}', [App\Http\Controllers\CommitteeScoreController::class, 'finalizeCommitteeScores']);
    Route::post('/committee-scores/batch-finalize-by-committee', [App\Http\Controllers\CommitteeScoreController::class, 'batchFinalizeByCommittee']);

    Route::post('/committees/check-assignment', [App\Http\Controllers\Committee\CommitteeController::class, 'isTeacherAndStudentInSameCommittee']);

    Route::prefix('assigned-grading')->group(function () {
        Route::get('/', [App\Http\Controllers\AssignedGradingController::class, 'index']); // list all
        Route::get('/teacher', [App\Http\Controllers\AssignedGradingController::class, 'getByAssignedByUser']);
        // list all
        Route::post('/', [App\Http\Controllers\AssignedGradingController::class, 'store']); // store multiple
        Route::post('/check-assignment', [App\Http\Controllers\AssignedGradingController::class, 'checkAssignment']); // permission check
        Route::delete('/{assignedGrading}', [App\Http\Controllers\AssignedGradingController::class, 'destroy']); // delete
    });
    // Route::get('/assigned-grading/teacher', [App\Http\Controllers\AssignedGradingController::class, 'getByAssignedByUser']);
    Route::get('/assigned-grading/score', [App\Http\Controllers\AssignedGradingController::class, 'getScoreByAssignedTeacherByUser']);
    Route::get('/grading-assignments', [App\Http\Controllers\AssignedGradingController::class, 'getGradingAssignments']);

    Route::get('/assigned-grading/component/{componentId}/cycle/{cycleId}', [App\Http\Controllers\AssignedGradingController::class, 'getByComponentAndCycle']);
    Route::get('thesis-cycles/{thesis_cycle_id}/grading-components/{component_id}/scores', [App\Http\Controllers\ScoreController::class, 'getScoresByComponentAndCycle']);
    Route::get('/committees/{id}/members-scores', [App\Http\Controllers\Committee\CommitteeController::class, 'getCommitteeMembersWithStudentsAndScores']);

    Route::apiResource('committee-scores', App\Http\Controllers\CommitteeScoreController::class);

    //Deadlines
    Route::post('/thesiscycle/component/deadline', [App\Http\Controllers\ThesisCycleDeadlineController::class, 'storeOrUpdate']);

    //Reminder

    Route::post('/thesiscycle/reminder/save', [App\Http\Controllers\ReminderController::class, 'store']);
    Route::get('/reminders', [App\Http\Controllers\ReminderController::class, 'index']); // optional
    Route::get('/thesiscycle/{id}/reminders', [App\Http\Controllers\ReminderController::class, 'getByCycle']);
    Route::patch('/thesiscycle/reminder/{reminder}', [App\Http\Controllers\ReminderController::class, 'update']);
    Route::delete('/thesiscycle/reminder/{reminder}', [App\Http\Controllers\ReminderController::class, 'destroy']);

    Route::apiResource('external-reviewers', App\Http\Controllers\ExternalReviewerController::class);
    Route::apiResource('external-reviewer-scores', App\Http\Controllers\ExternalReviewerScoreController::class);
    Route::post('/external-reviewer-scores/batch', [App\Http\Controllers\ExternalReviewerScoreController::class, 'storeBatch']);
    Route::post('/committee/external-reviewer-scores/batch', [App\Http\Controllers\ExternalReviewerScoreController::class, 'storeBatch2']);
    Route::post('/committee-scores/save-editable-scores', [App\Http\Controllers\CommitteeScoreController::class, 'saveEditableScores']);

    Route::get('/cycle-deadlines/by-schema', [App\Http\Controllers\ThesisCycleDeadlineController::class, 'getBySchema']);
    Route::get('/cycle-deadlines/active-schema', [App\Http\Controllers\ThesisCycleDeadlineController::class, 'getActiveCycleBySchema']);

    //ProposedTopicController
    //
    Route::get('proposed-topics/byUser', [App\Http\Controllers\Proposal\ProposedTopicController::class, 'getByUser']);
    //supervisor
    Route::get('proposed-topics/by-students/approved', [App\Http\Controllers\Proposal\ProposedTopicController::class, 'getAllApprovedTopicsByStudents']);
    Route::get('proposed-topics/by-teachers/approved', [App\Http\Controllers\Proposal\ProposedTopicController::class, 'getAllApprovedTopicsByTeachers']);

    Route::get('proposed-topics/by-students/submitted', [App\Http\Controllers\Proposal\ProposedTopicController::class, 'getAllSubmittedByStudents']);
    Route::get('proposed-topics/by-teachers/submitted', [App\Http\Controllers\Proposal\ProposedTopicController::class, 'getAllSubmittedByTeachers']);
    Route::get('/proposed-topics/approved-by-user', [App\Http\Controllers\Proposal\ProposedTopicController::class, 'getAllApprovedByUser']);

    Route::put('/proposed-topics/{id}/status', [App\Http\Controllers\Proposal\ProposedTopicController::class, 'updateStatus']);

    Route::post('proposed-topics/{id}/update', [App\Http\Controllers\Proposal\ProposedTopicController::class, 'update']);
    Route::post('/proposed-topics/{id}/review', [App\Http\Controllers\Proposal\ProposedTopicController::class, 'reviewTopic']);//only for supervisor permisssion person

    Route::apiResource('proposed-topics', App\Http\Controllers\Proposal\ProposedTopicController::class);
    Route::put('proposed-topics/{id}/archive', [ App\Http\Controllers\Proposal\ProposedTopicController::class, 'archive']);
    Route::put('proposed-topics/{id}/unarchive', [ App\Http\Controllers\Proposal\ProposedTopicController::class, 'unarchive']);
    
    // Зөвхөн active статустай талбар авах тусгай route
    Route::get('proposal-fields/active', [App\Http\Controllers\Proposal\ProposalFieldController::class, 'activeOnly']);

    // Бүрэн CRUD route
    Route::apiResource('proposal-fields', App\Http\Controllers\Proposal\ProposalFieldController::class);
    Route::post('proposal-fields/bulk-upsert', [App\Http\Controllers\Proposal\ProposalFieldController::class, 'bulkUpsert']);


// Proposal Topic Requests -Сэдэв сонгох хүсэлтүүд
Route::prefix('proposal-topic-requests')->group(function () {
    Route::get('/', [App\Http\Controllers\Proposal\ProposalTopicRequestController::class, 'index']);
    Route::get('{id}', [App\Http\Controllers\Proposal\ProposalTopicRequestController::class, 'show']);
    Route::post('/', [App\Http\Controllers\Proposal\ProposalTopicRequestController::class, 'store']);
    Route::put('{id}/cancel', [ProposedTopicController::class, 'cancelling']);
    Route::put('{id}/approve', [App\Http\Controllers\Proposal\ProposalTopicRequestController::class, 'approve']);
    Route::put('{id}/decline', [App\Http\Controllers\Proposal\ProposalTopicRequestController::class, 'decline']);

    Route::delete('{id}', [App\Http\Controllers\Proposal\ProposalTopicRequestController::class, 'destroy']);
});

});
