<?php

use App\Http\Controllers\DataController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\ExperimentController;
use App\Http\Controllers\ExperimentDataController;
use App\Http\Controllers\ExperimentDownloadController;
use App\Http\Controllers\ExperimentPredictionController;
use App\Http\Controllers\ExperimentPredictionDownloadController;
use App\Http\Controllers\ExperimentTrainController;
use App\Http\Controllers\InspectController;
use App\Http\Controllers\InspectSaveController;
use App\Http\Controllers\LabelEvidenceController;
use App\Http\Controllers\LabelEvidenceGridController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('teams.index');
    }

    return redirect()->route('login');
});

Route::group(['middleware' => config('jetstream.middleware', ['web'])], function () {
    $authMiddleware = config('jetstream.guard')
        ? 'auth:'.config('jetstream.guard')
        : 'auth';

    Route::group(['middleware' => [$authMiddleware, 'verified']], function () {
        Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    });
});

// Authenticated routes.
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');

    Route::middleware(['team'])->group(function () {
        // Data routes
        Route::prefix('data')->group(function () {
            Route::get('/', [DataController::class, 'index'])->name('data.index');

            Route::post('/datasets', [DatasetController::class, 'store'])->name('data.datasets.store');
            Route::get('/datasets/{dataset}', [DatasetController::class, 'show'])->name('data.datasets.show');
            Route::delete('/datasets/{dataset}', [DatasetController::class, 'destroy'])->name('data.datasets.destroy');

            Route::post('/labelEvidence', [LabelEvidenceController::class, 'store'])->name('data.labelEvidence.store');
            Route::get('/labelEvidence/{labelEvidence}', [LabelEvidenceController::class, 'show'])->name('data.labelEvidence.show');
            Route::delete('/labelEvidence/{labelEvidence}', [LabelEvidenceController::class, 'destroy'])->name('data.labelEvidence.destroy');
            Route::post('/labelEvidence/{labelEvidence}/labels', [LabelEvidenceGridController::class, 'store'])->name('data.labelEvidence.grids.store');
        });

        // Inspect routes
        Route::prefix('inspect')->group(function () {
            Route::get('/', [InspectController::class, 'index'])->name('inspect.index');
            Route::post('/', [InspectController::class, 'store'])->name('inspect.store');
            Route::get('/{inspectDataset}', [InspectController::class, 'show'])->name('inspect.show');
            Route::post('/{inspectDataset}/experimentData', [InspectSaveController::class, 'store'])->name('inspect.experimentData.store');
        });

        // Experiments routes
        Route::resource('experiments', ExperimentController::class)->only([
            'index', 'store', 'show', 'destroy',
        ]);
        Route::prefix('experiments')->group(function () {
            Route::post('/data/{inspectDataset}', [ExperimentDataController::class, 'store'])->name('experiments.data.store');
            Route::get('/data/{experimentData}', [ExperimentDataController::class, 'show'])->name('experiments.data.show');
            Route::delete('/data/{experimentData}', [ExperimentDataController::class, 'destroy'])->name('experiments.data.destroy');

            Route::get('/{experiment}/download', ExperimentDownloadController::class)->name('experiments.download');
            Route::post('/{experiment}/start', [ExperimentTrainController::class, 'start'])->name('experiments.start');
            Route::delete('/{experiment}/stop', [ExperimentTrainController::class, 'stop'])->name('experiments.stop');

            Route::post('/{experiment}/predictions', [ExperimentPredictionController::class, 'store'])->name('predictions.store');
            Route::get('/{experiment}/predictions/{prediction}/download', ExperimentPredictionDownloadController::class)->name('predictions.download');
        });
    });
});
