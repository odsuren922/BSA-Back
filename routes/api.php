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

    Route::get('/teachers/{id}', [TeacherController::class, 'dep_id']);
    Route::get('/teacher/{id}', [TeacherController::class, 'show']);
    Route::get('/teachers/count/department/{dep_id}', [TeacherController::class, 'countByDepartment']);
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

    Route::get('/committees/by-teacher/{teacherId}', [App\Http\Controllers\Committee\CommitteeController::class, 'getCommitteesByTeacher']);
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
    // Route::apiResource('committee-scores', App\Http\Controllers\CommitteeScoreController::class);

    //SAVES SCORES
    Route::post('/committee-scores/batch', [App\Http\Controllers\CommitteeScoreController::class, 'storeBatch']);
    //CHECK ALL MEMBER GIVES SCORES TO A STUDENT
    Route::post('/committee-scores/finalize/{studentId}/{componentId}', [App\Http\Controllers\CommitteeScoreController::class, 'finalizeCommitteeScores']);
    Route::post('/committee-scores/batch-finalize-by-committee', [App\Http\Controllers\CommitteeScoreController::class, 'batchFinalizeByCommittee']);

    Route::post('/committees/check-assignment', [App\Http\Controllers\Committee\CommitteeController::class, 'isTeacherAndStudentInSameCommittee']);

    Route::prefix('assigned-grading')->group(function () {
        Route::get('/', [App\Http\Controllers\AssignedGradingController::class, 'index']); // list all
        Route::get('/teacher/{teacherId}', [App\Http\Controllers\AssignedGradingController::class, 'getByAssignedById']);
        // list all
        Route::post('/', [App\Http\Controllers\AssignedGradingController::class, 'store']); // store multiple
        Route::post('/check-assignment', [App\Http\Controllers\AssignedGradingController::class, 'checkAssignment']); // permission check
        Route::delete('/{assignedGrading}', [App\Http\Controllers\AssignedGradingController::class, 'destroy']); // delete
    });
    Route::get('/assigned-grading/teacher/{teacherId}', [App\Http\Controllers\AssignedGradingController::class, 'getByAssignedById']);
    Route::get('/assigned-grading/score/{teacherId}', [App\Http\Controllers\AssignedGradingController::class, 'getScoreByAssignedById']);
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



});
