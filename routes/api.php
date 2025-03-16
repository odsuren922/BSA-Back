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
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubprojectController;


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
    //Шинээр project үүсгэх 
    //TODO:: CREATE TASK MODEL AND CONTROLLER
    //TODO:: CREATE SUBTASKMODEL NAD CONTROLLERS
    Route::post('/task', [ProjectController::class, 'store']);  
    Route::put('/task/{id}', [ProjectController::class, 'updateProject']);
    Route::get('/task', [ProjectController::class, 'index']);
    Route::delete('/task/{id}', [ProjectController::class, 'destroy']);
    
    Route::post('/subtask', [SubprojectController::class, 'store']); 
    Route::put('/subtask/{id}', [SubProjectController::class, 'updateSubProject']);
    Route::delete('/subtask/{id}', [SubprojectController::class, 'destroy']);

    Route::get('/thesis/{id}', [ThesisController::class, 'index']);//done

});

