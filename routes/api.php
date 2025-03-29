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


Route::get('/proposalform', [ProposalFormController::class, 'index']);
Route::post('/proposalform', [ProposalFormController::class, 'update']);

//new
Route::apiResource('topics', TopicController::class);
Route::post('/topic/store', [TopicController::class, 'store']);


Route::get('/teacher/{id}', [TeacherController::class, 'show']);
Route::get('/department/{id}', [DepartmentController::class, 'show']);


Route::get('/topic_requests_teacher', [TopicRequestController::class, 'getRequestedTopicByTeacher']);


//Register and login 
Route::post('/auth/register',[AuthController::class,'createUser']);
Route::post('/auth/login',[AuthController::class,'loginUser']);

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
    Route::get('/thesis/{id}', [ThesisController::class, 'pdf']);//done
    //Supervisor ӨӨРИЙН УДИРДАХ БСА ХАРАХ
    Route::get('/theses', [ThesisController::class, 'supervisodThesis']);
    Route::get('/allTheses', [ThesisController::class, 'allTheses']);// БҮХ БСА ХАРАХ
    Route::get('/onethesisSuper/{id}', [ThesisController::class, 'index']);//1 БСА ХАРАХ +teacher+student (not used cus i think slow )
    Route::get('/onethesis/{id}', [ThesisController::class, 'getThesis']); //1 БСА ХАРАХ
    Route::get('/thesis/{id}/student', [ThesisController::class, 'getStudentByThesis']);//БСА СУРАГЧ
    Route::get('/thesis/{id}/supervisor', [ThesisController::class, 'getSupervisorByThesis']); //БСА УДИРДАХ 
  
    //tuhain thesiscycle iin buh thesis bagsh suragchin mdeelel awah
    Route::get('/cycles/{id}/theses', [ThesisController::class, 'getThesesByCycle']);
   //Neg major hedin suragch ug cycled baigaa we
   Route::get('/cycles/{id}/student-counts', [ThesisController::class, 'getStudentCountByProgram']);

    
    //Тэнхмийн туслах шинэ cycle үүсгэх
    Route::post('/thesis-cycles', [ThesisCycleController::class, 'store']); 
    Route::get('/thesis-cycles', [ThesisCycleController::class, 'index']);
    Route::get('/active-cycles', [ThesisCycleController::class, 'active']);
    Route::get('/thesis-cycles/{id}', [ThesisCycleController::class, 'show']);
    Route::put('/thesis-cycles/{id}', [ThesisCycleController::class, 'update']);
    Route::delete('/thesis-cycles/{id}', [ThesisCycleController::class, 'destroy']);//done

    //Grading Schema Management 
    //үнэлэх аргын нэгдэл
    Route::post('/grading-schemas', [GradingSchemaController::class, 'store']); 
    Route::get('/grading-schemas', [GradingSchemaController::class, 'index']);
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



});




