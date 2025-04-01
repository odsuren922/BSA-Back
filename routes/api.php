<?php

use App\Http\Controllers\ProposalFormController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TopicRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ThesisController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\ThesisCycleController;
use App\Http\Controllers\GradingSchemaController;
use App\Http\Controllers\GradingComponentController;
use App\Http\Controllers\GradingCriteriaController;
use App\Http\Controllers\ScoreController;

use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\CommitteeMemberController;
use App\Http\Controllers\CommitteeStudentController;
use App\Http\Controllers\CommitteeScheduleController;

Route::get('/proposalform', [ProposalFormController::class, 'index']);
Route::post('/proposalform', [ProposalFormController::class, 'update']);

//new
Route::apiResource('topics', TopicController::class);
Route::post('/topic/store', [TopicController::class, 'store']);

Route::get('/teachers', [TeacherController::class, 'index']);
Route::get('/teachers/{id}', [TeacherController::class, 'dep_id']);
Route::get('/teacher/{id}', [TeacherController::class, 'show']);
Route::get('/department/{id}', [DepartmentController::class, 'show']);

Route::get('/topic_requests_teacher', [TopicRequestController::class, 'getRequestedTopicByTeacher']);

//Register and login
Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);

Route::middleware('auth:sanctum')->group(function () {
    //ҮЕЧИЛСЭН ТӨЛӨВЛӨГӨӨ ҮҮСГЭХ
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}', [TaskController::class, 'updateTask']);
    Route::get('/tasks', [TaskController::class, 'index']); //done
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    Route::post('/subtask', [SubtaskController::class, 'store']);
    Route::put('/subtask/{id}', [SubtaskController::class, 'updateSubTask']);
    Route::delete('/subtask/{id}', [SubtaskController::class, 'destroy']);
    //PDF ҮҮСГЭХД ХЭРЭГЛЭХ МЭДЭЭЛЭЛ
    Route::get('/thesis/{id}', [ThesisController::class, 'pdf']); //done
    //Supervisor ӨӨРИЙН УДИРДАХ БСА ХАРАХ
    Route::get('/theses', [ThesisController::class, 'supervisodThesis']);
    Route::get('/allTheses', [ThesisController::class, 'allTheses']); // БҮХ БСА ХАРАХ
    Route::get('/onethesisSuper/{id}', [ThesisController::class, 'index']); //1 БСА ХАРАХ +teacher+student (not used cus i think slow )
    Route::get('/onethesis/{id}', [ThesisController::class, 'getThesis']); //1 БСА ХАРАХ
    Route::get('/thesis/{id}/student', [ThesisController::class, 'getStudentByThesis']); //БСА СУРАГЧ
    Route::get('/thesis/{id}/supervisor', [ThesisController::class, 'getSupervisorByThesis']); //БСА УДИРДАХ
    //ThesisCycle id гаар бүх БСА-н мэдээллийг багш сурагчидтай хамт авах
    Route::get('/cycles/{id}/theses', [ThesisController::class, 'getThesesByCycle']);
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
    Route::get('committees/{committee}', [CommitteeController::class, 'show']); // Get single committee
    Route::post('committees', [CommitteeController::class, 'store']); // Create committee
    Route::put('committees/{committee}', [CommitteeController::class, 'update']); // Update committee
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
        Route::apiResource('schedules', ScheduleController::class)
            ->except(['show'])
            ->scoped(['schedule' => 'committee']);
    });
});
