<?php

use App\Http\Controllers\Api\ExperimentErrorController;
use App\Http\Controllers\Api\ExperimentFinishController;
use App\Http\Controllers\Api\InspectErrorController;
use App\Http\Controllers\Api\InspectFinishController;
use App\Http\Controllers\Api\PredictionErrorController;
use App\Http\Controllers\Api\PredictionFinishController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Inspect routes
Route::post('/inspect/{inspectKey}/finish', InspectFinishController::class);
Route::post('/inspect/{inspectKey}/error', InspectErrorController::class);

// Experiment routes
Route::post('/experiments/{experiment}/finish', ExperimentFinishController::class);
Route::post('/experiments/{experiment}/error', ExperimentErrorController::class);

// Prediction routes
Route::post('/predictions/{predictionKey}/finish', PredictionFinishController::class);
Route::post('/predictions/{predictionKey}/error', PredictionErrorController::class);
