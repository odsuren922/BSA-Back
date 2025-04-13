<?php

use App\Http\Controllers\ProposalFormController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TopicRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\Thesis\ThesisController;
use App\Http\Controllers\Thesis\ThesisCycleController;
use App\Http\Controllers\Thesis\ThesisScoreController;
use App\Http\Controllers\Thesis\ThesisPlanStatusController;


use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubtaskController;


use App\Http\Controllers\Grading\GradingSchemaController;
use App\Http\Controllers\Grading\GradingComponentController;
use App\Http\Controllers\Grading\GradingCriteriaController;
use App\Http\Controllers\ScoreController;

use App\Http\Controllers\Committee\CommitteeController;
use App\Http\Controllers\Committee\CommitteeMemberController;
use App\Http\Controllers\Committee\CommitteeStudentController;

use App\Http\Controllers\ScheduleController;


Route::get('/proposalform', [ProposalFormController::class, 'index']);
Route::post('/proposalform', [ProposalFormController::class, 'update']);

//new
Route::apiResource('topics', TopicController::class);
Route::post('/topic/store', [TopicController::class, 'store']);

Route::get('/teachers', [TeacherController::class, 'index']);

Route::get('/teachers/{id}', [TeacherController::class, 'dep_id']);
//Тухайн тэнхмийн бүх багшийн мэдээлэл авах
//Route::get('/teachers/{id}', [TeacherController::class, 'teacherCommittee']); //TODO::
Route::get('/teacher/{id}', [TeacherController::class, 'show']);
Route::get('/department/{id}', [DepartmentController::class, 'show']);
Route::get('/teachers/count/department/{dep_id}', [TeacherController::class, 'countByDepartment']);

Route::get('/topic_requests_teacher', [TopicRequestController::class, 'getRequestedTopicByTeacher']);

//Register and login
Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);

Route::middleware('auth:sanctum')->group(function () {
    // ------------------------------
    //ҮЕЧИЛСЭН ТӨЛӨВЛӨГӨӨ ҮҮСГЭХ
    // ------------------------------
    // Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}', [TaskController::class, 'updateTask']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    Route::post('/subtask', [SubtaskController::class, 'store']);
    Route::put('/subtask/{id}', [SubtaskController::class, 'updateSubTask']);
    Route::delete('/subtask/{id}', [SubtaskController::class, 'destroy']);

    Route::get('/thesis/{id}', [ThesisController::class, 'pdf']); //PDF ҮҮСГЭХД ХЭРЭГЛЭХ МЭДЭЭЛЭЛ
    // ------------------------------
    //ҮЕЧИЛСЭН ТӨЛӨВЛӨГӨӨ ТӨЛӨВ ӨӨРЧЛӨХ
    // ------------------------------


    Route::get('/thesis-plan-status/{thesis_id}', [ThesisPlanStatusController::class, 'show']);
    Route::patch('/thesis-plan-status/{thesis_id}/student-send', [ThesisPlanStatusController::class, 'studentSent']);
    Route::patch('/thesis-plan-status/{thesis_id}/student-Unsend', [ThesisPlanStatusController::class, 'studentUnSent']);
    Route::patch('/thesis-plan-status/{thesis_id}/teacher-status', [ThesisPlanStatusController::class, 'updateTeacherStatus']);
    Route::patch('/thesis-plan-status/{thesis_id}/department-status', [ThesisPlanStatusController::class, 'updateDepartmentStatus']);


    // ------------------------------
    //Supervisor ӨӨРИЙН УДИРДАХ БСА ХАРАХ
    // ------------------------------
//TODO::USER OR UN USED
    Route::get('/theses', [ThesisController::class, 'supervisodThesis']); //нэвтэрсэн багштай харьяатай бүр БСА //DONE
    Route::get('/thesisInfo/{id}', [ThesisController::class, 'index']); //БСА Хэрэгжүүлэгч болон удирдагч 
    Route::get('/onethesis/{id}', [ThesisController::class, 'getThesis']); // id- гаар ганц БСА ХАРАХ



    //ThesisCycle id гаар бүх БСА-н мэдээллийг багш сурагчидтай хамт авах
    Route::get('/cycles/{id}/theses', [ThesisController::class, 'getThesesByCycle']);
    //getActiveThesesByCycle багш сурагчидын мэдээлэлтэй 
    Route::get('/cycles/{id}/active-theses', [ThesisController::class, 'getActiveThesesByCycle']);
    //Тухайн БСА ымар мэргэжлийн хэдэн хүүхэд байгааг олох
    Route::get('/cycles/{id}/student-counts', [ThesisController::class, 'getStudentCountByProgram']);
     
    //Тэнхмийн туслах шинэ cycle үүсгэх
    Route::post('/thesis-cycles', [ThesisCycleController::class, 'store']);
    Route::get('/thesis-cycles', [ThesisCycleController::class, 'index']);
    Route::get('/active-cycles', [ThesisCycleController::class, 'active']);
    Route::get('/thesis-cycles/{id}', [ThesisCycleController::class, 'show']);
    
    Route::put('/thesis-cycles/{id}', [ThesisCycleController::class, 'update']);
    Route::delete('/thesis-cycles/{id}', [ThesisCycleController::class, 'destroy']); //done

    //Grading Schema Management
    //үнэлэх аргын нэгдэл
    Route::post('/grading-schemas', [GradingSchemaController::class, 'store']);
    Route::get('/grading-schemas', [GradingSchemaController::class, 'index']);
    //showByThesisCycle
    Route::get('/thesis-cycles/{id}/grading-schema', [GradingSchemaController::class, 'showByThesisCycle']);

    Route::get('/grading-schemas/{id}', [GradingSchemaController::class, 'show']);
    Route::put('/grading-schemas/{id}', [GradingSchemaController::class, 'update']);
    Route::patch('/grading-schemas/{id}', [GradingSchemaController::class, 'addComponents']);
    Route::put('/grading-one-schema/{id}', [GradingSchemaController::class, 'updateone']);
    Route::delete('/grading-schemas/{id}', [GradingSchemaController::class, 'destroy']);

    // Grading Component Management
    //ADD WHICH WEEK
    //үнэлэх аргын хэсэг
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

    // Score Management
    //үнэлгээний  оноо
    Route::post('scores', [ScoreController::class, 'store']);
    Route::get('scores', [ScoreController::class, 'index']);
    Route::get('scores/{id}', [ScoreController::class, 'show']);
    Route::put('scores/{id}', [ScoreController::class, 'update']);
    Route::delete('scores/{id}', [ScoreController::class, 'destroy']);

    Route::get('committees', [CommitteeController::class, 'index']); // Get all committees
    Route::get('/committees/active-cycle', [CommitteeController::class, 'getActiveCycleValidCommittees']); // Get all committees with active cycle
    Route::get('committees/{committee}', [CommitteeController::class, 'show']); // Get single committee
    Route::post('committees', [CommitteeController::class, 'store']); // Create committee
    Route::patch('committees/{committee}', [CommitteeController::class, 'update']);
    //Route::put('committees/{committee}', [CommitteeController::class, 'update']); // Update committee
    Route::delete('committees/{committee}', [CommitteeController::class, 'destroy']); // Delete committee

    // комиссын мэдээлэл авахдаа : 1. Thesis_cycle-н id gaarn awah
    Route::get('/thesis-cycles/{thesisCycle}/committees', [CommitteeController::class, 'getByThesisCycle']);
    // комиссын мэдээлэл авахдаа : 1. Thesis_cycle-н id gaarn awah 2. үнэлгээтэйгээ хамт авна
    Route::prefix('thesis-cycles/{thesisCycle}/grading-components/{gradingComponent}')->group(function () {
        Route::get('/committees', [CommitteeController::class, 'getByCycleAndComponent']);
        Route::post('/committees', [CommitteeController::class, 'storeWithCycleAndComponent']);
    });

    Route::prefix('committees/{committee}')->group(function () {
        Route::get('members', [CommitteeMemberController::class, 'index']);
        Route::post('members', [CommitteeMemberController::class, 'store']);
        Route::put('members/{member}', [CommitteeMemberController::class, 'update']);
        // Route::delete('members/{member}', [CommitteeMemberController::class, 'destroy']);
    });
    Route::delete('/committee-members/{id}', [CommitteeMemberController::class, 'destroy']);
    Route::patch('/committee-members/{member}/role', [CommitteeMemberController::class, 'patchRole']);

    Route::prefix('committees/{committee}')->group(function () {
        // Students routes
        Route::get('students', [CommitteeStudentController::class, 'index']);
        Route::post('students', [CommitteeStudentController::class, 'store']);
        Route::put('students/{committeeStudent}', [CommitteeStudentController::class, 'update'])->scopeBindings();
        Route::delete('students/{committeeStudent}', [CommitteeStudentController::class, 'destroy'])->scopeBindings();
    });


    Route::prefix('committees/{committee}')->group(function () {
        Route::get('schedules', [ScheduleController::class, 'index']);
        Route::post('schedules', [ScheduleController::class, 'store']);
        Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy']);
        // Route::apiResource('schedules', ScheduleController::class)
        //     ->except(['show'])
        //     ->scoped(['schedule' => 'committee']);
    });
    Route::patch('schedules/{schedule}', [ScheduleController::class, 'update']);

    Route::get('/thesis/{id}/scores', [ThesisScoreController::class, 'getThesisScores']);
    Route::post('/supervisor/thesis-scores', [ThesisScoreController::class, 'storeScore']);
    Route::post('/thesis/{thesisId}/give-scores', [ThesisScoreController::class, 'storeMultipleScores']);
    Route::post('/committee-scores/bulk', [ThesisScoreController::class, 'storeBulk']);
    Route::get('/committees/{committee}/scores', [ThesisScoreController::class, 'getCommitteeStudentScores']);

    Route::get('/scores/{id}', [ThesisScoreController::class, 'index']);

});
