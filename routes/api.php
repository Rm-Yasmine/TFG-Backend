<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

// Rutas de proyectos
    Route::get('/projects', [ProjectController::class, 'misproyectos']);
    Route::get('/projects/{id}', [ProjectController::class, 'showproyecto']);
    Route::post('/projects', [ProjectController::class, 'addproyectos']);
    Route::post('/projects/{id}/members', [ProjectController::class, 'addMembers']);
    Route::delete('/projects/{id}', [ProjectController::class, 'eliminarproyecto']);

// Rutas de tareas
    Route::get('/tasks', [TaskController::class, 'tareas']);
    Route::post('/tasks', [TaskController::class, 'addtareas']);
    Route::get('/tasks/{id}', [TaskController::class, 'showtask']);
    Route::put('/tasks/{id}', [TaskController::class, 'updatetask']);
    Route::delete('/tasks/{id}', [TaskController::class, 'deletetask']);
    Route::post('/tasks/{taskId}/assign', [TaskController::class, 'assignUser']);
    
// Rutas de Notas
    Route::get('/notes', [NoteController::class, 'notes']);
    Route::post('/notes', [NoteController::class, 'addnote']);
    Route::get('/notes/{id}', [NoteController::class, 'shownote']);
    Route::put('/notes/{id}', [NoteController::class, 'updatenote']);
    Route::delete('/notes/{id}', [NoteController::class, 'destroynote']);
    Route::get('/notes/shared', [NoteController::class, 'shared']);

});
