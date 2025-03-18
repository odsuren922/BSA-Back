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
 
   
    Route::post('/tasks', [TaskController::class, 'store']);  
    Route::put('/tasks/{id}', [TaskController::class, 'updateTask']);
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    
    Route::post('/subtask', [SubtaskController::class, 'store']); 
    Route::put('/subtask/{id}', [SubtaskController::class, 'updateSubTask']);
    Route::delete('/subtask/{id}', [SubtaskController::class, 'destroy']);

    Route::get('/thesis/{id}', [ThesisController::class, 'index']);//done

});

