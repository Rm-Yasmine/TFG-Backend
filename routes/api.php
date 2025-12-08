<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimeSessionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/resend-code', [AuthController::class, 'resendCode']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/request', [AuthController::class, 'requestReset']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas de proyectos
    Route::get('/projects', [ProjectController::class, 'misproyectos']);
    Route::get('/projects/sorted-by-end-date', [ProjectController::class, 'sortedByEndDate']);
    Route::get('/projects/{id}', [ProjectController::class, 'showproyecto']);
    Route::post('/projects', [ProjectController::class, 'addproyectos']);
    // Route::post('/projects/{projectId}/members', [ProjectController::class, 'addMembers']);
    Route::post('/projects/{id}/add-member-by-email', [ProjectController::class, 'addMemberByEmail']);
    Route::delete('/projects/{id}', [ProjectController::class, 'eliminarproyecto']);


    // Rutas de tareas
    Route::get('/tasks', [TaskController::class, 'tareas']);
    Route::post('/tasks', [TaskController::class, 'addtareas']);
    Route::get('/tasks/{id}', [TaskController::class, 'showtask']);
    Route::put('/tasks/{id}', [TaskController::class, 'updatetask']);
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updatestatus']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroytask']);
    Route::post('/tasks/{taskId}/assign', [TaskController::class, 'assignUser']);

    // Rutas de Notas
    Route::get('/notes', [NoteController::class, 'notes']);
    Route::post('/notes', [NoteController::class, 'addnote']);
    Route::get('/notes/{id}', [NoteController::class, 'shownote']);
    Route::put('/notes/{id}', [NoteController::class, 'updatenote']);
    Route::delete('/notes/{id}', [NoteController::class, 'destroynote']);
    Route::get('/notes/shared', [NoteController::class, 'shared']);

    // Rutas de sesiones de tiempo
    Route::post('/time-sessions/start', [TimeSessionController::class, 'start']);
    Route::post('/time-sessions/stop', [TimeSessionController::class, 'stop']);
    Route::get('/time-sessions', [TimeSessionController::class, 'index']);
});
