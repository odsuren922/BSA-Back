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

//Register and login 
Route::post('/auth/register',[AuthController::class,'createUser']);
Route::post('/auth/login',[AuthController::class,'loginUser']);

Route::get('/proposalform', [ProposalFormController::class, 'index']);
Route::post('/proposalform', [ProposalFormController::class, 'update']);

//new
Route::apiResource('topics', TopicController::class);
Route::post('/topic/store', [TopicController::class, 'store']);


Route::get('/teacher/{id}', [TeacherController::class, 'show']);
Route::get('/department/{id}', [DepartmentController::class, 'show']);


Route::get('/topic_requests_teacher', [TopicRequestController::class, 'getRequestedTopicByTeacher']);

Route::middleware('auth:sanctum')->group(function () {
    //Шинээр project үүсгэх 
    Route::post('/projects', [ProjectController::class, 'store']);  
    Route::put('/projects/{id}', [ProjectController::class, 'updateProject']);
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    
    Route::post('/subprojects', [SubprojectController::class, 'store']); 
    Route::put('/subprojects/{id}', [SubProjectController::class, 'updateSubProject']);
    Route::delete('/subprojects/{id}', [SubprojectController::class, 'destroy']);

    Route::get('/thesis/{id}', [ThesisController::class, 'index']);

});

