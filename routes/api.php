<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProposalFormController,
    TopicController,
    DepartmentController,
    TeacherController,
    TopicRequestController,
    SupervisorController,
    StudentController
};

// ───── Department related ─────
Route::get('/department/{id}', [DepartmentController::class, 'show']);

// ───── Proposal Form ─────
Route::get('/proposalform', [ProposalFormController::class, 'index']);
Route::post('/proposalform', [ProposalFormController::class, 'update']);

// ───── Teacher ─────
Route::get('/teachers', [TeacherController::class, 'index']);
Route::get('/teachers/{id}', [TeacherController::class, 'show']);
Route::post('/teachers', [TeacherController::class, 'store']);
Route::put('/teachers/{id}', [TeacherController::class, 'update']);
Route::delete('/teachers/{id}', [TeacherController::class, 'destroy']);
Route::get('/teachers/{id}/topics', [TeacherController::class, 'getTeacherTopics']);
Route::get('/topics/confirmed-by-teacher', [TopicController::class, 'getConfirmedTopicsByTeacher']);
Route::get('/submitted/{type}', [TopicController::class, 'getSubmittedTopicsByType']);




// ───── Supervisor ─────
Route::get('/supervisors', [SupervisorController::class, 'index']);
Route::get('/supervisors/{id}', [SupervisorController::class, 'show']);

Route::get('/supervisor/topics/submitted', [SupervisorController::class, 'getSubmittedTopics']);
Route::get('/supervisor/topics/approved', [SupervisorController::class, 'getApprovedTopics']);
Route::get('/supervisor/topics/refused', [SupervisorController::class, 'getRefusedTopics']);

Route::post('/supervisor/topic/approve', [SupervisorController::class, 'approveTopic']);
Route::post('/supervisor/topic/refuse', [SupervisorController::class, 'refuseTopic']);

// ───── Topics ─────
Route::apiResource('topics', TopicController::class);
Route::post('/topic/store', [TopicController::class, 'store']); // used in student/teacher store
Route::post('/topic_confirm', [TopicController::class, 'confirmTopic']);
Route::post('/topic_decline', [TopicController::class, 'declineTopic']);
Route::get('/topics/checkedtopics', [TopicController::class, 'getCheckedTopics']); // for teacher proposed
Route::get('/topics/checkedtopicsbystud', [TopicController::class, 'getCheckedTopicsByStud']);
Route::get('/topics/topiclistproposedbyuser', [TopicController::class, 'getTopicListProposedByUser']);
Route::get('/topics/reviewedtopicList', [TopicController::class, 'getRefusedOrApprovedTopics']);
Route::get('/topics_confirmed', [TopicController::class, 'getConfirmedTopicsWithAdvisors']);

// ───── Topic Requests ─────
Route::get('/topic_requests', [TopicRequestController::class, 'index']);
Route::post('/topic-requests', [TopicRequestController::class, 'store']);
Route::post('/topic-requests/confirm', [TopicRequestController::class, 'confirmByStudent']);
Route::get('/topic_requests_teacher', [TopicRequestController::class, 'getRequestedTopicByTeacher']);

// ───── Student ─────
Route::get('/students/all', [StudentController::class, 'index']);
