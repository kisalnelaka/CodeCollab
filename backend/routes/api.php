<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CodingSessionController;
use App\Http\Controllers\ChallengeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/github-token', [AuthController::class, 'updateGithubToken']);

    // Project routes
    Route::apiResource('projects', ProjectController::class);

    // Coding Session routes
    Route::apiResource('projects.coding-sessions', CodingSessionController::class)->shallow();
    Route::post('/coding-sessions/{codingSession}/join', [CodingSessionController::class, 'join']);
    Route::post('/coding-sessions/{codingSession}/leave', [CodingSessionController::class, 'leave']);
    Route::post('/coding-sessions/{codingSession}/update-content', [CodingSessionController::class, 'updateContent']);

    // Challenge routes
    Route::apiResource('challenges', ChallengeController::class);
    Route::post('/challenges/{challenge}/submit', [ChallengeController::class, 'submit']);
    Route::get('/challenges/{challenge}/leaderboard', [ChallengeController::class, 'leaderboard']);
});
