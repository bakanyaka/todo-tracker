<?php

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

//Auth::routes();
use App\Http\Controllers\Api\AssigneeController;
use App\Http\Controllers\Api\AssigneeReportController;
use App\Http\Controllers\Api\IssueController;
use App\Http\Controllers\Api\IssueReportController;
use App\Http\Controllers\Api\IssueStatsController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\RedmineSyncController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TimeEntryController;
use App\Http\Controllers\Api\TrackerController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\TrackIssueController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'],)->middleware('guest');;
Route::any('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::group(['prefix' => 'api', 'middleware' => ['auth']], function () {
    Route::group(['prefix' => 'issues'], function () {
        Route::get('/', [IssueController::class, 'index'])->name('api.issues');
        Route::get('/sync', [IssueController::class, 'sync'])->name('api.issues.sync');
        Route::get('/stats', [IssueStatsController::class, 'index'])->name('api.issues.stats');
        Route::delete('/{issue}', [IssueController::class, 'destroy'])->name('api.issues.destroy');
        Route::post('/track', [TrackIssueController::class, 'store'])->name('api.issues.track');
        Route::delete('/{issue}/track', [TrackIssueController::class, 'destroy'])->name('api.issues.untrack');
        Route::get('/reports/projects', [IssueReportController::class, 'byProject'])
            ->name('api.issues.reports.projects');
        Route::get('/reports', [IssueReportController::class, 'index'])->name('api.issues.reports');
    });

    Route::group(['prefix' => 'projects'], function () {
        Route::get('/', [ProjectController::class, 'index'])->name('api.projects');
        Route::get('/sync', [ProjectController::class, 'sync'])->name('api.projects.sync');
    });

    Route::group(['prefix' => 'trackers'], function () {
        Route::get('/', [TrackerController::class, 'index'])->name('api.trackers');
        Route::get('/sync', [TrackerController::class, 'sync'])->name('api.trackers.sync');
    });

    Route::group(['prefix' => 'services', 'middleware' => ['can:touch,'.\App\Models\Service::class]], function () {
        Route::get('/', [ServiceController::class, 'index'])->name('api.services');
        Route::get('/sync', [ServiceController::class, 'sync'])->name('api.services.sync');
    });

    Route::group(['prefix' => 'assignees'], function () {
        Route::get('/', [AssigneeController::class, 'index'])->name('api.assignees');
        Route::get('/sync', [AssigneeController::class, 'sync'])->name('api.assignees.sync');
        Route::get('/report', [AssigneeReportController::class, 'index'])->name('api.assignees.report.index');
        Route::get('/report/{assignee}', [AssigneeReportController::class, 'show'])->name('api.assignees.report.show');
    });

    Route::group(['prefix' => 'time-entries'], function () {
        Route::get('/sync', [TimeEntryController::class, 'sync'])->name('api.time-entries.sync');
    });


    Route::get('/synchronizations/last', [RedmineSyncController::class, 'index'])->name('api.synchronizations.index');
});

//All unregistered routes should be handled by frontend
Route::any('{all}', [FrontendController::class, 'index'])->where(['all' => '.*'])->middleware(['auth']);




